<?php

namespace Modules\Partnership\Models;

use Modules\Core\Models\CoreModel;

class PartnerApplication extends CoreModel
{
    protected $fillable = [
        'user_id',
        'status', // 'pending', 'approved', 'rejected'
        'reason_for_rejection',
        'approved_by', // admin user ID
    ];

    protected $casts = [
        'approved_by' => 'uuid',
    ];

    public function user()
    {
        return $this->belongsTo(\Modules\Users\Models\User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(\Modules\Users\Models\User::class, 'approved_by');
    }
}