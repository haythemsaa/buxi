<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Task Scheduling
|--------------------------------------------------------------------------
|
| Here you may define all of your scheduled tasks. These tasks will be
| executed by the Laravel scheduler according to the defined frequency.
|
*/

// Traiter les rappels de paiement quotidiennement à 10h
Schedule::command('reminders:process')
    ->dailyAt('10:00')
    ->timezone('Europe/Paris')
    ->description('Traiter et envoyer les rappels de paiement')
    ->onSuccess(function () {
        \Log::info('Payment reminders processed successfully');
    })
    ->onFailure(function () {
        \Log::error('Payment reminders processing failed');
    });

// Alternative : Exécuter plusieurs fois par jour (matin et après-midi)
// Schedule::command('reminders:process')
//     ->twiceDaily(10, 15)
//     ->timezone('Europe/Paris');

// Alternative : Exécuter tous les jours ouvrés à 10h
// Schedule::command('reminders:process')
//     ->weekdays()
//     ->dailyAt('10:00')
//     ->timezone('Europe/Paris');
