<?php

namespace App\Services;

use App\Support\MsSqlConsoleDebug;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use UnexpectedValueException;

class PaymentService
{
    /**
     * @param array{
     *     order_id:int,
     *     total_amount:float|int|string,
     *     items:array<int,array<string,mixed>>
     * } $order
     */
    public function createStripeCheckoutSession(array $order, int $userId, ?string $customerEmail): string
    {
        $this->assertStripeSecretConfigured();

        $orderId = (int) ($order['order_id'] ?? 0);
        if ($orderId <= 0) {
            throw new RuntimeException('Unable to initialize online payment: invalid order.');
        }

        $orderAmount = round((float) ($order['total_amount'] ?? 0), 2);
        if ($orderAmount <= 0) {
            throw new RuntimeException('Unable to initialize online payment: invalid order amount.');
        }

        $items = $order['items'] ?? [];
        if (!is_array($items) || $items === []) {
            throw new RuntimeException('Unable to initialize online payment: no order items found.');
        }

        $this->upsertPaymentRecord(
            $orderId,
            $orderAmount,
            'online',
            'stripe',
            'pending'
        );

        $currency = strtolower((string) config('services.stripe.currency', 'bdt'));
        $lineItems = [];

        foreach ($items as $item) {
            $productName = trim((string) ($item['product_name'] ?? 'Product'));
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $unitAmount = (int) round(((float) ($item['unit_price'] ?? 0)) * 100);

            if ($unitAmount <= 0) {
                throw new RuntimeException(sprintf(
                    'Unable to initialize online payment: invalid price for %s.',
                    $productName === '' ? 'an item' : $productName
                ));
            }

            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount' => $unitAmount,
                    'product_data' => [
                        'name' => $productName === '' ? 'Product' : $productName,
                    ],
                ],
                'quantity' => $quantity,
            ];
        }

        Stripe::setApiKey((string) config('services.stripe.secret'));

        $session = StripeCheckoutSession::create([
            'mode' => 'payment',
            'line_items' => $lineItems,
            'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel', ['order_id' => $orderId]),
            'customer_email' => $customerEmail ?: null,
            'metadata' => [
                'order_id' => (string) $orderId,
                'user_id' => (string) $userId,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'order_id' => (string) $orderId,
                    'user_id' => (string) $userId,
                ],
            ],
        ]);

        $sessionId = (string) ($session->id ?? '');
        if ($sessionId === '') {
            throw new RuntimeException('Unable to initialize online payment session.');
        }

        $paymentIntentId = $this->extractStripeResourceId($session->payment_intent ?? null);

        $this->updatePaymentStripeReferences($orderId, $sessionId, $paymentIntentId);

        $checkoutUrl = (string) ($session->url ?? '');
        if ($checkoutUrl === '') {
            throw new RuntimeException('Stripe did not return a checkout URL.');
        }

        return $checkoutUrl;
    }

    public function recordCashOnDelivery(int $orderId, float $amount): void
    {
        $this->upsertPaymentRecord(
            $orderId,
            round($amount, 2),
            'cash_on_delivery',
            'cash_on_delivery',
            'pending'
        );
    }

    /**
     * @return array{order_id:int,confirmed:bool}
     */
    public function confirmStripePaymentFromSuccessCallback(string $sessionId, int $expectedUserId): array
    {
        $this->assertStripeSecretConfigured();
        Stripe::setApiKey((string) config('services.stripe.secret'));

        $session = StripeCheckoutSession::retrieve($sessionId, [
            'expand' => ['payment_intent'],
        ]);

        $orderId = $this->resolveOrderIdFromCheckoutSession($session);
        if ($orderId === null) {
            throw new RuntimeException('Unable to locate the order for this payment session.');
        }

        $metadataUserId = (int) (($session->metadata->user_id ?? 0));
        if ($metadataUserId > 0 && $metadataUserId !== $expectedUserId) {
            throw new RuntimeException('This payment session does not belong to your account.');
        }

        $paymentStatus = (string) ($session->payment_status ?? '');
        if ($paymentStatus !== 'paid') {
            return [
                'order_id' => $orderId,
                'confirmed' => false,
            ];
        }

        $paymentIntentId = $this->extractStripeResourceId($session->payment_intent ?? null);
        $paidAmount = round(((int) ($session->amount_total ?? 0)) / 100, 2);

        $this->applyStripePaymentSuccess(
            $orderId,
            (string) ($session->id ?? null),
            $paymentIntentId,
            $paidAmount > 0 ? $paidAmount : null
        );

        return [
            'order_id' => $orderId,
            'confirmed' => true,
        ];
    }

    public function markStripeCheckoutCancelled(int $orderId, int $expectedUserId): void
    {
        $orderOwnerSql = 'SELECT user_id FROM orders WHERE order_id = ?';
        $orderOwnerBindings = [$orderId];
        $row = DB::connection('sqlsrv')->selectOne($orderOwnerSql, $orderOwnerBindings);
        MsSqlConsoleDebug::push($orderOwnerSql, $orderOwnerBindings, $row ? (array) $row : null);

        if ($row === null) {
            throw new RuntimeException('Order not found.');
        }

        if ((int) $row->user_id !== $expectedUserId) {
            throw new RuntimeException('You are not authorized to update this payment.');
        }

        $sql = "UPDATE payments
                SET payment_status = CASE WHEN payment_status = 'pending' THEN 'cancelled' ELSE payment_status END,
                    failure_reason = CASE WHEN payment_status = 'pending' THEN ? ELSE failure_reason END,
                    updated_at = SYSDATETIME()
                WHERE order_id = ? AND gateway = 'stripe'";
        $bindings = ['Customer cancelled online payment.', $orderId];
        $affectedRows = DB::connection('sqlsrv')->update($sql, $bindings);
        MsSqlConsoleDebug::push($sql, $bindings, ['affected_rows' => $affectedRows]);
    }

    public function handleStripeWebhook(string $payload, string $signatureHeader): void
    {
        $this->assertStripeSecretConfigured();
        $this->assertStripeWebhookConfigured();

        $secret = (string) config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $signatureHeader, $secret);
        } catch (UnexpectedValueException|SignatureVerificationException $exception) {
            throw new RuntimeException('Invalid Stripe webhook signature.');
        }

        $eventType = (string) ($event->type ?? '');

        if ($eventType === 'checkout.session.completed') {
            $session = $event->data->object;
            $paymentStatus = (string) ($session->payment_status ?? '');
            if ($paymentStatus !== 'paid') {
                return;
            }

            $orderId = $this->resolveOrderIdFromCheckoutSession($session);
            if ($orderId === null) {
                return;
            }

            $paymentIntentId = $this->extractStripeResourceId($session->payment_intent ?? null);
            $paidAmount = round(((int) ($session->amount_total ?? 0)) / 100, 2);

            $this->applyStripePaymentSuccess(
                $orderId,
                (string) ($session->id ?? null),
                $paymentIntentId,
                $paidAmount > 0 ? $paidAmount : null
            );

            return;
        }

        if ($eventType === 'checkout.session.expired') {
            $session = $event->data->object;
            $sessionId = (string) ($session->id ?? '');
            if ($sessionId === '') {
                return;
            }

            $this->markStripeSessionAsCancelled($sessionId, 'Stripe checkout session expired.');
            return;
        }

        if ($eventType === 'payment_intent.payment_failed') {
            $paymentIntent = $event->data->object;
            $paymentIntentId = $this->extractStripeResourceId($paymentIntent->id ?? null);
            if ($paymentIntentId === null) {
                return;
            }

            $failureReason = (string) ($paymentIntent->last_payment_error->message ?? 'Online payment failed.');
            $this->markStripePaymentIntentAsFailed($paymentIntentId, $failureReason);
        }
    }

    public function findPaidOrderIdForUserByStripeSessionId(string $sessionId, int $userId): ?int
    {
        $normalizedSessionId = trim($sessionId);
        if ($normalizedSessionId === '' || $userId <= 0) {
            return null;
        }

        $sql = "SELECT TOP 1 o.order_id
                FROM payments p
                INNER JOIN orders o ON o.order_id = p.order_id
                WHERE p.stripe_checkout_session_id = ?
                  AND o.user_id = ?
                  AND p.payment_status = 'paid'
                ORDER BY o.order_id DESC";
        $bindings = [$normalizedSessionId, $userId];
        $row = DB::connection('sqlsrv')->selectOne($sql, $bindings);
        MsSqlConsoleDebug::push($sql, $bindings, $row ? (array) $row : null);

        if ($row === null) {
            return null;
        }

        return (int) $row->order_id;
    }

    private function assertStripeSecretConfigured(): void
    {
        $secret = trim((string) config('services.stripe.secret'));
        if ($secret === '') {
            throw new RuntimeException('Stripe secret key is missing. Set STRIPE_SECRET in your environment.');
        }
    }

    private function assertStripeWebhookConfigured(): void
    {
        $webhookSecret = trim((string) config('services.stripe.webhook_secret'));
        if ($webhookSecret === '') {
            throw new RuntimeException('Stripe webhook secret is missing. Set STRIPE_WEBHOOK_SECRET in your environment.');
        }
    }

    private function upsertPaymentRecord(
        int $orderId,
        float $amount,
        string $paymentMethod,
        string $gateway,
        string $paymentStatus
    ): void {
        $existingSql = 'SELECT payment_id FROM payments WHERE order_id = ?';
        $existingBindings = [$orderId];
        $existingRow = DB::connection('sqlsrv')->selectOne($existingSql, $existingBindings);
        MsSqlConsoleDebug::push($existingSql, $existingBindings, $existingRow ? (array) $existingRow : null);

        if ($existingRow !== null) {
            $updateSql = "UPDATE payments
                          SET amount = ?, payment_method = ?, gateway = ?, payment_status = ?, payment_date = NULL,
                              failure_reason = NULL, updated_at = SYSDATETIME()
                          WHERE order_id = ?";
            $updateBindings = [$amount, $paymentMethod, $gateway, $paymentStatus, $orderId];
            $affectedRows = DB::connection('sqlsrv')->update($updateSql, $updateBindings);
            MsSqlConsoleDebug::push($updateSql, $updateBindings, ['affected_rows' => $affectedRows]);
            return;
        }

        $insertSql = 'INSERT INTO payments
                      (order_id, amount, payment_date, payment_method, gateway, payment_status, failure_reason, created_at, updated_at)
                      VALUES (?, ?, NULL, ?, ?, ?, NULL, SYSDATETIME(), SYSDATETIME())';
        $insertBindings = [$orderId, $amount, $paymentMethod, $gateway, $paymentStatus];
        $inserted = DB::connection('sqlsrv')->insert($insertSql, $insertBindings);
        MsSqlConsoleDebug::push($insertSql, $insertBindings, ['inserted' => $inserted]);
    }

    private function updatePaymentStripeReferences(int $orderId, string $sessionId, ?string $paymentIntentId): void
    {
        $sql = 'UPDATE payments
                SET stripe_checkout_session_id = ?, stripe_payment_intent_id = ?, updated_at = SYSDATETIME()
                WHERE order_id = ?';
        $bindings = [$sessionId, $paymentIntentId, $orderId];
        $affectedRows = DB::connection('sqlsrv')->update($sql, $bindings);
        MsSqlConsoleDebug::push($sql, $bindings, ['affected_rows' => $affectedRows]);
    }

    private function applyStripePaymentSuccess(
        int $orderId,
        ?string $sessionId,
        ?string $paymentIntentId,
        ?float $paidAmount
    ): void {
        $connection = DB::connection('sqlsrv');
        $transactionStarted = false;

        try {
            $connection->beginTransaction();
            $transactionStarted = true;
            MsSqlConsoleDebug::push('BEGIN TRANSACTION (PDO)', [], ['executed' => true]);

            $existingPaymentSql = 'SELECT payment_id FROM payments WHERE order_id = ?';
            $existingPaymentBindings = [$orderId];
            $existingPayment = $connection->selectOne($existingPaymentSql, $existingPaymentBindings);
            MsSqlConsoleDebug::push($existingPaymentSql, $existingPaymentBindings, $existingPayment ? (array) $existingPayment : null);

            if ($existingPayment === null) {
                $orderTotalSql = 'SELECT total_amount FROM orders WHERE order_id = ?';
                $orderTotalBindings = [$orderId];
                $orderTotalRow = $connection->selectOne($orderTotalSql, $orderTotalBindings);
                MsSqlConsoleDebug::push($orderTotalSql, $orderTotalBindings, $orderTotalRow ? (array) $orderTotalRow : null);

                if ($orderTotalRow === null) {
                    throw new RuntimeException('Order not found while finalizing payment.');
                }

                $baseAmount = (float) $orderTotalRow->total_amount;
                $insertPaymentSql = 'INSERT INTO payments
                                     (order_id, amount, payment_date, payment_method, gateway, payment_status, failure_reason, created_at, updated_at)
                                     VALUES (?, ?, NULL, ?, ?, ?, NULL, SYSDATETIME(), SYSDATETIME())';
                $insertPaymentBindings = [$orderId, $baseAmount, 'online', 'stripe', 'pending'];
                $inserted = $connection->insert($insertPaymentSql, $insertPaymentBindings);
                MsSqlConsoleDebug::push($insertPaymentSql, $insertPaymentBindings, ['inserted' => $inserted]);
            }

            if ($paidAmount !== null && $paidAmount > 0) {
                $updatePaymentSql = "UPDATE payments
                                     SET amount = ?, payment_status = 'paid', payment_method = 'online', gateway = 'stripe',
                                         payment_date = COALESCE(payment_date, SYSDATETIME()),
                                         stripe_checkout_session_id = COALESCE(?, stripe_checkout_session_id),
                                         stripe_payment_intent_id = COALESCE(?, stripe_payment_intent_id),
                                         failure_reason = NULL,
                                         updated_at = SYSDATETIME()
                                     WHERE order_id = ?";
                $updatePaymentBindings = [$paidAmount, $sessionId, $paymentIntentId, $orderId];
            } else {
                $updatePaymentSql = "UPDATE payments
                                     SET payment_status = 'paid', payment_method = 'online', gateway = 'stripe',
                                         payment_date = COALESCE(payment_date, SYSDATETIME()),
                                         stripe_checkout_session_id = COALESCE(?, stripe_checkout_session_id),
                                         stripe_payment_intent_id = COALESCE(?, stripe_payment_intent_id),
                                         failure_reason = NULL,
                                         updated_at = SYSDATETIME()
                                     WHERE order_id = ?";
                $updatePaymentBindings = [$sessionId, $paymentIntentId, $orderId];
            }

            $paymentUpdatedRows = $connection->update($updatePaymentSql, $updatePaymentBindings);
            MsSqlConsoleDebug::push($updatePaymentSql, $updatePaymentBindings, ['affected_rows' => $paymentUpdatedRows]);

            $updateOrderSql = "UPDATE orders
                               SET is_paid = 1,
                                   order_status = CASE WHEN order_status = 'pending' THEN 'confirmed' ELSE order_status END,
                                   updated_at = SYSDATETIME()
                               WHERE order_id = ?";
            $updateOrderBindings = [$orderId];
            $orderUpdatedRows = $connection->update($updateOrderSql, $updateOrderBindings);
            MsSqlConsoleDebug::push($updateOrderSql, $updateOrderBindings, ['affected_rows' => $orderUpdatedRows]);

            $connection->commit();
            MsSqlConsoleDebug::push('COMMIT TRANSACTION (PDO)', [], ['executed' => true]);
            $transactionStarted = false;
        } catch (\Throwable $exception) {
            if ($transactionStarted && $connection->transactionLevel() > 0) {
                try {
                    $connection->rollBack();
                    MsSqlConsoleDebug::push('ROLLBACK TRANSACTION (PDO)', [], ['executed' => true]);
                } catch (\Throwable) {
                    // Keep the original exception.
                }
            }

            throw $exception;
        }
    }

    private function markStripeSessionAsCancelled(string $sessionId, string $reason): void
    {
        $sql = "UPDATE payments
                SET payment_status = CASE WHEN payment_status = 'pending' THEN 'cancelled' ELSE payment_status END,
                    failure_reason = CASE WHEN payment_status = 'pending' THEN ? ELSE failure_reason END,
                    updated_at = SYSDATETIME()
                WHERE stripe_checkout_session_id = ?";
        $bindings = [$reason, $sessionId];
        $affectedRows = DB::connection('sqlsrv')->update($sql, $bindings);
        MsSqlConsoleDebug::push($sql, $bindings, ['affected_rows' => $affectedRows]);
    }

    private function markStripePaymentIntentAsFailed(string $paymentIntentId, string $reason): void
    {
        $sql = "UPDATE payments
                SET payment_status = CASE WHEN payment_status = 'pending' THEN 'failed' ELSE payment_status END,
                    failure_reason = CASE WHEN payment_status = 'pending' THEN ? ELSE failure_reason END,
                    updated_at = SYSDATETIME()
                WHERE stripe_payment_intent_id = ?";
        $bindings = [$reason, $paymentIntentId];
        $affectedRows = DB::connection('sqlsrv')->update($sql, $bindings);
        MsSqlConsoleDebug::push($sql, $bindings, ['affected_rows' => $affectedRows]);
    }

    private function resolveOrderIdFromCheckoutSession(object $session): ?int
    {
        $metadataOrderId = (int) (($session->metadata->order_id ?? 0));
        if ($metadataOrderId > 0) {
            return $metadataOrderId;
        }

        $sessionId = (string) ($session->id ?? '');
        if ($sessionId === '') {
            return null;
        }

        $sql = 'SELECT order_id FROM payments WHERE stripe_checkout_session_id = ?';
        $bindings = [$sessionId];
        $row = DB::connection('sqlsrv')->selectOne($sql, $bindings);
        MsSqlConsoleDebug::push($sql, $bindings, $row ? (array) $row : null);

        if ($row === null) {
            return null;
        }

        return (int) $row->order_id;
    }

    private function extractStripeResourceId(mixed $resource): ?string
    {
        if (is_string($resource) && $resource !== '') {
            return $resource;
        }

        if (is_object($resource) && isset($resource->id) && is_string($resource->id) && $resource->id !== '') {
            return $resource->id;
        }

        return null;
    }
}
