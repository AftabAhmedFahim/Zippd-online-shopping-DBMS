<?php

namespace App\Services;

use App\Support\ProductImagePath;
use App\Support\MsSqlConsoleDebug;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReturnService
{
    /**
     * @return array<int,string>
     */
    public function getReturnReasons(): array
    {
        return [
            'Wrong item received',
            'Damaged product received',
            'Fake or counterfeit product',
            'Wrong color received',
            'Product arrived with missing parts',
        ];
    }

    /**
     * @return array<int,array{value:string,label:string,icon_path:string}>
     */
    public function getRefundDestinations(): array
    {
        return [
            ['value' => 'bkash', 'label' => 'bKash', 'icon_path' => 'images/refunds/bkash.svg'],
            ['value' => 'nagad', 'label' => 'Nagad', 'icon_path' => 'images/refunds/nagad.svg'],
            ['value' => 'voucher', 'label' => 'Voucher', 'icon_path' => 'images/refunds/voucher.svg'],
        ];
    }

    /**
     * @return array<string,array{value:string,label:string,icon_path:string}>
     */
    public function getRefundDestinationsByValue(): array
    {
        $destinations = [];

        foreach ($this->getRefundDestinations() as $destination) {
            $destinations[$destination['value']] = $destination;
        }

        return $destinations;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getReturnableItem(int $userId, int $orderId, int $productId): ?array
    {
        $sql = "SELECT
                    o.order_id,
                    o.order_status,
                    oi.product_id,
                    oi.quantity,
                    p.product_name,
                    r.return_id,
                    r.return_code,
                    r.status AS return_status
                FROM orders o
                INNER JOIN order_items oi ON oi.order_id = o.order_id
                INNER JOIN products p ON p.product_id = oi.product_id
                LEFT JOIN returns r ON r.order_id = oi.order_id AND r.product_id = oi.product_id
                WHERE o.user_id = ?
                  AND o.order_id = ?
                  AND oi.product_id = ?";

        $bindings = [$userId, $orderId, $productId];
        $row = DB::connection('sqlsrv')->selectOne($sql, $bindings);
        MsSqlConsoleDebug::push($sql, $bindings, $row ? (array) $row : null);

        if ($row === null) {
            return null;
        }

        $item = (array) $row;
        $orderStatus = strtolower((string) ($item['order_status'] ?? ''));

        $item['product_id'] = (int) $item['product_id'];
        $item['order_id'] = (int) $item['order_id'];
        $item['quantity'] = (int) $item['quantity'];
        $item['image_path'] = ProductImagePath::resolve((string) $item['product_name']);
        $item['can_return'] = $orderStatus === 'delivered' && empty($item['return_code']);

        return $item;
    }

    /**
     * @return array{return_id:int,return_code:string}
     */
    public function submitReturn(
        int $userId,
        int $orderId,
        int $productId,
        string $returnReason,
        string $comments,
        string $refundTo
    ): array {
        $item = $this->getReturnableItem($userId, $orderId, $productId);

        if ($item === null) {
            throw new RuntimeException('We could not find that delivered order item.');
        }

        if (($item['can_return'] ?? false) !== true) {
            throw new RuntimeException('This item is not eligible for a new return request.');
        }

        $connection = DB::connection('sqlsrv');
        $connection->beginTransaction();

        try {
            MsSqlConsoleDebug::push('BEGIN TRANSACTION (PDO)', [], ['executed' => true]);

            $insertSql = 'INSERT INTO returns (order_id, product_id, user_id, return_reason, comments, refund_to, return_date, status, created_at, updated_at)
                 OUTPUT INSERTED.return_id AS return_id
                 VALUES (?, ?, ?, ?, ?, ?, SYSDATETIME(), ?, SYSDATETIME(), SYSDATETIME())';
            $insertBindings = [$orderId, $productId, $userId, $returnReason, $comments, $refundTo, 'pending'];
            $returnRow = $connection->selectOne(
                $insertSql,
                $insertBindings
            );
            MsSqlConsoleDebug::push($insertSql, $insertBindings, $returnRow ? (array) $returnRow : null);

            if ($returnRow === null) {
                throw new RuntimeException('Unable to create the return request right now.');
            }

            $returnId = (int) $returnRow->return_id;
            $returnCode = sprintf('RET%06d', $returnId);

            $updateSql = 'UPDATE returns
                 SET return_code = ?, updated_at = SYSDATETIME()
                 WHERE return_id = ?';
            $updateBindings = [$returnCode, $returnId];
            $affectedRows = $connection->update($updateSql, $updateBindings);
            MsSqlConsoleDebug::push($updateSql, $updateBindings, ['affected_rows' => $affectedRows]);

            $connection->commit();
            MsSqlConsoleDebug::push('COMMIT TRANSACTION (PDO)', [], ['executed' => true]);

            return [
                'return_id' => $returnId,
                'return_code' => $returnCode,
            ];
        } catch (\Throwable $exception) {
            $connection->rollBack();

            MsSqlConsoleDebug::push('ROLLBACK TRANSACTION (PDO)', [], ['executed' => true]);
            throw $exception;
        }
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getReturnDetails(int $userId, int $returnId): ?array
    {
        $sql = "SELECT
                    r.return_id,
                    r.return_code,
                    r.order_id,
                    r.product_id,
                    r.return_reason,
                    r.comments,
                    r.refund_to,
                    r.return_date,
                    r.status,
                    oi.quantity,
                    p.product_name
                FROM returns r
                INNER JOIN order_items oi ON oi.order_id = r.order_id AND oi.product_id = r.product_id
                INNER JOIN products p ON p.product_id = r.product_id
                WHERE r.user_id = ?
                  AND r.return_id = ?";

        $bindings = [$userId, $returnId];
        $row = DB::connection('sqlsrv')->selectOne($sql, $bindings);
        MsSqlConsoleDebug::push($sql, $bindings, $row ? (array) $row : null);

        if ($row === null) {
            return null;
        }

        $details = (array) $row;
        $details['return_id'] = (int) $details['return_id'];
        $details['order_id'] = (int) $details['order_id'];
        $details['product_id'] = (int) $details['product_id'];
        $details['quantity'] = (int) $details['quantity'];
        $details['image_path'] = ProductImagePath::resolve((string) $details['product_name']);

        $refundDestination = $this->getRefundDestinationsByValue()[(string) ($details['refund_to'] ?? '')] ?? null;
        $details['refund_destination'] = $refundDestination;
        $details['can_cancel'] = true;

        return $details;
    }

    public function cancelReturn(int $userId, int $returnId): bool
    {
        $existing = $this->getReturnDetails($userId, $returnId);
        if ($existing === null) {
            return false;
        }

        $connection = DB::connection('sqlsrv');
        $connection->beginTransaction();

        try {
            MsSqlConsoleDebug::push('BEGIN TRANSACTION (PDO)', [], ['executed' => true]);

            $deleteSql = 'DELETE FROM returns WHERE return_id = ? AND user_id = ?';
            $deleteBindings = [$returnId, $userId];
            $deleted = $connection->delete($deleteSql, $deleteBindings);
            MsSqlConsoleDebug::push($deleteSql, $deleteBindings, ['affected_rows' => $deleted]);

            $connection->commit();
            MsSqlConsoleDebug::push('COMMIT TRANSACTION (PDO)', [], ['executed' => true]);

            return $deleted === 1;
        } catch (\Throwable $exception) {
            $connection->rollBack();

            MsSqlConsoleDebug::push('ROLLBACK TRANSACTION (PDO)', [], ['executed' => true]);
            throw $exception;
        }
    }
}
