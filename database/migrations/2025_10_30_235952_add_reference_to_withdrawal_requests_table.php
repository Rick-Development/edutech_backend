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
    Schema::table('withdrawal_requests', function (Blueprint $table) {
        $table->string('reference')->nullable()->after('status');
    });
}

public function down()
{
    Schema::table('withdrawal_requests', function (Blueprint $table) {
        $table->dropColumn('reference');
    });
}
};
