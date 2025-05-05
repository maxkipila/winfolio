<?php

namespace App\Console\Commands;

use App\Models\LegoIdMapping;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateMinifigMappingsCommand extends Command
{
    protected $signature = 'lego:update-minifig-mappings
                           {--chunk=500 : Počet záznamů zpracovávaných v jedné dávce}
                           {--limit=0 : Omezení počtu zpracovaných záznamů}
                           {--offset=0 : Začít od určitého offsetu}';

    protected $description = 'Update BrickEconomy IDs for existing minifig mappings based on patterns';

    public function handle()
    {
        // Nastavení
        $chunkSize = (int) $this->option('chunk');
        $limit = (int) $this->option('limit');
        $offset = (int) $this->option('offset');

        // Zvýšení limitu paměti
        ini_set('memory_limit', '1G');

        // Vypnutí query logu pro úsporu paměti
        DB::disableQueryLog();

        $this->info('Začínám aktualizaci mapování pro minifigurky...');

        // Získáme všechny minifigurky bez BrickEconomy ID
        $query = LegoIdMapping::whereNull('brickeconomy_id')
            ->where('rebrickable_id', 'LIKE', 'fig-%')
            ->orderBy('id');

        // Aplikace offsetu
        if ($offset > 0) {
            $query->skip($offset);
        }

        // Aplikace limitu
        if ($limit > 0) {
            $query->take($limit);
        }

        // Počet záznamů ke zpracování
        $totalCount = $query->count();
        $this->info("Celkem ke zpracování: {$totalCount} minifigurek");

        // Progress bar
        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        $updatedCount = 0;

        // Známé prefixy pro různé série
        $seriesPrefixes = [
            'Star Wars' => 'sw',
            'Harry Potter' => 'hp',
            'Batman' => 'bat',
            'Marvel' => 'sh',
            'Super Heroes' => 'sh',
            'DC' => 'sh',
            'Spider-Man' => 'sh',
            'Iron Man' => 'sh',
            'Captain America' => 'sh',
            'Ninjago' => 'njo',
            'Lord of the Rings' => 'lor',
            'Hobbit' => 'lor',
            'Indiana Jones' => 'iaj',
            'Pirates of the Caribbean' => 'poc',
            'City' => 'cty',
            'Town' => 'twn',
            'Castle' => 'cas',
            'Space' => 'sp',
            'Friends' => 'frnd',
            'Minecraft' => 'min',
            'Disney' => 'dis',
            'Simpsons' => 'sim',
            'Chima' => 'loc',
            'Jurassic World' => 'jw',
            'Hidden Side' => 'hs',
            'Stranger Things' => 'st',
            'Speed Champions' => 'sc',
            'Overwatch' => 'ow',
            'Ghost Busters' => 'gb',
            'Creator' => 'col',
            'Collectible Minifigures' => 'col',
        ];

        // Zpracování po dávkách
        $query->chunk($chunkSize, function ($mappings) use (&$updatedCount, $bar, $seriesPrefixes) {
            foreach ($mappings as $mapping) {
                // Extrakt identifikátoru z ID
                preg_match('/fig-(\d+)/', $mapping->rebrickable_id, $matches);
                $numericId = $matches[1] ?? null;

                // Pokusíme se určit BrickEconomy ID podle názvů a známých vzorů
                $brickEconomyId = null;

                // 1. Podle série v názvu
                foreach ($seriesPrefixes as $serieName => $prefix) {
                    if (
                        str_contains($mapping->name, $serieName) ||
                        str_contains($mapping->notes ?? '', $serieName)
                    ) {
                        // Pro série máme prefix + číslo (např. sw0123)
                        if ($numericId) {
                            $brickEconomyId = $prefix . str_pad($numericId, 4, '0', STR_PAD_LEFT);
                            break;
                        }
                    }
                }

                // 2. Detekce podle názvu postavy (jen některé známé postavy)
                if (!$brickEconomyId) {
                    $characterMappings = [
                        'Darth Vader' => 'sw',
                        'Luke Skywalker' => 'sw',
                        'Princess Leia' => 'sw',
                        'Han Solo' => 'sw',
                        'Chewbacca' => 'sw',
                        'Yoda' => 'sw',
                        'R2-D2' => 'sw',
                        'C-3PO' => 'sw',
                        'Stormtrooper' => 'sw',
                        'Boba Fett' => 'sw',
                        'Harry Potter' => 'hp',
                        'Hermione' => 'hp',
                        'Ron Weasley' => 'hp',
                        'Dumbledore' => 'hp',
                        'Hagrid' => 'hp',
                        'Voldemort' => 'hp',
                        'Batman' => 'bat',
                        'Joker' => 'bat',
                        'Robin' => 'bat',
                        'Superman' => 'sh',
                        'Spider-Man' => 'sh',
                        'Iron Man' => 'sh',
                        'Captain America' => 'sh',
                        'Thor' => 'sh',
                        'Hulk' => 'sh',
                        'Black Widow' => 'sh',
                        'Gandalf' => 'lor',
                        'Frodo' => 'lor',
                        'Legolas' => 'lor',
                        'Aragorn' => 'lor',
                        'Gollum' => 'lor',
                        'Indiana Jones' => 'iaj',
                        'Jack Sparrow' => 'poc',
                    ];

                    foreach ($characterMappings as $character => $prefix) {
                        if (str_contains($mapping->name, $character)) {
                            if ($numericId) {
                                $brickEconomyId = $prefix . str_pad($numericId, 4, '0', STR_PAD_LEFT);
                                break;
                            }
                        }
                    }
                }

                // Pokud jsme našli BrickEconomy ID, aktualizujeme
                if ($brickEconomyId) {
                    $mapping->brickeconomy_id = $brickEconomyId;
                    $mapping->notes = ($mapping->notes ?? '') . ' | Pattern-matched: ' . $brickEconomyId;
                    $mapping->save();
                    $updatedCount++;
                }

                $bar->advance();
            }

            // Vyčištění paměti
            gc_collect_cycles();
        });

        $bar->finish();
        $this->newLine();

        $this->info("Aktualizováno {$updatedCount} záznamů z celkových {$totalCount}.");

        // Počet zbývajících minifigurek bez mapování
        $remainingCount = LegoIdMapping::whereNull('brickeconomy_id')
            ->where('rebrickable_id', 'LIKE', 'fig-%')
            ->count();

        $this->info("Zbývá doplnit {$remainingCount} minifigurek.");

        return 0;
    }
}
