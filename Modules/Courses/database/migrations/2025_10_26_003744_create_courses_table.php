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
    Schema::create('courses', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->string('title');
        $table->text('description');
        $table->decimal('price', 15, 2)->default(0.00); // 0 = free
        $table->uuid('mentor_id')->nullable();
        $table->integer('duration_weeks')->default(4);
        $table->enum('status', ['active', 'draft', 'archived'])->default('active');
        $table->boolean('is_incoming')->default(false); // e.g., Stock Market
        $table->timestamps();
        $table->softDeletes();

        $table->foreign('mentor_id')->references('id')->on('users')->onDelete('set null');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
