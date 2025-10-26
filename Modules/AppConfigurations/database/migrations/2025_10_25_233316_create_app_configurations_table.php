<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('app_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('app_name')->default('My Platform');
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('support_email')->nullable();
            $table->string('support_phone')->nullable();
            $table->text('about')->nullable();
            $table->boolean('maintenance_mode')->default(false);
            $table->boolean('user_registration')->default(true);
            $table->boolean('two_factor_enabled')->default(false);
            $table->boolean('email_notifications_enabled')->default(true);
            $table->json('additional_settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_configurations');
    }
};
