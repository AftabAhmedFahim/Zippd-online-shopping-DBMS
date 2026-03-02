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
        Schema::create('returns', function (Blueprint $table) {
            $table->id('return_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->noActionOnDelete();
            $table->text('return_reason');
            $table->dateTime('return_date')->useCurrent();
            $table->string('status', 30)->default('pending');
            $table->timestamps();

            $table->unique(['order_id', 'product_id']);
            $table->foreign(['order_id', 'product_id'])
                ->references(['order_id', 'product_id'])
                ->on('order_items')
                ->noActionOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
