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
       Schema::create('transactions', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('wallet_id');
    $table->enum('type', ['deposit', 'withdrawal', 'commission']);
    $table->decimal('amount', 15, 2);
    $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
    $table->string('reference')->nullable();
    $table->text('description')->nullable();
    $table->json('meta')->nullable();
    $table->timestamps();
    $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
