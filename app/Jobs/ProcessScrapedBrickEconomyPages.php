<?php

namespace App\Jobs;

use App\Enums\PriceType;
use App\Models\Price;
use App\Models\Product;
use App\Models\Theme;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class ProcessScrapedBrickEconomyPages implements ShouldQueue
{
    use Queueable;

    public Product $product;

    /**
     * Create a new job instance.
     */
    public function __construct(public $file_path, public $product_id)
    {
        $this->product = Product::find($product_id);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        if(!file_exists($this->file_path))
            $this->fail("HTML for product {$this->product_id} doesn't exist.");

        $html = file_get_contents($this->file_path);
        $this->parseSetInfo($html, $this->product_id);
        $this->parseMinifigInfo($html, $this->product_id);
        $this->parsePrices($html, $this->product_id);
        $this->parseImages($html, $this->product_id);

        // if (file_exists($this->file_path))
        //     unlink($this->file_path);
    }

    public function parseSetInfo($html, $product_id)
    {
        $product = $this->product;

        if ($product->product_type == 'set') {
            $crawler = new Crawler($html);
            $details = [];
            // Find the Set Details panel
            $panel = $crawler->filter('#ContentPlaceHolder1_SetDetails');
            if ($panel->count() > 0) {
                $panel->filter('.row.rowlist')->each(function ($row) use (&$details) {
                    $label = trim($row->filter('.col-xs-5.text-muted')->text(''));
                    $valueNode = $row->filter('.col-xs-7');

                    // Get the value as HTML (to preserve links, spans, etc.)
                    $valueHtml = $valueNode->count() ? $valueNode->html() : '';
                    // Or as plain text:
                    $valueText = $valueNode->count() ? trim($valueNode->text('')) : '';

                    // Save both, or just one as needed
                    $details[$label] = [
                        'label' => $label,
                        'value' => $valueText,
                        'value_html' => $valueHtml,
                    ];
                });
            }

            $product->update(array_filter([
                'name' => $details['Name']['value'] ?? NULL,
                'year' => ($details['Year']['value'] ?? ($details['Years']['value'] ? strtok($details['Years']['value'], ' -') : NULL)) ?? NULL,
                'released_at' => ($details['Released']['value'] ?? NULL) ? Carbon::parse($details['Released']['value']) : NULL,
                'availability' => $details['Availability']['value'] ?? NULL,
                'num_parts' => ($details['Pieces']['value'] ?? NULL) ? intval($details['Pieces']['value']) : NULL,
                'packaging' => $details['Packaging']['value'] ?? NULL,
            ]));

            $themeId = NULL;
            $subthemeId = NULL;

            //$output = new \Symfony\Component\Console\Output\ConsoleOutput();

            if ($details['Themes']['value_html'] ?? NULL) {
                //$output->writeln("<info> $product_id => Themes: " . json_encode($details['Themes']) . "</info>");
                if (preg_match_all('#href="([^"]+)"#', $details['Themes']['value_html'], $hrefMatches)) {
                    foreach ($hrefMatches[1] as $href) {
                        // Try to match theme and subtheme
                        if (preg_match('#/sets/theme/([^/]+)(?:/subtheme/([^"/]+))?#', $href, $m)) {
                            $themeId = $m[1];
                            $subthemeId = $m[2] ?? null;

                            //$output->writeln("<info> MULTIPLE $product_id => $themeId, $subthemeId</info>");

                            $theme = Theme::where('brickeconomy_id', $themeId)->whereNull('parent_id')->first();
                            $subtheme = $theme?->children()?->where('brickeconomy_id', $subthemeId)?->first();

                            if ($theme && $subtheme) {
                                $product->update([
                                    'theme_id' => $subtheme->id
                                ]);
                                $product->themes()->sync([$subtheme->id]);
                            } else if ($theme && !$subtheme) {
                                $product->update([
                                    'theme_id' => $theme->id
                                ]);
                                $product->themes()->sync([$theme->id]);
                            }
                        }
                    }
                }
            } else {
                if ($details['Subtheme']['value_html'] ?? NULL) {
                    if (preg_match('#href="([^"]+)"#', $details['Subtheme']['value_html'], $hrefMatch)) {
                        $href = $hrefMatch[1];
                        // Now extract the subtheme from the href
                        if (preg_match('#/([^/]+)/subtheme/([^"/]+)#', $href, $subthemeMatch)) {
                            $themeId = $subthemeMatch[1];
                            $subthemeId = $subthemeMatch[2];
                        }
                    }
                }

                if (!$themeId && $details['Theme']['value_html'] ?? NULL) {
                    if (preg_match('#/sets/theme/([^"/]+)#', $details['Theme']['value_html'], $m)) {
                        $themeId = $m[1];
                    }
                }

                //$output->writeln("<info> $product_id => $themeId, $subthemeId</info>");

                $theme = Theme::where('brickeconomy_id', $themeId)->whereNull('parent_id')->first();
                $subtheme = $theme?->children()?->where('brickeconomy_id', $subthemeId)?->first();

                if ($theme && $subtheme) {
                    $product->update([
                        'theme_id' => $subtheme->id
                    ]);
                    $product->themes()->sync([$subtheme->id]);
                } else if ($theme && !$subtheme) {
                    $product->update([
                        'theme_id' => $theme->id
                    ]);
                    $product->themes()->sync([$theme->id]);
                }
                /* else {
                    $output->writeln("<error> $product_id => $themeId, $subthemeId</error>");
                } */
            }


            $panel = $crawler->filter('#Minifigs');
            $minifigIds = [];
            if ($panel->count() > 0) {
                // Find all <a> tags with href matching /minifig/{id}/
                $panel->filter('a[href^="/minifig/"]')->each(function ($node) use (&$minifigIds) {
                    $href = $node->attr('href');
                    if (preg_match('#/minifig/([^/]+)/#', $href, $m)) {
                        if ($id = Product::where('brickeconomy_id', $m[1])->first()?->id)
                            $minifigIds[] = $id;
                    }
                });
            }

            $product->minifigs()->sync($minifigIds);

            $facts = [
                'facts' => [],
                'regional_prices' => [],
                'barcodes' => [],
            ];

            $panel = $crawler->filter('#ContentPlaceHolder1_PanelSetFacts');
            if ($panel->count() > 0) {
                // Extract facts from <ul>
                $panel->filter('.set-facts ul li')->each(function ($node) use (&$facts) {
                    $facts['facts'][] = trim($node->text());
                });
            }

            if ($facts['facts']) {
                $this->product->update([
                    'facts' => $facts['facts']
                ]);
            }

            // Log::info("Parsed set details", ['product_id' => $product_id, 'details' => $details]);
        }
    }

    public function parseMinifigInfo($html, $product_id)
    {
        $product = $this->product;

        if ($product->product_type == 'minifig') {
            $crawler = new Crawler($html);
            $details = [];
            // Find the Set Details panel
            $panel = $crawler->filter('#ContentPlaceHolder1_MinifigDetails');
            if ($panel->count() > 0) {
                $panel->filter('.row.rowlist')->each(function ($row) use (&$details) {
                    $label = trim($row->filter('.col-xs-5.text-muted')->text(''));
                    $valueNode = $row->filter('.col-xs-7');

                    // Get the value as HTML (to preserve links, spans, etc.)
                    $valueHtml = $valueNode->count() ? $valueNode->html() : '';
                    // Or as plain text:
                    $valueText = $valueNode->count() ? trim($valueNode->text('')) : '';

                    // Save both, or just one as needed
                    $details[$label] = [
                        'label' => $label,
                        'value' => $valueText,
                        'value_html' => $valueHtml,
                    ];
                });
            }

            $product->update(array_filter([
                'name' => $details['Name']['value'] ?? NULL,
                'year' => ($details['Year']['value'] ?? (($details['Years']['value'] ?? NULL) ? strtok($details['Years']['value'], ' -') : NULL)) ?? NULL,
                'released_at' => ($details['Released']['value'] ?? NULL) ? Carbon::parse($details['Released']['value']) : NULL,
                'availability' => $details['Availability']['value'] ?? NULL,
                'packaging' => $details['Packaging']['value'] ?? NULL,
            ]));

            //$output = new \Symfony\Component\Console\Output\ConsoleOutput();


            if ($details['Themes']['value_html'] ?? NULL) {
                if (preg_match_all('#href="([^"]+)"#', $details['Themes']['value_html'], $hrefMatches)) {
                    foreach ($hrefMatches[1] as $href) {
                        // Try to match theme and subtheme
                        if (preg_match('#/([^/]+)(?:/subtheme/([^"/]+))?#', $href, $m)) {
                            $themeId = $m[1];
                            $subthemeId = $m[2] ?? null;

                            $theme = Theme::where('brickeconomy_id', $themeId)->whereNull('parent_id')->first();
                            $subtheme = $theme?->children()?->where('brickeconomy_id', $subthemeId)?->first();

                            if ($theme && $subtheme) {
                                $product->update([
                                    'theme_id' => $subtheme->id
                                ]);
                                $product->themes()->sync([$subtheme->id]);
                            } else if ($theme && !$subtheme) {
                                $product->update([
                                    'theme_id' => $theme->id
                                ]);
                                $product->themes()->sync([$theme->id]);
                            }
                        }
                    }
                }
            } else {

                $themeId = NULL;
                $subthemeId = NULL;

                if ($details['Subtheme']['value_html'] ?? NULL) {
                    if (preg_match('#href="([^"]+)"#', $details['Subtheme']['value_html'], $hrefMatch)) {
                        $href = $hrefMatch[1];
                        // Now extract the subtheme from the href
                        if (preg_match('#/([^/]+)/subtheme/([^"/]+)#', $href, $subthemeMatch)) {
                            $themeId = $subthemeMatch[1];
                            $subthemeId = $subthemeMatch[2];
                        }
                    }
                }

                if (!$themeId && $details['Theme']['value_html'] ?? NULL) {
                    if (preg_match('#/minifigs/theme/([^"/]+)#', $details['Theme']['value_html'], $m)) {
                        $themeId = $m[1];
                    }
                }

                //$output->writeln("<info> $product_id => $themeId, $subthemeId</info>");

                $theme = Theme::where('brickeconomy_id', $themeId)->whereNull('parent_id')->first();
                $subtheme = $theme?->children()?->where('brickeconomy_id', $subthemeId)?->first();



                if ($theme && $subtheme) {
                    $product->update([
                        'theme_id' => $subtheme->id
                    ]);
                    $product->themes()->sync([$subtheme->id]);
                } else if ($theme && !$subtheme) {
                    $product->update([
                        'theme_id' => $theme->id
                    ]);
                    $product->themes()->sync([$theme->id]);
                }
                /* else {
                    $output->writeln("<error> $product_id => $themeId, $subthemeId</error>");
                } */
            }

            $facts = [
                'fact' => null,
                'common_description' => null,
            ];

            $panel = $crawler->filter('#ContentPlaceHolder1_PanelMinifigFacts');

            if ($panel->count() > 0) {
                // The main fact is in the first .pt-10.pr-20.pl-20.pb-20 div
                $factDiv = $panel->filter('.pt-10.pr-20.pl-20.pb-20');
                if ($factDiv->count()) {
                    $facts['fact'] = trim($factDiv->text());
                }
            }

            if ($facts['fact']) {
                $this->product->update([
                    'facts' => [$facts['fact']]
                ]);
            }
        }
    }

    public function parseImages($html, $product_id)
    {
        $regex = '/<ul id="setmediagallery"[^>]*>(.*?)<\/ul>/s';
        // First, extract the content of the <ul> block
        if ($html && preg_match($regex, $html, $ulMatch)) {
            $ulContent = $ulMatch[1];
            // Now, extract all src attributes from the <img> tags within the <ul>
            $imgRegex = "/\\.attr\\('src',\\s*'([^']+)'\\)/";
            if (preg_match_all($imgRegex, $ulContent, $imgMatches)) {
                // Array of all src attributes
                $srcs = collect($imgMatches[1])->map(fn($url) => "https://www.brickeconomy.com{$url}");
                // Log::info("Dispatching download of {$srcs->count()} images", ['urls' => $srcs]);
                $this->product->update([
                    'scraped_imgs' => $srcs
                ]);
                DownloadProductImageJob::dispatch($product_id, $srcs)->onQueue('scraping');
                return;
            }
        }

        $imgRegex = '/<img[^>]+id="image_modal_xlarge"[^>]+src="([^"]+)"[^>]*>/i';

        if ($html && preg_match($imgRegex, $html, $imgMatch)) {
            $src = $imgMatch[1];
            // Optionally prepend the domain if needed
            $fullUrl = str_starts_with($src, 'http') ? $src : "https://www.brickeconomy.com{$src}";
            // Log::info("Dispatching download of modal image $fullUrl", ['url' => $fullUrl]);
            $this->product->update([
                'scraped_imgs' => [$fullUrl]
            ]);
            DownloadProductImageJob::dispatch($product_id, collect([$fullUrl]))->onQueue('scraping');
        }
    }

    public function parsePrices($html, $product_id)
    {
        $product = $this->product;

        $has_daily = $product->prices()->where('type', PriceType::SCRAPED)->where('date', now()->format('Y-m-d'))->exists();
        $has_historical = $product->prices()->where('type', PriceType::AGGREGATED)->exists();

        if (!$has_historical) {

            preg_match_all('/data\.addRows\(\[\s*(.*?)\s*\]\);/s', $html, $rowsMatches, PREG_SET_ORDER);
            $dataRows = '';

            foreach ($rowsMatches as $match) {
                $rowsContent = $match[1];
                if (strpos($rowsContent, "'Released'") !== false) {
                    $dataRows = $rowsContent;
                    break;
                }
            }

            if (!$dataRows && !empty($rowsMatches)) {
                $dataRows = $rowsMatches[0][1] ?? '';
            }

            if ($dataRows && preg_match_all('/\[new Date\((\d+),\s*(\d+),\s*(\d+)\),\s*([\d.]+)/s', $dataRows, $matches, PREG_SET_ORDER)) {
                $minYear = 1900;
                $maxYear = now()->year;
                foreach ($matches as $match) {
                    $year = (int)$match[1];
                    $month = (int)$match[2];
                    $day = (int)$match[3];
                    $price = (float)$match[4];

                    if ($year >= $minYear && $year <= $maxYear) {
                        $date = Carbon::create($year, $month, $day)->format('Y-m-d');
                        $product->prices()->create([
                            'date' => $date,
                            'value' => $price,
                            'currency' => 'USD',
                            'type' => PriceType::AGGREGATED
                        ]);
                    }
                }
            }
        }

        if (!$has_daily) {
            $crawler = new Crawler($html);
            Log::info("product {$product_id}");

            if ($product->product_type == 'set') {

                // Find the div with the desired ID
                $pricingDiv = $crawler->filter('#ContentPlaceHolder1_PanelSetPricing');

                if ($pricingDiv->count() > 0) {
                    // Extract Retail price
                    $retailPrice = $pricingDiv->filter('.row.rowlist')->reduce(function (Crawler $node) {
                        return $node->filter('.col-xs-5.text-muted')->text('') === 'Retail price';
                    })->filter('.col-xs-7')?->text('');

                    // Extract Value under New/Sealed
                    $newSealedSection = $pricingDiv->filter('.semibold.bdr-b-l.pb-2')->reduce(function (Crawler $node) {
                        return stripos($node->text(''), 'New/Sealed') !== false;
                    });

                    $sealedValue = NULL;

                    if ($newSealedSection->count() > 0) {
                        $sealedValue = $newSealedSection->nextAll()->filter('.row.rowlist')->reduce(function (Crawler $node) {
                            return $node->filter('.col-xs-5.text-muted')->text('') === 'Value';
                        })->filter('.col-xs-7 b')->text('');
                    }

                    if ($sealedValue || $retailPrice) {
                        $product->prices()->create([
                            'date' => now()->format('Y-m-d'),
                            'value' =>  (float) preg_replace('/[^\d.]/', '', $sealedValue ?? $product->prices()->latest('date')?->value ?? $retailPrice),
                            'retail' => $retailPrice != '' ? ((float) preg_replace('/[^\d.]/', '', $retailPrice)) : NULL,
                            'currency' => 'USD',
                            'type' => PriceType::SCRAPED
                        ]);
                    }

                    $usedHeader = $pricingDiv->filter('.semibold.bdr-b-l.pb-2')->reduce(function (Crawler $node) {
                        return stripos($node->text(''), 'Used') !== false;
                    });

                    if ($usedHeader->count() > 0) {
                        $usedValue = NULL;
                        $usedRange = NULL;
                        // Get all following siblings
                        $nextRows = $usedHeader->nextAll()->filter('.row.rowlist');
                        foreach ($nextRows as $row) {
                            $rowCrawler = new Crawler($row);
                            $label = trim($rowCrawler->filter('.col-xs-5.text-muted')->text(''));
                            $value = trim($rowCrawler->filter('.col-xs-7')->text(''));
                            if ($label === 'Value') {
                                $usedValue = $value;
                            }
                            if (stripos($label, 'Range') !== false) {
                                $usedRange = $value;
                            }
                            // Stop if both found
                            if ($usedValue && $usedRange) {
                                $product->update([
                                    'used_price' => (float) preg_replace('/[^\d.]/', '', $usedValue),
                                    'used_range' => $usedRange,
                                ]);
                                break;
                            };
                        }
                    }
                }
            } else if ($product->product_type == 'minifig') {
                $pricingDiv = $crawler->filter('#ContentPlaceHolder1_PanelMinifigPricing');

                if ($pricingDiv->count() > 0) {
                    // Extract the "Value" row
                    $value = $pricingDiv->filter('.row.rowlist')->reduce(function (Crawler $node) {
                        return stripos($node->filter('.col-xs-5.text-muted')->text(''), 'Value') !== false;
                    })->filter('.col-xs-7 b')->text('');

                    if ($value != '') {
                        $product->prices()->create([
                            'date' => now()->format('Y-m-d'),
                            'value' => (float) preg_replace('/[^\d.]/', '', $value),
                            'currency' => 'USD',
                            'type' => PriceType::SCRAPED
                        ]);
                    }
                }
            }
        }



        if ($product) {
            $product?->update([
                'prices_count' => $product->prices()->count(),
                // 'scraped_at' => now()
            ]);
        }
    }
}
