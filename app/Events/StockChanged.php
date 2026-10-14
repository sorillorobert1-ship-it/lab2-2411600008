<?php
namespace App\Events;
use Illuminate\Foundation\Events\Dispatchable;
class StockChanged {use Dispatchable;public function __construct(public int $transactionId) {}}
