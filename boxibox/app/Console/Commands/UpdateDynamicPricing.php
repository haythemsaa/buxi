<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Services\DynamicPricingService;
use Illuminate\Console\Command;

class UpdateDynamicPricing extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pricing:update-all
                            {--site= : ID du site spécifique}
                            {--dry-run : Simulation sans mise à jour}';

    /**
     * The console command description.
     */
    protected $description = 'Met à jour les prix dynamiques de tous les boxes';

    /**
     * Execute the console command.
     */
    public function handle(DynamicPricingService $service)
    {
        $this->info('🔄 Mise à jour des prix dynamiques...');
        $this->newLine();

        $sites = $this->option('site')
            ? [Site::findOrFail($this->option('site'))]
            : Site::all();

        $totalUpdated = 0;

        foreach ($sites as $site) {
            $this->line("📍 Site: {$site->name}");

            $occupancy = $service->getOccupancyRate($site->id);
            $this->line("   Taux occupation: {$occupancy}%");

            if ($this->option('dry-run')) {
                $recommendations = $service->getPricingRecommendations($site);

                if (empty($recommendations)) {
                    $this->info('   ✓ Aucune recommandation (prix déjà optimaux)');
                } else {
                    $this->table(
                        ['Box', 'Prix Actuel', 'Prix Recommandé', 'Changement', 'Action'],
                        collect($recommendations)->map(fn($r) => [
                            $r['box_number'],
                            $r['current_price'] . '€',
                            $r['recommended_price'] . '€',
                            ($r['percentage_change'] > 0 ? '+' : '') . $r['percentage_change'] . '%',
                            $r['action'] === 'increase' ? '⬆️ Augmenter' : '⬇️ Réduire',
                        ])
                    );
                }
            } else {
                $updated = $service->updateSitePrices($site);
                $totalUpdated += $updated;
                $this->info("   ✓ {$updated} prix mis à jour");
            }

            $this->newLine();
        }

        if (!$this->option('dry-run')) {
            $this->info("✅ Total: {$totalUpdated} boxes mis à jour");
        } else {
            $this->warn('⚠️  Mode dry-run: aucune modification effectuée');
        }

        return 0;
    }
}
