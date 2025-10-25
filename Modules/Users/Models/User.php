<?php

namespace Modules\Users\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Core\Models\CoreModel;

class User extends CoreModel
{
    use HasApiTokens, Notifiable ;

    protected $fillable = [
        'firstname',
        'lastname',
        'name', // optional – can be kept for backward compatibility
        'email',
        'password',
        'phone',
        'role', // 'student', 'mentor', 'partner', 'admin'
        'is_biometric_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_biometric_enabled' => 'boolean',
    ];

    // Roles
    public const ROLE_STUDENT = 'student';
    public const ROLE_MENTOR = 'mentor';
    public const ROLE_PARTNER = 'partner';
    public const ROLE_ADMIN = 'admin';

    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    public function isPartner(): bool
    {
        return $this->role === self::ROLE_PARTNER;
    }

    // Password hashing
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    // Computed full name accessor
    public function getFullNameAttribute(): string
    {
        return trim("{$this->firstname} {$this->lastname}");
    }
}
