<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->uuid('referrer_id')->nullable()->after('referral_code');
        $table->foreign('referrer_id')->references('id')->on('users')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['referrer_id']);
        $table->dropColumn('referrer_id');
    });
}

};
