<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('telescope:prune --hours=48')->daily();

Schedule::command('app:check-awards')->daily()->timezone('Europe/Prague'); //kontrola odznaku
Schedule::command('app:calculate-trends')->dailyAt('02:00')->timezone('Europe/Prague'); //vypocet trendu
Schedule::command('app:update-user-records')->daily()->timezone('Europe/Prague'); //aktualizace uzivatelskych recordu

// Schedule::command('awards:notify')->everyFiveMinutes()->timezone('Europe/Prague');
// Schedule::command('import:lego-data')->dailyAt('23:50')->timezone('Europe/Prague'); //import dat
// Schedule::command('import:lego-images')->dailyAt('23:53')->timezone('Europe/Prague'); //import obrazku
// Schedule::command('prices:aggregate ')->dailyAt('03:00')->timezone('Europe/Prague');



/* Schedule::call(function () {
    $seeder = new PriceSeeder();
    $seeder->seedPrices();
})->dailyAt('23:57')->timezone('Europe/Prague');

Schedule::call(function () {
    $seeder = new PriceSeeder();

    $seeder->weeklyPriceUpdate();
})->weekly()->timezone('Europe/Prague'); */


/* Schedule::command('prices:update-historical')->dailyAt('01:00')->timezone('Europe/Prague'); */

//predpocitavani trndu
