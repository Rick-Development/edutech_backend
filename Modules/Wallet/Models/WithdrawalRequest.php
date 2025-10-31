<?php

namespace Modules\Wallet\Models;

use Modules\Core\Models\CoreModel;

class WithdrawalRequest extends CoreModel
{
    protected $fillable = [
        'user_id',
        'amount',
        'account_number',
        'account_name',
        'bank_name',
        'reference',
        'status', // 'pending', 'approved', 'rejected'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(\Modules\Users\Models\User::class);
    }
}