<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class AssignThemesToMinifigs extends Command
{
    protected $signature = 'app:assign-themes-to-minifigs';
    protected $description = 'Přiřadí témata minifigurkám podle setů, ve kterých jsou obsaženy';

    public function handle()
    {
        ini_set('memory_limit', '1G');

        $this->info('přiřazování témat k minifigurkám...');

        $minifigs = Product::where('product_type', 'minifig')
            ->whereNull('theme_id')
            ->get();

        $count = $minifigs->count();
        $this->info("Celkem nalezeno {$count} minifigurek bez přiřazeného tématu");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $updated = 0;

        foreach ($minifigs as $minifig) {
            $sets = $minifig->sets()->whereNotNull('theme_id')->get();

            if ($sets->isNotEmpty()) {
                $themeId = $sets->groupBy('theme_id')
                    ->sortByDesc(function ($group) {
                        return $group->count();
                    })
                    ->keys()
                    ->first();

                if ($themeId) {
                    $minifig->theme_id = $themeId;
                    $minifig->save();
                    $updated++;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("Hotovo! Přiřazeno téma celkem {$updated} minifigurkám.");

        return Command::SUCCESS;
    }
}
