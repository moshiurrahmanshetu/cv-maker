<?php

namespace App\Services\Payment;

use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Unique identifier for this provider (e.g. 'mock', 'stripe', 'paypal').
     */
    public function getIdentifier(): string;

    /**
     * User-facing display name for the payment method.
     */
    public function getName(): string;

    /**
     * Initiate payment transaction for an order.
     *
     * @param Order $order
     * @param array $options
     * @return array Array containing redirect_url, transaction_id, or client payload
     */
    public function initiatePayment(Order $order, array $options = []): array;

    /**
     * Verify payment status on return / redirect callback.
     *
     * @param Order $order
     * @param array $payload
     * @return PaymentResult
     */
    public function verifyPayment(Order $order, array $payload = []): PaymentResult;

    /**
     * Handle asynchronous webhook callback from payment provider.
     *
     * @param Request $request
     * @return PaymentResult
     */
    public function handleWebhook(Request $request): PaymentResult;
}
