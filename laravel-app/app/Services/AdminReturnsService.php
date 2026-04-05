<?php

namespace App\Services;

use App\Support\MsSqlConsoleDebug;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AdminReturnsService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getReturnsForAdminView(?string $searchQuery = null): array
    {
        $searchTerm = trim((string) $searchQuery);
        $likeValue = '%' . $searchTerm . '%';

        $sql = "SELECT
                    r.return_id,
                    r.return_code,
                    r.order_id,
                    r.product_id,
                    r.user_id,
                    u.full_name AS user_name,
                    u.email AS user_email,
                    p.product_name,
                    ISNULL(oi.quantity, 0) AS quantity,
                    r.return_reason,
                    r.comments,
                    r.refund_to,
                    r.return_date,
                    r.status,
                    r.created_at,
                    r.updated_at
                FROM returns r
                INNER JOIN users u ON u.user_id = r.user_id
                LEFT JOIN order_items oi ON oi.order_id = r.order_id AND oi.product_id = r.product_id
                LEFT JOIN products p ON p.product_id = r.product_id
                WHERE (? = ''
                    OR CAST(r.return_id AS VARCHAR(20)) LIKE ?
                    OR ISNULL(r.return_code, '') LIKE ?
                    OR CAST(r.order_id AS VARCHAR(20)) LIKE ?
                    OR CAST(r.product_id AS VARCHAR(20)) LIKE ?
                    OR CAST(r.user_id AS VARCHAR(20)) LIKE ?
                    OR u.full_name LIKE ?
                    OR u.email LIKE ?
                    OR ISNULL(p.product_name, '') LIKE ?
                    OR r.return_reason LIKE ?
                    OR ISNULL(r.comments, '') LIKE ?
                    OR ISNULL(r.refund_to, '') LIKE ?
                    OR r.status LIKE ?
                    OR CONVERT(VARCHAR(19), r.return_date, 120) LIKE ?
                    OR CONVERT(VARCHAR(19), r.updated_at, 120) LIKE ?)
                ORDER BY r.return_date DESC, r.return_id DESC";

        $bindings = [
            $searchTerm,
            $likeValue,
            $likeValue,
            $likeValue,
            $likeValue,
            $likeValue,
            $likeValue,
            $likeValue,
            $likeValue,
            $likeValue,
            $likeValue,
            $likeValue,
            $likeValue,
            $likeValue,
            $likeValue,
        ];

        $rows = DB::connection('sqlsrv')->select($sql, $bindings);
        $returns = array_map(static fn ($row): array => (array) $row, $rows);
        MsSqlConsoleDebug::push($sql, $bindings, $returns);

        return $returns;
    }

    public function updateReturnForAdmin(int $returnId, string $status): void
    {
        $normalizedStatus = strtolower(trim($status));
        $allowedStatuses = [
            'pending',
            'in progress',
            'approved',
            'returned successfully',
            'rejected',
            'cancelled',
        ];

        if (!in_array($normalizedStatus, $allowedStatuses, true)) {
            throw new RuntimeException('Invalid return status.');
        }

        $sql = 'UPDATE returns
                SET status = ?, updated_at = SYSDATETIME()
                WHERE return_id = ?';
        $bindings = [$normalizedStatus, $returnId];
        $affectedRows = DB::connection('sqlsrv')->update($sql, $bindings);
        MsSqlConsoleDebug::push($sql, $bindings, ['affected_rows' => $affectedRows]);

        if ($affectedRows > 0) {
            return;
        }

        $this->assertReturnExists($returnId);
    }

    private function assertReturnExists(int $returnId): void
    {
        $existsSql = 'SELECT return_id
                      FROM returns
                      WHERE return_id = ?';
        $existsBindings = [$returnId];
        $existingRow = DB::connection('sqlsrv')->selectOne($existsSql, $existsBindings);
        MsSqlConsoleDebug::push($existsSql, $existsBindings, $existingRow ? (array) $existingRow : null);

        if ($existingRow === null) {
            throw new RuntimeException('Return request not found.');
        }
    }
}
