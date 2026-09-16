<?php

namespace App\Services\Payment\Gateways;

use App\Models\Order;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\Payment\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MockPaymentGateway implements PaymentGatewayInterface
{
    public function getIdentifier(): string
    {
        return 'mock';
    }

    public function getName(): string
    {
        return 'Sandbox Test Payment (Demo / Dev Mode)';
    }

    /**
     * Initiate simulated checkout transaction.
     */
    public function initiatePayment(Order $order, array $options = []): array
    {
        $mockAction = $options['mock_action'] ?? 'success';
        
        $callbackUrl = route('checkout.callback', [
            'order' => $order->id,
            'mock_action' => $mockAction,
            'mock_nonce' => Str::random(16),
        ]);

        return [
            'redirect_url' => $callbackUrl,
            'transaction_id' => 'INIT-' . strtoupper(Str::random(10)),
            'provider' => 'mock',
        ];
    }

    /**
     * Verify simulated test payment.
     */
    public function verifyPayment(Order $order, array $payload = []): PaymentResult
    {
        $action = $payload['mock_action'] ?? 'success';

        if ($action === 'fail') {
            return PaymentResult::failed('Sandbox payment simulated failure.', [
                'provider' => 'mock',
                'simulated_reason' => 'insufficient_funds',
            ]);
        }

        if ($action === 'cancel') {
            return PaymentResult::cancelled('Sandbox checkout was cancelled by user.', [
                'provider' => 'mock',
            ]);
        }

        $transactionId = 'TEST-TXN-' . strtoupper(Str::random(12));

        return PaymentResult::success($transactionId, 'Sandbox test payment authorized successfully.', [
            'provider' => 'mock',
            'order_number' => $order->order_number,
            'amount' => $order->total_amount,
            'currency' => $order->currency,
            'verified_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Handle mock webhook.
     */
    public function handleWebhook(Request $request): PaymentResult
    {
        $orderId = $request->input('order_id');
        $status = $request->input('status', 'success');

        if ($status === 'success') {
            $txn = $request->input('transaction_id', 'TEST-WH-' . strtoupper(Str::random(12)));
            return PaymentResult::success($txn, 'Webhook verified payment.', $request->all());
        }

        return PaymentResult::failed('Webhook reported payment failure.', $request->all());
    }
}
