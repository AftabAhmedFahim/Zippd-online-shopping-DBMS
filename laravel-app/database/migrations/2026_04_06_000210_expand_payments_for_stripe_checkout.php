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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('gateway', 30)->default('cash_on_delivery')->after('payment_method');
            $table->string('stripe_checkout_session_id', 255)->nullable()->after('gateway');
            $table->string('stripe_payment_intent_id', 255)->nullable()->after('stripe_checkout_session_id');
            $table->text('failure_reason')->nullable()->after('stripe_payment_intent_id');
        });

        DB::statement("CREATE UNIQUE INDEX ux_payments_stripe_checkout_session_id
                       ON payments(stripe_checkout_session_id)
                       WHERE stripe_checkout_session_id IS NOT NULL");
        DB::statement("CREATE UNIQUE INDEX ux_payments_stripe_payment_intent_id
                       ON payments(stripe_payment_intent_id)
                       WHERE stripe_payment_intent_id IS NOT NULL");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT chk_payments_gateway
                       CHECK (gateway IN ('cash_on_delivery', 'stripe'))");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT chk_payments_status_values
                       CHECK (payment_status IN ('pending', 'paid', 'failed', 'cancelled'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE payments DROP CONSTRAINT chk_payments_status_values');
        DB::statement('ALTER TABLE payments DROP CONSTRAINT chk_payments_gateway');
        DB::statement('DROP INDEX ux_payments_stripe_payment_intent_id ON payments');
        DB::statement('DROP INDEX ux_payments_stripe_checkout_session_id ON payments');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'gateway',
                'stripe_checkout_session_id',
                'stripe_payment_intent_id',
                'failure_reason',
            ]);
        });
    }
};
