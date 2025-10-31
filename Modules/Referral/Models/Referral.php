<?php

namespace Modules\Referral\Models;

use Modules\Core\Models\CoreModel;

class Referral extends CoreModel
{
    protected $fillable = [
        'referrer_id',    // Partner who referred
        'referred_id',    // New student
        'course_id',      // Course enrolled in
        'commission_amount',
        'status',         // 'pending', 'paid'
    ];

    protected $casts = [
        'commission_amount' => 'decimal:2',
    ];

    public function referrer()
    {
        return $this->belongsTo(\Modules\Users\Models\User::class, 'referrer_id');
    }

    public function referred()
    {
        return $this->belongsTo(\Modules\Users\Models\User::class, 'referred_id');
    }

    public function course()
    {
        return $this->belongsTo(\Modules\Courses\Models\Course::class);
    }
}