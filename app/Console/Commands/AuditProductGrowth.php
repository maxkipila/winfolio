<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\TrendService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditProductGrowth extends Command
{
    protected $signature = 'app:audit-product-growth {--product_id= : ID konkrétního produktu}';
    protected $description = 'Audit růstu produktů a porovnání s hodnotami v UI';

    public function handle(TrendService $trendService)
    {
        ini_set('memory_limit', '1G');
        // Disable session and logging to reduce memory overhead in CLI
        config(['session.driver' => 'array']);
        config(['logging.default' => 'null']);
        $productId = $this->option('product_id');

        // Select only necessary fields and eager load latest_price
        $query = Product::select('id', 'name')->with('latest_price');
        if ($productId) {
            $query->where('id', $productId);
        }

        // Disable query logging to save memory
        DB::connection()->disableQueryLog();

        $this->info('Začínám audit růstu produktů...');
        $this->info('ID | Název | Retail | Value | Vypočtený růst | Problém?');

        foreach ($query->cursor() as $product) {
            $retail = $product->latest_price ? $product->latest_price->retail : 'N/A';
            $value = $product->latest_price ? $product->latest_price->value : 'N/A';
            $growth = $trendService->calculateGrowthForProductOptimized($product->id, 7) ?? 'N/A';
            $problem = 'N/A';
            if ($retail !== 'N/A' && $value !== 'N/A' && $retail > 0) {
                $manualGrowth = (($value - $retail) / $retail) * 100;
                $problem = abs($manualGrowth - $growth) > 10 ? 'ANO' : 'NE';
            }
            $this->line("{$product->id} | {$product->name} | {$retail} | {$value} | {$growth} | {$problem}");
            unset($product);
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }

        $this->info('Audit dokončen.');
        return Command::SUCCESS;
    }
}
