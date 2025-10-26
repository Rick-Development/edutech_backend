<?php

namespace Modules\Courses\Models;

use Modules\Core\Models\CoreModel;

class Course extends CoreModel
{
    protected $fillable = [
        'title',
        'description',
        'price',
        'mentor_id',
        'duration_weeks',
        'status', // 'active', 'draft', 'archived'
        'is_incoming' // for Stock Market & Blockchain
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_incoming' => 'boolean',
        'duration_weeks' => 'integer',
    ];

    // Relationships
    public function mentor()
    {
        return $this->belongsTo(\Modules\Users\Models\User::class, 'mentor_id');
    }

    public function enrollments()
    {
        return $this->hasMany(\Modules\Enrollment\Models\Enrollment::class);
    }
}