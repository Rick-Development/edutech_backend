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
       Schema::create('withdrawal_requests', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('user_id');
    $table->decimal('amount', 15, 2);
    $table->string('account_number');
    $table->string('account_name');
    $table->string('bank_name');
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->timestamps();
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
