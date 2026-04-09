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
            if (!Schema::hasColumn('returns', 'return_code')) {
                $table->string('return_code', 20)->nullable()->after('return_id');
            }

            if (!Schema::hasColumn('returns', 'comments')) {
                $table->text('comments')->nullable()->after('return_reason');
            }

            if (!Schema::hasColumn('returns', 'refund_to')) {
                $table->string('refund_to', 30)->nullable()->after('comments');
            }
        });

        if (Schema::hasColumn('returns', 'return_code')) {
            DB::statement("UPDATE returns
                           SET return_code = CONCAT('RET', RIGHT('000000' + CAST(return_id AS VARCHAR(6)), 6))
                           WHERE return_code IS NULL");

            DB::statement("ALTER TABLE returns ALTER COLUMN return_code VARCHAR(20) NOT NULL");
        }

        DB::statement("IF NOT EXISTS (
                            SELECT 1
                            FROM sys.indexes
                            WHERE name = 'ux_returns_return_code'
                              AND object_id = OBJECT_ID('returns')
                       )
                       CREATE UNIQUE INDEX ux_returns_return_code ON returns(return_code)");

        DB::statement("IF NOT EXISTS (
                            SELECT 1
                            FROM sys.check_constraints
                            WHERE name = 'chk_returns_refund_to'
                       )
                       ALTER TABLE returns ADD CONSTRAINT chk_returns_refund_to
                       CHECK (refund_to IS NULL OR refund_to IN ('bkash', 'nagad', 'voucher'))");
    }

    public function down(): void
    {
        DB::statement("IF EXISTS (
                            SELECT 1
                            FROM sys.check_constraints
                            WHERE name = 'chk_returns_refund_to'
                       )
                       ALTER TABLE returns DROP CONSTRAINT chk_returns_refund_to");

        DB::statement("IF EXISTS (
                            SELECT 1
                            FROM sys.indexes
                            WHERE name = 'ux_returns_return_code'
                              AND object_id = OBJECT_ID('returns')
                       )
                       DROP INDEX ux_returns_return_code ON returns");

        Schema::table('returns', function (Blueprint $table) {
            if (Schema::hasColumn('returns', 'return_code')) {
                $table->dropColumn('return_code');
            }

            if (Schema::hasColumn('returns', 'comments')) {
                $table->dropColumn('comments');
            }

            if (Schema::hasColumn('returns', 'refund_to')) {
                $table->dropColumn('refund_to');
            }
        });
    }
};
