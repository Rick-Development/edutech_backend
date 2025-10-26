<?php

namespace Modules\Enrollment\Models;

use Modules\Core\Models\CoreModel;

class Enrollment extends CoreModel
{
    protected $fillable = [
        'user_id',
        'course_id',
        'matric_number',
        'payment_status', // 'pending', 'completed', 'failed'
        'payment_reference',
        'status' // 'active', 'completed'
    ];

    protected $casts = [
        'payment_status' => 'string',
        'status' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(\Modules\Users\Models\User::class);
    }

    public function course()
    {
        return $this->belongsTo(\Modules\Courses\Models\Course::class);
    }
}