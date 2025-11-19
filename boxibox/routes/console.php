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

// Nettoyer les réservations expirées tous les jours à 2h du matin
Schedule::command('reservations:cleanup')
    ->dailyAt('02:00')
    ->timezone('Europe/Paris')
    ->description('Marquer les réservations expirées et libérer les boxes')
    ->onSuccess(function () {
        \Log::info('Expired reservations cleaned up successfully');
    })
    ->onFailure(function () {
        \Log::error('Expired reservations cleanup failed');
    });

// Traiter l'expiration des points de fidélité le 1er de chaque mois à 3h
Schedule::command('loyalty:process-expiry')
    ->monthlyOn(1, '03:00')
    ->timezone('Europe/Paris')
    ->description('Expirer les points de fidélité de plus de 12 mois')
    ->onSuccess(function () {
        \Log::info('Loyalty points expiry processed successfully');
    })
    ->onFailure(function () {
        \Log::error('Loyalty points expiry processing failed');
    });

// Générer les factures mensuelles le 1er de chaque mois à 1h
Schedule::command('invoices:generate-monthly')
    ->monthlyOn(1, '01:00')
    ->timezone('Europe/Paris')
    ->description('Générer les factures mensuelles pour tous les contrats actifs')
    ->onSuccess(function () {
        \Log::info('Monthly invoices generated successfully');
    })
    ->onFailure(function () {
        \Log::error('Monthly invoices generation failed');
    });

// Envoyer les rappels de renouvellement de contrat tous les jours à 9h
Schedule::command('contracts:send-renewal-reminders')
    ->dailyAt('09:00')
    ->timezone('Europe/Paris')
    ->description('Envoyer les rappels de renouvellement pour les contrats arrivant à échéance')
    ->onSuccess(function () {
        \Log::info('Contract renewal reminders sent successfully');
    })
    ->onFailure(function () {
        \Log::error('Contract renewal reminders sending failed');
    });

// Mettre à jour les prix dynamiques quotidiennement à 2h30
Schedule::command('pricing:update-all')
    ->dailyAt('02:30')
    ->timezone('Europe/Paris')
    ->description('Mettre à jour automatiquement les prix des boxes selon les règles de pricing dynamique')
    ->withoutOverlapping()
    ->onSuccess(function () {
        \Log::info('Dynamic pricing updated successfully');
    })
    ->onFailure(function () {
        \Log::error('Dynamic pricing update failed');
    });
