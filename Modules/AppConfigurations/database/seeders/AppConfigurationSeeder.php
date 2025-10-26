<?php

namespace Modules\AppConfigurations\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AppConfigurations\Models\AppConfiguration;

class AppConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        AppConfiguration::truncate();

        AppConfiguration::create([
            'app_name' => 'Wave Creast Trading Institute',
            'logo_path' => 'https://billway.ng/wave-removebg-preview.png',
            'favicon_path' => 'https://billway.ng/wave.jpeg',
            'support_email' => 'support@wavecreastinstitute.com',
            'support_phone' => '+2348100000000',
            'about' => 'Wave Creast Trading Institute is a forward-thinking educational technology platform committed to empowering individuals with real-world trading and investment skills through innovation, mentorship, and digital transformation.',
            'maintenance_mode' => false,
            'user_registration' => true,
            'two_factor_enabled' => false,
            'email_notifications_enabled' => true,
            'additional_settings' => [
                'theme' => 'default',
                'timezone' => 'Africa/Lagos',
                'currency' => 'NGN',
                'language' => 'en',
                'version' => '1.0.0',
            ],
        ]);
    }
}
