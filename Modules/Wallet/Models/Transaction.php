<?php

namespace Modules\Wallet\Models;

use Modules\Core\Models\CoreModel;

class Transaction extends CoreModel
{
    protected $fillable = [
        'wallet_id',
        'type', // 'deposit', 'withdrawal', 'commission'
        'amount',
        'status', // 'pending', 'completed', 'failed'
        'reference',
        'description',
        'meta' // JSON for bank details, etc.
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}