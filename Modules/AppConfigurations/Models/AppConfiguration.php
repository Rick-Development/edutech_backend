<?php

namespace Modules\AppConfigurations\Models;

use Illuminate\Database\Eloquent\Model;

class AppConfiguration extends Model
{
    protected $fillable = [
        'app_name',
        'logo_path',
        'favicon_path',
        'support_email',
        'support_phone',
        'about',
        'maintenance_mode',
        'user_registration',
        'two_factor_enabled',
        'email_notifications_enabled',
        'additional_settings',
    ];

    protected $casts = [
        'maintenance_mode' => 'boolean',
        'user_registration' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'email_notifications_enabled' => 'boolean',
        'additional_settings' => 'array',
    ];
}
