<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->boolean('email_notifications')->default(true)->after('email_verified_at');
        $table->boolean('two_factor_auth')->default(false)->after('email_notifications');
        $table->boolean('delete_account')->default(false)->after('two_factor_auth');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['email_notifications', 'two_factor_auth', 'delete_account']);
    });
}

};
