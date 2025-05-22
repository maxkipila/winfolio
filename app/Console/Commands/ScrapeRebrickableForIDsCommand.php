<?php

namespace App\Console\Commands;

use App\Jobs\ScrapeRebrickableForIDs;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeRebrickableForIDsCommand extends Command
{
    protected $signature = 'scrape:rebrickable
                           {--limit=0 : Maximální počet minifigurek ke zpracování (0 = všechny)}
                           {--offset=0 : Začít od určitého offsetu}';

    protected $description = 'Automaticke scrapovani id produktu z rebrickable (kde je bricklink id) na brickeconomy_id';

    protected $requestCache = [];

    public function handle()
    {
        $limit = (int) $this->option('limit');
        $offset = (int) $this->option('offset');
        $this->scrape($this, $limit, $offset);
    }

    public static function scrape(Command $command, $limit = 0, $offset = 0)
    {
        DB::disableQueryLog();

        $command->info("Začátek scrapu IDček pro minifigurky.");
        // Najde minifigurky bez BrickEconomy ID a s product_id
        $query = Product::whereNull('brickeconomy_id')
            ->where('product_type', 'minifig')
            ->orderBy('id');

        // Aplikace offsetu a limitu
        if ($offset > 0) {
            $query->skip($offset);
        }

        if ($limit > 0) {
            $query->take($limit);
        }

        $totalToProcess = $query->count();
        $command->info("Celkem ke zpracování: {$totalToProcess} minifigurek");

        if ($totalToProcess === 0) {
            $command->info("Nebyly nalezeny žádné minifigurky ke zpracování.");
            return [];
        }

        $jobs =  collect([]);

        $command->withProgressBar($query->pluck('id'), function ($product_id) use($jobs) {
            $jobs->push(new ScrapeRebrickableForIDs($product_id));
        });

        return $jobs;
    }
}
