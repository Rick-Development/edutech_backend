<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('partner_applications', function (Blueprint $table) {
            // Add partnership_code column
            $table->string('partnership_code')->unique()->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('partner_applications', function (Blueprint $table) {
            $table->dropColumn('partnership_code');
        });
    }
};