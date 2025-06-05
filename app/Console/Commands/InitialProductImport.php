<?php

namespace App\Console\Commands;

use App\Models\Theme;
use App\Traits\HasUserAgent;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class InitialProductImport extends Command
{
    use HasUserAgent;
    protected $signature = 'import:initial';

    protected $description = 'Import dat';

    public function handle()
    {
        ini_set('memory_limit', '512M');

        $sitemapPath = storage_path('app/html/sitemap.xml'); // Adjust path if needed

        try {
            $this->info('Downloading sitemap');
            if (file_exists($sitemapPath))
                unlink($sitemapPath);

            $this->getSitemap();
        } catch (\Throwable $th) {
            $this->fail($th);
        }

        if (!file_exists($sitemapPath))
            $this->fail("Sitemap doesn't exist");

        $xml = simplexml_load_file($sitemapPath);

        // Arrays to hold themes and subthemes
        $themes = [];
        $subthemes = [];
        $sets = [];
        $minifigs = [];

        // Parse each <url>
        foreach ($xml->url as $urlNode) {
            $loc = (string)$urlNode->loc;

            // Match theme URLs
            if (preg_match('#/sets/theme/([^/]+)$#', $loc, $m)) {
                $theme = $m[1];
                if (!isset($themes[$theme])) {
                    $themes[$theme] = [
                        'name' => $theme,
                        'subthemes' => []
                    ];
                }
            }

            // Match subtheme URLs
            if (preg_match('#/sets/theme/([^/]+)/subtheme/([^/]+)$#', $loc, $m)) {
                $theme = $m[1];
                $subtheme = $m[2];
                $themes[$theme]['subthemes'][] = $subtheme;
                $subthemes[] = [
                    'theme' => $theme,
                    'subtheme' => $subtheme
                ];
            }

            // For sets: /set/{id}/{slug}
            if (preg_match('#/set/([^/]+)/([^/]+)#', $loc, $m)) {
                $sets[] = [
                    'id' => $m[1],
                    'slug' => $m[2],
                ];
            }

            // For minifigs: /minifig/{id}/{slug}
            if (preg_match('#/minifig/([^/]+)/([^/]+)#', $loc, $m)) {
                $minifigs[] = [
                    'id' => $m[1],
                    'slug' => $m[2],
                ];
            }
        }

        function unslug($slug)
        {
            return ucwords(str_replace('-', ' ', $slug));
        }

        $this->info('Creating themes and subthemes');

        $this->withProgressBar($themes, function ($theme) {
            $t = Theme::firstOrCreate(
                [
                    'brickeconomy_id' => $theme['name']
                ],
                [
                    'name' => unslug($theme['name']),
                ]
            );

            foreach ($theme['subthemes'] ?? [] as $key => $subtheme) {
                Theme::updateOrCreate(
                    [
                        'brickeconomy_id' => $subtheme,
                        'parent_id' => $t->id
                    ],
                    [
                        'name' => unslug($subtheme),
                    ]
                );
            }
        });

        $this->info("\nScraping themes not in sitemap");

        $not_in_sitemap = [
            'gear'
        ];

        foreach ($not_in_sitemap as $key => $theme) {
            try {
                $this->info("Getting theme $theme");
                $this->getTheme("https://www.brickeconomy.com/$theme", $theme);
            } catch (\Throwable $th) {
                $this->info("Theme $theme couldn't be scraped: " . $th->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
