<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Site;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Box;
use App\Models\Customer;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Str;

class BoxiboxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test tenant (assuming landlord_tenants table exists)
        $tenantId = 1; // Default tenant for testing

        // Create admin user
        $admin = User::create([
            'name' => 'Admin Boxibox',
            'email' => 'admin@boxibox.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // Create a site
        $site = Site::create([
            'tenant_id' => $tenantId,
            'name' => 'Boxibox Paris Nord',
            'slug' => 'paris-nord',
            'description' => 'Centre de self-stockage moderne et sécurisé au nord de Paris',
            'address' => '123 Avenue de la République',
            'city' => 'Paris',
            'postal_code' => '75018',
            'country' => 'FR',
            'latitude' => 48.8915,
            'longitude' => 2.3444,
            'phone' => '+33 1 23 45 67 89',
            'email' => 'paris-nord@boxibox.com',
            'opening_hours' => [
                'monday' => '9:00-18:00',
                'tuesday' => '9:00-18:00',
                'wednesday' => '9:00-18:00',
                'thursday' => '9:00-18:00',
                'friday' => '9:00-18:00',
                'saturday' => '9:00-13:00',
                'sunday' => 'closed',
            ],
            'access_hours' => [
                'type' => '24/7',
                'note' => 'Accès 24h/24 et 7j/7 avec badge'
            ],
            'features' => ['parking', 'elevator', 'security_camera', 'climate_control', 'trolleys'],
            'is_active' => true,
        ]);

        // Create buildings
        $buildingA = Building::create([
            'site_id' => $site->id,
            'name' => 'Bâtiment A',
            'type' => 'interior',
            'construction_year' => 2020,
            'total_surface' => 1500.00,
            'climate_controlled' => true,
            'description' => 'Bâtiment climatisé avec ascenseur',
            'features' => ['elevator', 'climate_control'],
            'display_order' => 1,
        ]);

        $buildingB = Building::create([
            'site_id' => $site->id,
            'name' => 'Bâtiment B',
            'type' => 'exterior',
            'construction_year' => 2021,
            'total_surface' => 800.00,
            'climate_controlled' => false,
            'description' => 'Boxes extérieurs avec accès véhicule',
            'features' => ['vehicle_access', 'ground_level'],
            'display_order' => 2,
        ]);

        // Create floors for Building A
        $floors = [];
        for ($level = 0; $level <= 2; $level++) {
            $floors[] = Floor::create([
                'building_id' => $buildingA->id,
                'level' => $level,
                'name' => $level === 0 ? 'Rez-de-chaussée' : "Étage $level",
                'has_elevator' => true,
                'has_freight_elevator' => $level === 0,
                'corridor_width' => 2.5,
                'display_order' => $level,
            ]);
        }

        // Create floor for Building B (ground level only)
        $floors[] = Floor::create([
            'building_id' => $buildingB->id,
            'level' => 0,
            'name' => 'Rez-de-chaussée',
            'has_elevator' => false,
            'has_freight_elevator' => false,
            'corridor_width' => 3.0,
            'display_order' => 0,
        ]);

        // Create boxes
        $boxSizes = [
            ['category' => 'mini', 'length' => 100, 'width' => 100, 'height' => 200, 'price' => 50],
            ['category' => 'small', 'length' => 150, 'width' => 150, 'height' => 220, 'price' => 80],
            ['category' => 'medium', 'length' => 200, 'width' => 200, 'height' => 230, 'price' => 120],
            ['category' => 'large', 'length' => 300, 'width' => 200, 'height' => 240, 'price' => 180],
            ['category' => 'xl', 'length' => 400, 'width' => 300, 'height' => 250, 'price' => 250],
        ];

        $boxCounter = 1;
        $boxes = [];

        foreach ($floors as $floor) {
            $numBoxes = $floor->building->type === 'interior' ? 10 : 6;

            for ($i = 0; $i < $numBoxes; $i++) {
                $size = $boxSizes[array_rand($boxSizes)];
                $length = $size['length'];
                $width = $size['width'];
                $height = $size['height'];

                $boxes[] = Box::create([
                    'floor_id' => $floor->id,
                    'number' => 'BOX-' . str_pad($boxCounter++, 4, '0', STR_PAD_LEFT),
                    'type' => $floor->building->climate_controlled ? 'climate_controlled' : 'standard',
                    'size_category' => $size['category'],
                    'length' => $length,
                    'width' => $width,
                    'height' => $height,
                    'volume' => ($length * $width * $height) / 1000000, // m³
                    'surface' => ($length * $width) / 10000, // m²
                    'base_price_monthly' => $size['price'],
                    'current_price_monthly' => $size['price'] * (rand(90, 110) / 100), // Variation ±10%
                    'ground_floor' => $floor->level === 0,
                    'vehicle_access' => $floor->building->type === 'exterior',
                    'climate_controlled' => $floor->building->climate_controlled,
                    'has_electricity' => false,
                    'features' => [],
                    'status' => rand(0, 100) < 70 ? 'occupied' : 'available', // 70% occupied
                ]);
            }
        }

        // Create customers
        $customers = [];
        for ($i = 0; $i < 50; $i++) {
            $type = rand(0, 100) < 80 ? 'individual' : 'professional';
            $firstName = fake()->firstName();
            $lastName = fake()->lastName();

            $customers[] = Customer::create([
                'tenant_id' => $tenantId,
                'customer_number' => 'CUST-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'type' => $type,
                'civility' => $type === 'individual' ? (rand(0, 1) ? 'M.' : 'Mme') : null,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'birth_date' => $type === 'individual' ? fake()->dateTimeBetween('-65 years', '-18 years') : null,
                'company_name' => $type === 'professional' ? fake()->company() : null,
                'siret' => $type === 'professional' ? fake()->numerify('###############') : null,
                'email' => strtolower($firstName . '.' . $lastName) . '@example.com',
                'phone' => fake()->phoneNumber(),
                'mobile' => fake()->mobileNumber(),
                'address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'postal_code' => fake()->postcode(),
                'country' => 'FR',
                'language' => 'fr',
                'documents_verified' => rand(0, 100) < 80 ? 'verified' : 'pending',
                'payment_score' => ['good', 'average', 'new'][rand(0, 2)],
                'acquisition_source' => ['web', 'phone', 'visit', 'referral'][rand(0, 3)],
            ]);
        }

        // Create contracts for occupied boxes
        $occupiedBoxes = array_filter($boxes, fn($box) => $box->status === 'occupied');

        foreach ($occupiedBoxes as $index => $box) {
            if ($index >= count($customers)) break;

            $customer = $customers[$index];
            $startDate = fake()->dateTimeBetween('-2 years', '-1 month');
            $monthlyPrice = $box->current_price_monthly;

            $contract = Contract::create([
                'contract_number' => 'CT-' . date('Y') . '-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'box_id' => $box->id,
                'site_id' => $site->id,
                'start_date' => $startDate,
                'end_date' => null,
                'initial_duration_months' => null,
                'auto_renewal' => true,
                'notice_period_days' => 30,
                'monthly_price' => $monthlyPrice,
                'deposit_amount' => $monthlyPrice,
                'setup_fee' => 0,
                'billing_frequency' => 'monthly',
                'billing_day' => 1,
                'payment_method' => ['card', 'sepa', 'transfer'][rand(0, 2)],
                'has_insurance' => rand(0, 100) < 60,
                'insurance_amount_monthly' => rand(0, 100) < 60 ? 15.00 : 0,
                'declared_value' => rand(1000, 10000),
                'status' => 'active',
                'signed_at' => $startDate,
                'access_code' => str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
            ]);

            // Create invoices for the last 6 months
            $invoiceStartDate = max(new \DateTime($startDate->format('Y-m-d')), new \DateTime('-6 months'));
            $currentDate = new \DateTime();
            $invoiceDate = clone $invoiceStartDate;
            $invoiceCounter = 1;

            while ($invoiceDate <= $currentDate) {
                $issueDate = clone $invoiceDate;
                $dueDate = (clone $invoiceDate)->modify('+30 days');
                $periodStart = clone $invoiceDate;
                $periodEnd = (clone $invoiceDate)->modify('+1 month')->modify('-1 day');

                $subtotalHT = $monthlyPrice / 1.20; // Assuming 20% VAT
                $taxAmount = $monthlyPrice - $subtotalHT;

                $invoice = Invoice::create([
                    'invoice_number' => 'INV-' . $invoiceDate->format('Y') . '-' . str_pad($invoiceCounter++, 6, '0', STR_PAD_LEFT),
                    'contract_id' => $contract->id,
                    'customer_id' => $customer->id,
                    'site_id' => $site->id,
                    'type' => 'rental',
                    'issue_date' => $issueDate,
                    'due_date' => $dueDate,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'subtotal_ht' => $subtotalHT,
                    'tax_amount' => $taxAmount,
                    'tax_rate' => 20.00,
                    'total_ttc' => $monthlyPrice,
                    'discount_amount' => 0,
                    'line_items' => [
                        [
                            'description' => "Location Box {$box->number} - " . $periodStart->format('d/m/Y') . ' au ' . $periodEnd->format('d/m/Y'),
                            'quantity' => 1,
                            'unit_price' => $subtotalHT,
                            'total' => $subtotalHT,
                            'tax_rate' => 20.00,
                        ]
                    ],
                    'status' => $invoiceDate < (new \DateTime('-30 days')) ? 'paid' : 'pending',
                    'amount_paid' => $invoiceDate < (new \DateTime('-30 days')) ? $monthlyPrice : 0,
                    'paid_at' => $invoiceDate < (new \DateTime('-30 days')) ? $dueDate : null,
                ]);

                // Create payment for paid invoices
                if ($invoice->status === 'paid') {
                    Payment::create([
                        'payment_number' => 'PAY-' . $invoiceDate->format('Y') . '-' . str_pad($invoiceCounter, 6, '0', STR_PAD_LEFT),
                        'invoice_id' => $invoice->id,
                        'contract_id' => $contract->id,
                        'customer_id' => $customer->id,
                        'amount' => $monthlyPrice,
                        'currency' => 'EUR',
                        'method' => $contract->payment_method,
                        'payment_date' => $dueDate,
                        'status' => 'succeeded',
                    ]);
                }

                $invoiceDate->modify('+1 month');
            }
        }

        $this->command->info('✅ Boxibox test data created successfully!');
        $this->command->info('📊 Created:');
        $this->command->info("   - 1 Site: {$site->name}");
        $this->command->info("   - 2 Buildings");
        $this->command->info("   - " . count($floors) . " Floors");
        $this->command->info("   - " . count($boxes) . " Boxes");
        $this->command->info("   - " . count($customers) . " Customers");
        $this->command->info("   - " . count($occupiedBoxes) . " Active Contracts");
        $this->command->info("👤 Admin user: admin@boxibox.com / password");
    }
}
