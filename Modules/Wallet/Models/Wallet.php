<?php

namespace Modules\Wallet\Models;

use Modules\Core\Models\CoreModel;

class Wallet extends CoreModel
{
    protected $fillable = ['user_id', 'balance','pending_balance'];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(\Modules\Users\Models\User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}