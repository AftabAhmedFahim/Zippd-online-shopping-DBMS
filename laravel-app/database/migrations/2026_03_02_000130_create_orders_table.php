<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->noActionOnDelete();
            $table->dateTime('order_date')->useCurrent();
            $table->string('order_status', 30)->default('pending');
            $table->text('shipping_address');
            $table->decimal('total_amount', 12, 2);
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE orders ADD CONSTRAINT chk_orders_total_amount_nonnegative CHECK (total_amount >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
