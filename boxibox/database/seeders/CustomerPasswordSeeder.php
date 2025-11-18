<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerPasswordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Générer des mots de passe pour tous les clients existants
        $customers = Customer::whereNull('password')->get();

        foreach ($customers as $customer) {
            // Mot de passe par défaut: "password123"
            $customer->update([
                'password' => Hash::make('password123')
            ]);
        }

        $this->command->info('Mots de passe générés pour ' . $customers->count() . ' clients.');
        $this->command->info('Mot de passe par défaut: password123');
    }
}
