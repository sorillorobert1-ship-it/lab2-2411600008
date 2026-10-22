<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'account_number', 'balance', 'low_balance_threshold'];

    protected function casts(): array
    {
        return ['balance' => 'decimal:2', 'low_balance_threshold' => 'decimal:2'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }
}
