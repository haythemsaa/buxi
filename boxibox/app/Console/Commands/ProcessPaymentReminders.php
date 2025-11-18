<?php

namespace App\Console\Commands;

use App\Services\PaymentReminderService;
use Illuminate\Console\Command;

class ProcessPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:process
                            {--force : Force l\'envoi même en dehors des heures ouvrées}
                            {--dry-run : Simuler sans envoyer les rappels}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Traiter et envoyer les rappels de paiement pour les factures impayées';

    protected PaymentReminderService $reminderService;

    /**
     * Create a new command instance.
     */
    public function __construct(PaymentReminderService $reminderService)
    {
        parent::__construct();
        $this->reminderService = $reminderService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔄 Traitement des rappels de paiement...');
        $this->newLine();

        // Vérifier si c'est une heure appropriée (éviter la nuit)
        if (!$this->option('force') && !$this->isBusinessHours()) {
            $this->warn('⏰ Hors des heures ouvrées (8h-20h). Utilisez --force pour forcer l\'envoi.');
            return Command::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->warn('🧪 Mode simulation activé - Aucun rappel ne sera envoyé');
            $this->newLine();
        }

        try {
            // Traiter les rappels
            if ($this->option('dry-run')) {
                $results = $this->simulateReminders();
            } else {
                $results = $this->reminderService->processReminders();
            }

            // Afficher les résultats
            $this->displayResults($results);

            // Afficher les statistiques
            $this->displayStatistics();

            $this->newLine();
            $this->info('✅ Traitement terminé avec succès !');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors du traitement des rappels :');
            $this->error($e->getMessage());
            $this->newLine();

            if ($this->option('verbose')) {
                $this->error($e->getTraceAsString());
            }

            return Command::FAILURE;
        }
    }

    /**
     * Simuler le traitement des rappels
     */
    private function simulateReminders(): array
    {
        $overdueInvoices = \App\Models\Invoice::where('status', 'pending')
            ->where('due_date', '<', now())
            ->whereColumn('amount_paid', '<', 'total_ttc')
            ->count();

        $this->info("📋 {$overdueInvoices} facture(s) impayée(s) trouvée(s)");

        return [
            'processed' => $overdueInvoices,
            'sent' => $overdueInvoices,
            'errors' => 0,
            'skipped' => 0,
        ];
    }

    /**
     * Afficher les résultats
     */
    private function displayResults(array $results): void
    {
        $this->newLine();
        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Factures traitées', $results['processed']],
                ['Rappels envoyés', $results['sent']],
                ['Ignorés', $results['skipped']],
                ['Erreurs', $results['errors']],
            ]
        );
    }

    /**
     * Afficher les statistiques globales
     */
    private function displayStatistics(): void
    {
        $stats = $this->reminderService->getStatistics();

        $this->newLine();
        $this->info('📊 Statistiques globales :');
        $this->newLine();

        $this->table(
            ['Catégorie', 'Valeur'],
            [
                ['Total rappels', $stats['total']],
                ['Phase 1 (Rappel amical)', $stats['by_phase']['phase_1']],
                ['Phase 2 (Rappel ferme)', $stats['by_phase']['phase_2']],
                ['Phase 3 (Mise en demeure)', $stats['by_phase']['phase_3']],
                ['En attente', $stats['by_status']['pending']],
                ['Envoyés', $stats['by_status']['sent']],
                ['Payés', $stats['by_status']['paid']],
                ['Montant total impayé', number_format($stats['total_amount_overdue'], 2, ',', ' ') . ' €'],
                ['Pénalités totales', number_format($stats['total_late_fees'], 2, ',', ' ') . ' €'],
            ]
        );
    }

    /**
     * Vérifier si nous sommes en heures ouvrées
     */
    private function isBusinessHours(): bool
    {
        $hour = now()->hour;
        return $hour >= 8 && $hour < 20;
    }
}

