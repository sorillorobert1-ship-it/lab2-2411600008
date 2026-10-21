<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\BankTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankingSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login(): void
    {
        $user = User::factory()->create(['password' => 'secret-password']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_deposit_updates_balance_and_audit_record(): void
    {
        [$user, $account] = $this->accountWithBalance(1000);

        $this->actingAs($user)->post('/transactions', [
            'account_id' => $account->id,
            'type' => 'deposit',
            'amount' => 250,
        ])->assertRedirect();

        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'balance' => '1250.00']);
        $this->assertDatabaseHas('bank_transactions', [
            'account_id' => $account->id,
            'type' => 'deposit',
            'amount' => '250.00',
            'balance_after' => '1250.00',
        ]);
    }

    public function test_withdrawal_updates_balance(): void
    {
        [$user, $account] = $this->accountWithBalance(1000);

        $this->actingAs($user)->post('/transactions', [
            'account_id' => $account->id,
            'type' => 'withdrawal',
            'amount' => 300,
        ])->assertRedirect();

        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'balance' => '700.00']);
    }

    public function test_transfer_updates_both_accounts_atomically(): void
    {
        [$sender, $source] = $this->accountWithBalance(1000);
        [, $destination] = $this->accountWithBalance(200);

        $this->actingAs($sender)->post('/transactions', [
            'account_id' => $source->id,
            'type' => 'transfer',
            'to_account_id' => $destination->id,
            'amount' => 400,
        ])->assertRedirect();

        $this->assertDatabaseHas('accounts', ['id' => $source->id, 'balance' => '600.00']);
        $this->assertDatabaseHas('accounts', ['id' => $destination->id, 'balance' => '600.00']);
        $this->assertSame(2, BankTransaction::where('reference', 'like', 'TXN-%')->count());
    }

    public function test_low_balance_creates_a_database_notification(): void
    {
        [$user, $account] = $this->accountWithBalance(600);

        $this->actingAs($user)->post('/transactions', [
            'account_id' => $account->id,
            'type' => 'withdrawal',
            'amount' => 150,
        ])->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'App\\Notifications\\LowBalanceNotification',
        ]);
    }

    private function accountWithBalance(float $balance): array
    {
        $user = User::factory()->create();
        $account = Account::create([
            'user_id' => $user->id,
            'account_number' => 'TEST-' . fake()->unique()->numerify('######'),
            'balance' => $balance,
            'low_balance_threshold' => 500,
        ]);

        return [$user, $account];
    }
}
