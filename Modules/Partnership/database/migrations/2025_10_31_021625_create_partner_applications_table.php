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
    Schema::create('partner_applications', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->uuid('user_id')->unique(); // One application per user
        $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
        $table->text('reason_for_rejection')->nullable();
        $table->uuid('approved_by')->nullable();
        $table->timestamps();
        $table->softDeletes();

        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_applications');
    }
};
