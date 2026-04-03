<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('returns', function (Blueprint $table) {
            $table->string('return_code', 20)->nullable()->after('return_id');
            $table->text('comments')->nullable()->after('return_reason');
            $table->string('refund_to', 30)->nullable()->after('comments');
        });

        DB::statement("UPDATE returns
                       SET return_code = CONCAT('RET', RIGHT('000000' + CAST(return_id AS VARCHAR(6)), 6))
                       WHERE return_code IS NULL");

        DB::statement("ALTER TABLE returns ALTER COLUMN return_code VARCHAR(20) NOT NULL");
        DB::statement("CREATE UNIQUE INDEX ux_returns_return_code ON returns(return_code)");
        DB::statement("ALTER TABLE returns ADD CONSTRAINT chk_returns_refund_to
                       CHECK (refund_to IS NULL OR refund_to IN ('bkash', 'nagad', 'voucher'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE returns DROP CONSTRAINT chk_returns_refund_to");
        DB::statement("DROP INDEX ux_returns_return_code ON returns");

        Schema::table('returns', function (Blueprint $table) {
            $table->dropColumn(['return_code', 'comments', 'refund_to']);
        });
    }
};
