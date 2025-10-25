<?php

namespace Modules\Auth\Models;

use Modules\Core\Models\CoreModel;

class BiometricToken extends CoreModel
{
    protected $fillable = ['user_id', 'token', 'device_name'];

    public function user()
    {
        return $this->belongsTo(\Modules\Users\Models\User::class);
    }

    // Generate a secure token for biometric auth
    public static function generateForUser($userId, $deviceName = 'mobile')
    {
        return self::create([
            'user_id' => $userId,
            'token' => hash('sha256', random_bytes(32)),
            'device_name' => $deviceName,
        ]);
    }

    // delete tokens when user logs out or disables biometric auth
    public static function revokeForUser($userId)
    {
        self::where('user_id', $userId)->delete();
    }
}