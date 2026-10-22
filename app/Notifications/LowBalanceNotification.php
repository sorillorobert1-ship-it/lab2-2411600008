<?php

namespace App\Notifications;

use App\Models\Account;
use Illuminate\Notifications\Notification;

class LowBalanceNotification extends Notification
{
    public function __construct(public Account $account) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Your account {$this->account->account_number} is below its low-balance threshold.",
            'balance' => (float) $this->account->balance,
            'threshold' => (float) $this->account->low_balance_threshold,
        ];
    }

}
