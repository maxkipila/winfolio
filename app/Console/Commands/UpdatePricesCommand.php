<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\PriceSeeder;

class UpdatePricesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Použitím volby --weekly spustíte týdenní aktualizaci,
     * jinak se spustí denní seedování cen.
     *
     * @var string
     */
    protected $signature = 'prices:update {--weekly : Run the weekly price update instead of the daily price seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually update product prices. If --weekly is provided, runs weekly update; otherwise runs daily price seeding.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $seeder = new PriceSeeder();

        if ($this->option('weekly')) {
            $this->info('Running weekly price update...');
            $seeder->weeklyPriceUpdate();
        } else {
            $this->info('Running daily price seeding...');

            $seeder->seedPrices();
        }

        $this->info('Price update completed successfully.');
        return \Symfony\Component\Console\Command\Command::SUCCESS;
    }
}
