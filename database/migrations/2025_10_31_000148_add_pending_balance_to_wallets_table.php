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
    Schema::table('wallets', function (Blueprint $table) {
        $table->decimal('pending_balance', 15, 2)->default(0.00)->after('balance');
    });
}

public function down()
{
    Schema::table('wallets', function (Blueprint $table) {
        $table->dropColumn('pending_balance');
    });
}
};
