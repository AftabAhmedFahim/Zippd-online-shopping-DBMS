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
        Schema::create('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->primary(['order_id', 'product_id']);

            $table->foreign('order_id')->references('order_id')->on('orders')->cascadeOnDelete();
            $table->foreign('product_id')->references('product_id')->on('products')->noActionOnDelete();
        });

        DB::statement('ALTER TABLE order_items ADD CONSTRAINT chk_order_items_quantity_positive CHECK (quantity > 0)');
        DB::statement('ALTER TABLE order_items ADD CONSTRAINT chk_order_items_unit_price_nonnegative CHECK (unit_price >= 0)');
        DB::statement('ALTER TABLE order_items ADD CONSTRAINT chk_order_items_line_total_nonnegative CHECK (line_total >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
