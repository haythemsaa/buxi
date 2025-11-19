<?php

namespace Database\Seeders;

use App\Models\Box;
use App\Models\Building;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Floor;
use App\Models\Invoice;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyTransaction;
use App\Models\Payment;
use App\Models\PaymentReminder;
use App\Models\PriceRule;
use App\Models\Promotion;
use App\Models\Reservation;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompleteDemoSeeder extends Seeder
{
    /**
     * Run the complete demo database seeder.
     *
     * This seeder creates a realistic and comprehensive demo environment
     * with all features populated for immediate testing and demonstration.
     */
    public function run(): void
    {
        $this->command->info('🌱 Démarrage du seeding complet de démonstration...');
        $this->command->newLine();

        // 1. Créer les sites (2 sites)
        $this->command->info('📍 Création des sites...');
        $sites = $this->createSites();
        $this->command->info("   ✓ {$sites->count()} sites créés");

        // 2. Créer les bâtiments, étages et boxes
        $this->command->info('🏢 Création des bâtiments, étages et boxes...');
        $boxes = $this->createBoxes($sites);
        $this->command->info("   ✓ {$boxes->count()} boxes créés");

        // 3. Créer les promotions
        $this->command->info('🎁 Création des promotions...');
        $promotions = $this->createPromotions();
        $this->command->info("   ✓ {$promotions->count()} promotions créées");

        // 4. Créer les règles de prix
        $this->command->info('💰 Création des règles de prix...');
        $priceRules = $this->createPriceRules($sites);
        $this->command->info("   ✓ {$priceRules->count()} règles de prix créées");

        // 5. Créer les clients
        $this->command->info('👥 Création des clients...');
        $customers = $this->createCustomers();
        $this->command->info("   ✓ {$customers->count()} clients créés");

        // 6. Créer les contrats
        $this->command->info('📋 Création des contrats...');
        $contracts = $this->createContracts($customers, $boxes);
        $this->command->info("   ✓ {$contracts->count()} contrats créés");

        // 7. Créer les factures
        $this->command->info('🧾 Création des factures...');
        $invoices = $this->createInvoices($contracts);
        $this->command->info("   ✓ {$invoices->count()} factures créées");

        // 8. Créer les paiements
        $this->command->info('💳 Création des paiements...');
        $payments = $this->createPayments($invoices);
        $this->command->info("   ✓ {$payments->count()} paiements créés");

        // 9. Créer les rappels de paiement
        $this->command->info('⏰ Création des rappels de paiement...');
        $reminders = $this->createPaymentReminders($invoices);
        $this->command->info("   ✓ {$reminders->count()} rappels créés");

        // 10. Créer les réservations
        $this->command->info('🎫 Création des réservations...');
        $reservations = $this->createReservations($customers, $boxes);
        $this->command->info("   ✓ {$reservations->count()} réservations créées");

        // 11. Créer les points de fidélité
        $this->command->info('🌟 Création des points de fidélité...');
        $this->createLoyaltyPoints($customers);
        $this->command->info("   ✓ Points de fidélité créés pour {$customers->count()} clients");

        // 12. Créer un utilisateur admin
        $this->command->info('👤 Création de l'utilisateur admin...');
        $this->createAdminUser();
        $this->command->info("   ✓ Utilisateur admin créé");

        $this->command->newLine();
        $this->command->info('🎉 Seeding terminé avec succès!');
        $this->command->newLine();

        $this->showCredentials();
    }

    private function createSites()
    {
        return collect([
            Site::create([
                'name' => 'Boxibox Paris Nord',
                'code' => 'PARIS_NORD',
                'address' => '123 Avenue du Stockage',
                'postal_code' => '75018',
                'city' => 'Paris',
                'country' => 'France',
                'phone' => '01 40 00 00 00',
                'email' => 'paris@boxibox.fr',
                'gps_latitude' => 48.8566,
                'gps_longitude' => 2.3522,
                'status' => 'active',
                'opening_hours' => json_encode([
                    'lundi' => '08:00-20:00',
                    'mardi' => '08:00-20:00',
                    'mercredi' => '08:00-20:00',
                    'jeudi' => '08:00-20:00',
                    'vendredi' => '08:00-20:00',
                    'samedi' => '09:00-18:00',
                    'dimanche' => 'Fermé',
                ]),
            ]),
            Site::create([
                'name' => 'Boxibox Lyon Centre',
                'code' => 'LYON_CENTRE',
                'address' => '45 Rue de la République',
                'postal_code' => '69002',
                'city' => 'Lyon',
                'country' => 'France',
                'phone' => '04 78 00 00 00',
                'email' => 'lyon@boxibox.fr',
                'gps_latitude' => 45.7640,
                'gps_longitude' => 4.8357,
                'status' => 'active',
                'opening_hours' => json_encode([
                    'lundi' => '08:00-20:00',
                    'mardi' => '08:00-20:00',
                    'mercredi' => '08:00-20:00',
                    'jeudi' => '08:00-20:00',
                    'vendredi' => '08:00-20:00',
                    'samedi' => '09:00-18:00',
                    'dimanche' => 'Fermé',
                ]),
            ]),
        ]);
    }

    private function createBoxes($sites)
    {
        $boxes = collect();

        foreach ($sites as $site) {
            // Créer 2 bâtiments par site
            for ($b = 1; $b <= 2; $b++) {
                $building = Building::create([
                    'site_id' => $site->id,
                    'name' => "Bâtiment $b",
                    'code' => "BAT$b",
                ]);

                // Créer 3 étages par bâtiment
                for ($f = 0; $f <= 2; $f++) {
                    $floorName = $f === 0 ? 'Rez-de-chaussée' : "Étage $f";
                    $floor = Floor::create([
                        'building_id' => $building->id,
                        'name' => $floorName,
                        'level' => $f,
                    ]);

                    // Créer 10 boxes par étage
                    for ($box = 1; $box <= 10; $box++) {
                        $boxNumber = "$b-$f" . str_pad($box, 2, '0', STR_PAD_LEFT);

                        // Varier les tailles
                        $sizes = [
                            ['volume' => 5, 'surface' => 2.5, 'length' => 2, 'width' => 1.25, 'height' => 2, 'price' => 60],
                            ['volume' => 10, 'surface' => 5, 'length' => 2.5, 'width' => 2, 'height' => 2, 'price' => 90],
                            ['volume' => 15, 'surface' => 7.5, 'length' => 3, 'width' => 2.5, 'height' => 2, 'price' => 120],
                            ['volume' => 20, 'surface' => 10, 'length' => 4, 'width' => 2.5, 'height' => 2, 'price' => 150],
                        ];

                        $size = $sizes[array_rand($sizes)];

                        // Statuts variés
                        $statuses = ['available' => 70, 'rented' => 25, 'maintenance' => 5];
                        $status = $this->weightedRandom($statuses);

                        $boxes->push(Box::create([
                            'site_id' => $site->id,
                            'floor_id' => $floor->id,
                            'number' => $boxNumber,
                            'volume' => $size['volume'],
                            'surface' => $size['surface'],
                            'length' => $size['length'],
                            'width' => $size['width'],
                            'height' => $size['height'],
                            'price_ht' => $size['price'],
                            'climate_controlled' => rand(0, 1),
                            'ground_floor' => $f === 0,
                            'vehicle_access' => rand(0, 1),
                            'has_electricity' => rand(0, 1),
                            'status' => $status,
                        ]));
                    }
                }
            }
        }

        return $boxes;
    }

    private function createPromotions()
    {
        return collect([
            Promotion::create([
                'code' => 'BIENVENUE30',
                'name' => 'Bienvenue 30%',
                'description' => '30% de réduction sur le premier mois',
                'discount_type' => 'percentage',
                'discount_value' => 30,
                'valid_from' => now()->subDays(30),
                'valid_until' => now()->addMonths(6),
                'new_customers_only' => true,
                'online_only' => true,
                'status' => 'active',
            ]),
            Promotion::create([
                'code' => 'PREMIER_MOIS_GRATUIT',
                'name' => 'Premier Mois Gratuit',
                'description' => 'Votre premier mois est offert',
                'discount_type' => 'first_month_free',
                'discount_value' => 1,
                'valid_from' => now()->subDays(15),
                'valid_until' => now()->addMonths(3),
                'min_duration_months' => 12,
                'status' => 'active',
            ]),
            Promotion::create([
                'code' => 'NOEL50',
                'name' => 'Spécial Noël -50€',
                'description' => '50€ de réduction sur votre première facture',
                'discount_type' => 'fixed_amount',
                'discount_value' => 50,
                'valid_from' => now()->subDays(7),
                'valid_until' => now()->addMonths(1),
                'status' => 'active',
            ]),
        ]);
    }

    private function createPriceRules($sites)
    {
        $rules = collect();

        // Règle durée 12 mois
        $rules->push(PriceRule::create([
            'name' => 'Réduction 12 mois',
            'type' => 'duration_discount',
            'conditions' => json_encode(['min_duration' => 12]),
            'discount_percentage' => 10,
            'priority' => 10,
            'is_active' => true,
        ]));

        // Règle durée 6 mois
        $rules->push(PriceRule::create([
            'name' => 'Réduction 6 mois',
            'type' => 'duration_discount',
            'conditions' => json_encode(['min_duration' => 6]),
            'discount_percentage' => 5,
            'priority' => 5,
            'is_active' => true,
        ]));

        return $rules;
    }

    private function createCustomers()
    {
        $customers = collect();

        // 20 clients variés
        for ($i = 1; $i <= 20; $i++) {
            $type = $i % 5 === 0 ? 'company' : 'individual';

            if ($type === 'individual') {
                $customer = Customer::create([
                    'customer_number' => 'CL' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'type' => 'individual',
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => "client$i@example.com",
                    'password' => Hash::make('password'),
                    'phone' => fake()->phoneNumber(),
                    'address' => fake()->streetAddress(),
                    'postal_code' => fake()->postcode(),
                    'city' => fake()->city(),
                    'country' => 'France',
                    'status' => 'active',
                ]);
            } else {
                $customer = Customer::create([
                    'customer_number' => 'CL' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'type' => 'company',
                    'company_name' => fake()->company(),
                    'siret' => fake()->numerify('##############'),
                    'email' => "company$i@example.com",
                    'password' => Hash::make('password'),
                    'phone' => fake()->phoneNumber(),
                    'address' => fake()->streetAddress(),
                    'postal_code' => fake()->postcode(),
                    'city' => fake()->city(),
                    'country' => 'France',
                    'status' => 'active',
                ]);
            }

            // Créer l'utilisateur associé
            User::create([
                'name' => $customer->name,
                'email' => $customer->email,
                'password' => Hash::make('password'),
                'userable_type' => Customer::class,
                'userable_id' => $customer->id,
            ]);

            $customers->push($customer);
        }

        return $customers;
    }

    private function createContracts($customers, $boxes)
    {
        $contracts = collect();
        $rentedBoxes = $boxes->where('status', 'rented');

        foreach ($rentedBoxes->take(15) as $index => $box) {
            $customer = $customers->random();

            $contract = Contract::create([
                'contract_number' => 'CO' . date('Ymd') . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'box_id' => $box->id,
                'site_id' => $box->site_id,
                'start_date' => now()->subMonths(rand(1, 24)),
                'initial_duration_months' => 12,
                'price_monthly_ht' => $box->price_ht,
                'tax_rate' => 20,
                'deposit_amount' => $box->price_ht * 2,
                'payment_method' => ['sepa', 'card', 'bank_transfer'][rand(0, 2)],
                'payment_day' => 5,
                'access_code' => str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'status' => 'active',
            ]);

            $contracts->push($contract);
        }

        return $contracts;
    }

    private function createInvoices($contracts)
    {
        $invoices = collect();

        foreach ($contracts as $contract) {
            // Créer 3-6 factures par contrat
            $monthsCount = rand(3, 6);

            for ($m = 0; $m < $monthsCount; $m++) {
                $issueDate = now()->subMonths($monthsCount - $m)->startOfMonth();
                $dueDate = $issueDate->copy()->addDays(15);

                $subtotalHt = $contract->price_monthly_ht;
                $taxAmount = round($subtotalHt * ($contract->tax_rate / 100), 2);
                $totalTtc = $subtotalHt + $taxAmount;

                // Status aléatoire basé sur l'ancienneté
                if ($m < $monthsCount - 2) {
                    $status = 'paid';
                    $amountPaid = $totalTtc;
                    $paidAt = $issueDate->copy()->addDays(rand(1, 10));
                } elseif ($m === $monthsCount - 2) {
                    $status = rand(0, 1) ? 'paid' : 'pending';
                    $amountPaid = $status === 'paid' ? $totalTtc : 0;
                    $paidAt = $status === 'paid' ? $issueDate->copy()->addDays(rand(1, 10)) : null;
                } else {
                    // Dernière facture - parfois en retard
                    if (rand(0, 100) < 30 && $dueDate->isPast()) {
                        $status = 'overdue';
                        $amountPaid = 0;
                        $paidAt = null;
                    } else {
                        $status = 'pending';
                        $amountPaid = 0;
                        $paidAt = null;
                    }
                }

                $invoice = Invoice::create([
                    'invoice_number' => 'INV-' . $issueDate->format('Ymd') . '-' . str_pad($contract->id * 10 + $m, 6, '0', STR_PAD_LEFT),
                    'contract_id' => $contract->id,
                    'customer_id' => $contract->customer_id,
                    'site_id' => $contract->site_id,
                    'type' => 'rent',
                    'issue_date' => $issueDate,
                    'due_date' => $dueDate,
                    'period_start' => $issueDate->copy()->subMonth(),
                    'period_end' => $issueDate,
                    'subtotal_ht' => $subtotalHt,
                    'tax_amount' => $taxAmount,
                    'tax_rate' => $contract->tax_rate,
                    'total_ttc' => $totalTtc,
                    'line_items' => json_encode([[
                        'description' => 'Location box ' . $contract->box->number . ' - ' . $issueDate->format('F Y'),
                        'quantity' => 1,
                        'unit_price' => $subtotalHt,
                        'total' => $subtotalHt,
                    ]]),
                    'status' => $status,
                    'amount_paid' => $amountPaid,
                    'paid_at' => $paidAt,
                ]);

                $invoices->push($invoice);
            }
        }

        return $invoices;
    }

    private function createPayments($invoices)
    {
        $payments = collect();

        foreach ($invoices->where('status', 'paid') as $invoice) {
            $payment = Payment::create([
                'payment_number' => 'PAY-' . date('Ymd') . '-' . str_pad($payments->count() + 1, 6, '0', STR_PAD_LEFT),
                'invoice_id' => $invoice->id,
                'contract_id' => $invoice->contract_id,
                'customer_id' => $invoice->customer_id,
                'amount' => $invoice->total_ttc,
                'payment_date' => $invoice->paid_at,
                'method' => $invoice->contract->payment_method,
                'status' => 'succeeded',
                'transaction_id' => 'txn_' . uniqid(),
            ]);

            $payments->push($payment);
        }

        return $payments;
    }

    private function createPaymentReminders($invoices)
    {
        $reminders = collect();
        $overdueInvoices = $invoices->where('status', 'overdue');

        foreach ($overdueInvoices as $invoice) {
            $daysOverdue = now()->diffInDays($invoice->due_date);

            if ($daysOverdue >= 30) {
                $phase = 'phase_3';
            } elseif ($daysOverdue >= 15) {
                $phase = 'phase_2';
            } elseif ($daysOverdue >= 7) {
                $phase = 'phase_1';
            } else {
                continue;
            }

            $amountDue = $invoice->total_ttc - $invoice->amount_paid;
            $lateFeePercentage = PaymentReminder::PHASES_CONFIG[$phase]['late_fee_percentage'] ?? 0;
            $lateFee = round($amountDue * ($lateFeePercentage / 100), 2);

            $reminder = PaymentReminder::create([
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'contract_id' => $invoice->contract_id,
                'phase' => $phase,
                'days_overdue' => $daysOverdue,
                'amount_due' => $amountDue,
                'late_fee' => $lateFee,
                'status' => rand(0, 1) ? 'sent' : 'pending',
                'sent_at' => rand(0, 1) ? now()->subDays(rand(1, 5)) : null,
                'sent_via' => json_encode(['email']),
                'message' => "Rappel de paiement {$phase} pour facture {$invoice->invoice_number}",
            ]);

            $reminders->push($reminder);
        }

        return $reminders;
    }

    private function createReservations($customers, $boxes)
    {
        $reservations = collect();
        $availableBoxes = $boxes->where('status', 'available')->take(10);

        foreach ($availableBoxes as $index => $box) {
            $isGuest = $index % 3 === 0; // 1/3 de réservations invités

            $startDate = now()->addDays(rand(7, 30));
            $expiresAt = $startDate->copy()->subDay();

            $durationMonths = [1, 3, 6, 12][rand(0, 3)];

            $reservation = Reservation::create([
                'reservation_number' => 'RES-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'customer_id' => $isGuest ? null : $customers->random()->id,
                'box_id' => $box->id,
                'site_id' => $box->site_id,
                'start_date' => $startDate,
                'duration_months' => $durationMonths,
                'monthly_price_ht' => $box->price_ht,
                'tax_rate' => 20,
                'total_monthly_ttc' => $box->price_ht * 1.20,
                'deposit_amount' => $box->price_ht * 2,
                'first_payment' => ($box->price_ht * 1.20) + ($box->price_ht * 2),
                'status' => ['pending', 'confirmed'][rand(0, 1)],
                'expires_at' => $expiresAt,
                'is_guest' => $isGuest,
                'guest_email' => $isGuest ? fake()->safeEmail() : null,
                'guest_phone' => $isGuest ? fake()->phoneNumber() : null,
                'guest_first_name' => $isGuest ? fake()->firstName() : null,
                'guest_last_name' => $isGuest ? fake()->lastName() : null,
            ]);

            $reservations->push($reservation);
        }

        return $reservations;
    }

    private function createLoyaltyPoints($customers)
    {
        foreach ($customers as $customer) {
            $pointsEarned = rand(100, 5000);
            $pointsSpent = rand(0, min(1000, $pointsEarned));
            $points = $pointsEarned - $pointsSpent;

            $tier = match(true) {
                $points >= 10000 => 'platinum',
                $points >= 5000 => 'gold',
                $points >= 1000 => 'silver',
                default => 'bronze',
            };

            $loyalty = LoyaltyPoint::create([
                'customer_id' => $customer->id,
                'points' => $points,
                'points_earned' => $pointsEarned,
                'points_spent' => $pointsSpent,
                'tier' => $tier,
            ]);

            // Créer quelques transactions
            for ($i = 0; $i < rand(3, 10); $i++) {
                LoyaltyTransaction::create([
                    'loyalty_point_id' => $loyalty->id,
                    'customer_id' => $customer->id,
                    'type' => rand(0, 1) ? 'earned' : 'spent',
                    'points' => rand(10, 100),
                    'description' => 'Transaction de démonstration',
                    'expires_at' => now()->addYear(),
                    'created_at' => now()->subDays(rand(1, 365)),
                ]);
            }
        }
    }

    private function createAdminUser()
    {
        User::create([
            'name' => 'Admin Boxibox',
            'email' => 'admin@boxibox.com',
            'password' => Hash::make('password'),
            'userable_type' => User::class,
            'userable_id' => 1,
        ]);
    }

    private function weightedRandom(array $weights): string
    {
        $total = array_sum($weights);
        $random = rand(1, $total);

        $current = 0;
        foreach ($weights as $value => $weight) {
            $current += $weight;
            if ($random <= $current) {
                return $value;
            }
        }

        return array_key_first($weights);
    }

    private function showCredentials()
    {
        $this->command->table(
            ['Type', 'Email', 'Password', 'Description'],
            [
                ['Admin Web', 'admin@boxibox.com', 'password', 'Accès complet administration'],
                ['Client', 'client1@example.com', 'password', '20 comptes clients (client1@ à client20@)'],
                ['API', 'client1@example.com', 'password', 'Utiliser pour tests API mobile'],
            ]
        );

        $this->command->newLine();
        $this->command->info('💡 Conseil: Tous les comptes utilisent le même mot de passe: password');
    }
}
