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

Schedule::command('telescope:prune --hours=48')->daily();

Schedule::command('import:lego-data')->dailyAt('23:50')->timezone('Europe/Prague');

Schedule::call(function () {
    $seeder = new PriceSeeder();
    $seeder->seedPrices();
})->dailyAt('23:55')->timezone('Europe/Prague');

Schedule::call(function () {
    $seeder = new PriceSeeder();

    $seeder->weeklyPriceUpdate();
})->weekly()->timezone('Europe/Prague');
