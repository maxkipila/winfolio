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

class ProcessScrapedBrickEconomyThemes implements ShouldQueue
{
    use Queueable;

    public Theme $theme;

    /**
     * Create a new job instance.
     */
    public function __construct(public $file_path, public $theme_id)
    {
        $this->theme = Theme::firstOrCreate(
            [
                'brickeconomy_id' => $theme_id,
                'parent_id' => NULL
            ],
            [
                'name' => ucwords(str_replace('-', ' ', $theme_id))
            ]
        );
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $html = file_get_contents($this->file_path);
        $this->parseThemeInfo($html);

        if (file_exists($this->file_path))
            unlink($this->file_path);
    }

    public function parseThemeInfo($html)
    {

        $crawler = new Crawler($html);

        // Find the theme name from the Theme row
        $themeName = null;
        $themeKey = null;
        $panel = $crawler->filter('#ContentPlaceHolder1_PanelThemeDetails');
        if ($panel->count() > 0) {
            $panel->filter('.row.rowlist')->each(function ($row) use (&$themeName, &$themeKey) {
                $label = trim($row->filter('.col-xs-5.text-muted')->text(''));
                if (strtolower($label) === 'theme') {
                    $themeName = trim($row->filter('.col-xs-7')->text(''));
                    $themeKey = strtolower(str_replace([' ', '/'], ['-', '-'], $themeName));
                }
            });
        }

        // Find subthemes
        $subthemes = [];
        if ($panel->count() > 0) {
            $panel->filter('.row.rowlist')->each(function ($row) use (&$subthemes) {
                $label = trim($row->filter('.col-xs-5.text-muted')->text(''));
                if (strtolower($label) === 'subthemes') {
                    // Find all <a> in the dropdown menu
                    $row->filter('ul.dropdown-menu li a')->each(function ($a) use (&$subthemes) {
                        $href = $a->attr('href');
                        $name = trim($a->text());
                        if (preg_match('#/[^/]+/subtheme/([^/]+)#', $href, $m)) {
                            $subthemes[$m[1]] = $name;
                        }
                    });
                }
            });
        }

        if ($themeKey && $themeName) {
            $result = [
                $themeKey => [
                    'name' => $themeName,
                    'subthemes' => $subthemes,
                ]
            ];

            $this->theme->update([
                'name' => $themeName
            ]);

            foreach ($subthemes ?? [] as $key => $subtheme) {
                Theme::updateOrCreate(
                    [
                        'brickeconomy_id' => $key,
                        'parent_id' => $this->theme->id
                    ],
                    [
                        'name' => $subtheme,
                    ]
                );
            }


            file_put_contents(storage_path("app/html/theme_{$this->theme_id}.json"), json_encode($result, JSON_PRETTY_PRINT));

            // Example: log or return the result
            Log::info('Extracted theme and subthemes', $result);
            return $result;
        }

        return [];
    }
}
