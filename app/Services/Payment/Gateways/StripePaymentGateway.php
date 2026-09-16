<?php

namespace App\Services\Payment\Gateways;

use App\Models\Order;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\Payment\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StripePaymentGateway implements PaymentGatewayInterface
{
    public function getIdentifier(): string
    {
        return 'stripe';
    }

    public function getName(): string
    {
        return 'Credit / Debit Card (Stripe)';
    }

    public function isConfigured(): bool
    {
        return !empty(config('services.stripe.secret', env('STRIPE_SECRET')));
    }

    /**
     * Initiate Stripe Checkout session.
     */
    public function initiatePayment(Order $order, array $options = []): array
    {
        $secretKey = config('services.stripe.secret', env('STRIPE_SECRET'));

        if (empty($secretKey)) {
            // If Stripe credentials are not present, throw or return descriptive guidance
            throw new \RuntimeException('Stripe API secret key is not configured in .env (STRIPE_SECRET).');
        }

        // Prepare line items for Stripe Checkout
        $lineItems = [];
        foreach ($order->items as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => strtolower($order->currency),
                    'unit_amount' => (int) round($item->price * 100),
                    'product_data' => [
                        'name' => $item->item_name,
                    ],
                ],
                'quantity' => $item->quantity,
            ];
        }

        $successUrl = route('checkout.callback', ['order' => $order->id, 'session_id' => '{CHECKOUT_SESSION_ID}']);
        $cancelUrl = route('checkout.callback', ['order' => $order->id, 'stripe_status' => 'cancel']);

        try {
            $response = Http::withToken($secretKey)
                ->asForm()
                ->post('https://api.stripe.com/v1/checkout/sessions', [
                    'payment_method_types' => ['card'],
                    'mode' => 'payment',
                    'success_url' => $successUrl,
                    'cancel_url' => $cancelUrl,
                    'client_reference_id' => (string) $order->id,
                    'customer_email' => $order->user?->email,
                    'line_items' => $lineItems,
                ]);

            if ($response->successful()) {
                $session = $response->json();
                return [
                    'redirect_url' => $session['url'] ?? $successUrl,
                    'transaction_id' => $session['id'] ?? null,
                    'provider' => 'stripe',
                ];
            }

            Log::error('Stripe Checkout session creation failed', ['response' => $response->body()]);
            throw new \RuntimeException('Stripe session creation failed: ' . ($response->json('error.message') ?? 'Unknown error'));
        } catch (\Exception $e) {
            Log::error('Stripe gateway exception: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify Stripe session status on return.
     */
    public function verifyPayment(Order $order, array $payload = []): PaymentResult
    {
        if (isset($payload['stripe_status']) && $payload['stripe_status'] === 'cancel') {
            return PaymentResult::cancelled('Stripe checkout was cancelled by customer.', $payload);
        }

        $sessionId = $payload['session_id'] ?? null;
        if (empty($sessionId)) {
            return PaymentResult::failed('Missing Stripe Checkout session ID.', $payload);
        }

        $secretKey = config('services.stripe.secret', env('STRIPE_SECRET'));
        if (empty($secretKey)) {
            return PaymentResult::failed('Stripe is not configured on this server.', $payload);
        }

        try {
            $response = Http::withToken($secretKey)->get("https://api.stripe.com/v1/checkout/sessions/{$sessionId}");

            if ($response->successful()) {
                $data = $response->json();
                if (($data['payment_status'] ?? '') === 'paid') {
                    $txnId = $data['payment_intent'] ?? $sessionId;
                    return PaymentResult::success($txnId, 'Stripe payment verified.', $data);
                }

                return PaymentResult::pending($sessionId, 'Stripe payment is still processing.', $data);
            }

            return PaymentResult::failed('Unable to verify Stripe session.', $response->json() ?? []);
        } catch (\Exception $e) {
            return PaymentResult::failed('Stripe verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle Stripe Webhook event.
     */
    public function handleWebhook(Request $request): PaymentResult
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret', env('STRIPE_WEBHOOK_SECRET'));

        // Basic payload parsing
        $event = json_decode($payload, true);
        $type = $event['type'] ?? null;

        if ($type === 'checkout.session.completed') {
            $session = $event['data']['object'] ?? [];
            $txnId = $session['payment_intent'] ?? $session['id'] ?? null;
            return PaymentResult::success($txnId, 'Stripe webhook payment completed.', $event);
        }

        return PaymentResult::pending(null, 'Unhandled event type: ' . $type, $event ?? []);
    }
}
