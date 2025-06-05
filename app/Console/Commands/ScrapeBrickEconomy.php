<?php

namespace App\Console\Commands;

use App\Jobs\ScrapeBrickEconomy as JobsScrapeBrickEconomy;
use App\Models\Product;
use App\Models\Theme;
use App\Traits\HasUserAgent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ScrapeBrickEconomy extends Command
{
    use HasUserAgent;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:brickeconomy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $sitemapPath = storage_path('app/html/sitemap.xml'); // Adjust path if needed

        // try {
        //     $this->info('Downloading sitemap');
        //     if (file_exists($sitemapPath))
        //         unlink($sitemapPath);

        //     $this->getSitemap();
        // } catch (\Throwable $th) {
        //     $this->fail($th);
        // }

        // if (!file_exists($sitemapPath))
        //     $this->fail("Sitemap doesn't exist");

        // $xml = simplexml_load_file($sitemapPath);

        // // Arrays to hold themes and subthemes
        // $themes = [];
        // $subthemes = [];
        // $sets = [];
        // $minifigs = [];

        // // Parse each <url>
        // foreach ($xml->url as $urlNode) {
        //     $loc = (string)$urlNode->loc;

        //     // Match theme URLs
        //     if (preg_match('#/sets/theme/([^/]+)$#', $loc, $m)) {
        //         $theme = $m[1];
        //         if (!isset($themes[$theme])) {
        //             $themes[$theme] = [
        //                 'name' => $theme,
        //                 'subthemes' => []
        //             ];
        //         }
        //     }

        //     // Match subtheme URLs
        //     if (preg_match('#/sets/theme/([^/]+)/subtheme/([^/]+)$#', $loc, $m)) {
        //         $theme = $m[1];
        //         $subtheme = $m[2];
        //         $themes[$theme]['subthemes'][] = $subtheme;
        //         $subthemes[] = [
        //             'theme' => $theme,
        //             'subtheme' => $subtheme
        //         ];
        //     }

        //     // For sets: /set/{id}/{slug}
        //     if (preg_match('#/set/([^/]+)/([^/]+)#', $loc, $m)) {
        //         $sets[] = [
        //             'id' => $m[1],
        //             'slug' => $m[2],
        //         ];
        //     }

        //     // For minifigs: /minifig/{id}/{slug}
        //     if (preg_match('#/minifig/([^/]+)/([^/]+)#', $loc, $m)) {
        //         $minifigs[] = [
        //             'id' => $m[1],
        //             'slug' => $m[2],
        //         ];
        //     }
        // }


        // function unslug($slug)
        // {
        //     return ucwords(str_replace('-', ' ', $slug));
        // }

        // $this->info('Creating themes and subthemes');

        // $this->withProgressBar($themes, function ($theme) {
        //     $t = Theme::firstOrCreate(
        //         [
        //             'brickeconomy_id' => $theme['name']
        //         ],
        //         [
        //             'name' => unslug($theme['name']),
        //         ]
        //     );

        //     foreach ($theme['subthemes'] ?? [] as $key => $subtheme) {
        //         Theme::updateOrCreate(
        //             [
        //                 'brickeconomy_id' => $subtheme,
        //                 'parent_id' => $t->id
        //             ],
        //             [
        //                 'name' => unslug($subtheme),
        //             ]
        //         );
        //     }
        // });

        // $this->info("Scraping themes not in sitemap");

        // $not_in_sitemap = [
        //     'gear'
        // ];

        // foreach ($not_in_sitemap as $key => $theme) {
        //     try {
        //         $this->info("Getting theme $theme");
        //         $this->getTheme("https://www.brickeconomy.com/$theme", $theme);
        //     } catch (\Throwable $th) {
        //         $this->info("Theme $theme couldn't be scraped: " . $th->getMessage());
        //     }
        // }

        // $this->info("\nCreating sets");

        // $this->withProgressBar(
        //     $sets,
        //     fn($set) =>  Product::firstOrCreate(
        //         [
        //             'brickeconomy_id' => $set['id']
        //         ],
        //         [
        //             'product_num' => $set['id'],
        //             'product_type' => 'set',
        //             'name' => unslug($set['slug'])
        //         ]
        //     )
        // );

        // $this->info("\nCreating minifigs");
        // $this->withProgressBar(
        //     $minifigs,
        //     fn($minifig) =>  Product::firstOrCreate(
        //         [
        //             'brickeconomy_id' => $minifig['id']
        //         ],
        //         [
        //             'product_num' => $minifig['id'],
        //             'product_type' => 'minifig',
        //             'name' => unslug($minifig['slug'])
        //         ]
        //     )
        // );

        // file_put_contents(storage_path('app/html/themes.json'), json_encode($themes, JSON_PRETTY_PRINT));
        // file_put_contents(storage_path('app/html/sets.json'), json_encode($sets, JSON_PRETTY_PRINT));
        // file_put_contents(storage_path('app/html/minifigs.json'), json_encode($minifigs, JSON_PRETTY_PRINT));

        $products = Product::whereNull('scraped_at')->orWhere('scraped_at', '<', now()->startOfMonth())->pluck('id');

        $chunkSize = 10; // Number of products per batch
        $chunks = $products->chunk($chunkSize); // Split products into chunks
        $length = $chunks->count() - 1;

        foreach ($chunks as $key => $chunk) {
            JobsScrapeBrickEconomy::dispatch($chunk, true, false, true)->onQueue('scraping');
            $this->info("Chunk $key/{$length} dispatched");
        }

        $this->info("All chunks dispatched");
    }
}
