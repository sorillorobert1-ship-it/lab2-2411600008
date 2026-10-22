<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Account;
use App\Models\BankTransaction;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $alex = User::factory()->create(['name' => 'Robert Sorillo', 'email' => 'robert@example.com']);
        $maria = User::factory()->create(['name' => 'Charmelle Santos', 'email' => 'charmelle@example.com']);

        $alexAccount = Account::create([
            'user_id' => $alex->id, 'account_number' => 'SB-100001',
            'low_balance_threshold' => 500,
        ]);
        $mariaAccount = Account::create([
            'user_id' => $maria->id, 'account_number' => 'SB-100002',
            'low_balance_threshold' => 500,
        ]);

        BankTransaction::create([
            'account_id' => $alexAccount->id, 'type' => 'deposit', 'amount' => 5000,
            'balance_after' => 0, 'description' => 'Opening deposit', 'reference' => 'OPEN-ALEX',
        ]);
        BankTransaction::create([
            'account_id' => $mariaAccount->id, 'type' => 'deposit', 'amount' => 2500,
            'balance_after' => 0, 'description' => 'Opening deposit', 'reference' => 'OPEN-MARIA',
        ]);
    }
}
