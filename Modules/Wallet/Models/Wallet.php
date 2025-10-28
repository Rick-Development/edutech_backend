<?php

namespace Modules\Wallet\Models;

use Modules\Core\Models\CoreModel;

class Wallet extends CoreModel
{
    protected $fillable = ['user_id', 'balance'];

    protected $casts = [
        'balance' => 'decimal:2',
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