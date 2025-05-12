<?php

use Database\Seeders\PriceSeeder;
use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


if (!function_exists('schedule')) {
    function schedule(Schedule $schedule)
    {
        // Nastavení plánovače
        $schedule->command('app:update --mode=daily')
            ->dailyAt('03:00')
            ->timezone('Europe/Prague')
            ->description('Denní aktualizace cen a odznaků');

        $schedule->command('app:update --mode=weekly')
            ->weeklyOn(0, '02:00')  // Neděle ve 2:00
            ->timezone('Europe/Prague')
            ->description('Týdenní aktualizace cen a trendů');

        $schedule->command('app:update --mode=monthly')
            ->monthlyOn(1, '01:00')  // 1. den v měsíci v 1:00
            ->timezone('Europe/Prague')
            ->description('Měsíční aktualizace dat a import nových produktů');

        $schedule->command('app:maintenance --action=fix-prices')
            ->weeklyOn(1, '04:00')  // Pondělí ve 4:00
            ->timezone('Europe/Prague')
            ->description('Týdenní údržba cenových údajů');

        $schedule->command('telescope:prune --hours=48')
            ->daily()
            ->timezone('Europe/Prague');

        $schedule->command('app:check-awards')
            ->dailyAt('23:30')
            ->timezone('Europe/Prague')
            ->description('Kontrola odznaků');

        $schedule->command('app:update-user-records')
            ->dailyAt('23:40')
            ->timezone('Europe/Prague')
            ->description('Aktualizace uživatelských rekordů');

        $schedule->command('awards:notify')
            ->hourly()
            ->timezone('Europe/Prague')
            ->description('Odesílání notifikací o odznacích');

        $schedule->command('prices:aggregate')
            ->dailyAt('03:00')
            ->timezone('Europe/Prague')
            ->description('Agregace cen produktů');

        // Seeder commands using closure
        $schedule->call(function () {
            $seeder = new PriceSeeder();
            $seeder->seedPrices();
        })
            ->dailyAt('23:57')
            ->timezone('Europe/Prague')
            ->description('Denní seedování cen');

        $schedule->call(function () {
            $seeder = new PriceSeeder();
            $seeder->weeklyPriceUpdate();
        })
            ->weekly()
            ->timezone('Europe/Prague')
            ->description('Týdenní aktualizace cen');
    }
}

/* 
Původní zakomentovaný kód je ponechán pro referenci:

Schedule::command('telescope:prune --hours=48')->daily();

Schedule::command('app:check-awards')->dailyAt('23:30')->timezone('Europe/Prague'); //kontrola odznaku
Schedule::command('app:update-user-records')->dailyAt('23:40')->timezone('Europe/Prague'); //aktualizace uzivatelskych recordu
Schedule::command('import:lego-data')->dailyAt('23:50')->timezone('Europe/Prague'); //import dat
Schedule::command('import:lego-images --skip-existing')->dailyAt('23:53')->timezone('Europe/Prague'); //import obrazku
Schedule::command('prices:aggregate --all')->dailyAt('03:00')->timezone('Europe/Prague');

Schedule::call(function () {
    $seeder = new PriceSeeder();
    $seeder->seedPrices();
})->dailyAt('23:57')->timezone('Europe/Prague');

Schedule::call(function () {
    $seeder = new PriceSeeder();

    $seeder->weeklyPriceUpdate();
})->weekly()->timezone('Europe/Prague');

Schedule::command('awards:notify')->hourly();

Schedule::command('prices:update-historical')->dailyAt('01:00')->timezone('Europe/Prague');

//predpocitavani trndu
Schedule::command('app:calculate-trends')->dailyAt('02:00')->timezone('Europe/Prague'); 
*/