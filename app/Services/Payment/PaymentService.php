<?php

namespace App\Services\Payment;

use App\Models\CvTemplate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\UserEntitlement;
use App\Services\Payment\Gateways\MockPaymentGateway;
use App\Services\Payment\Gateways\StripePaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Registered payment gateway adapters.
     *
     * @var array<string, PaymentGatewayInterface>
     */
    protected array $gateways = [];

    public function __construct(
        MockPaymentGateway $mockGateway,
        StripePaymentGateway $stripeGateway
    ) {
        $this->registerGateway($mockGateway);
        $this->registerGateway($stripeGateway);
    }

    /**
     * Register a payment gateway.
     */
    public function registerGateway(PaymentGatewayInterface $gateway): void
    {
        $this->gateways[$gateway->getIdentifier()] = $gateway;
    }

    /**
     * Resolve a payment gateway by key with fallback to mock.
     */
    public function getGateway(string $key = 'mock'): PaymentGatewayInterface
    {
        if (!isset($this->gateways[$key])) {
            Log::warning("Payment gateway [{$key}] not found. Falling back to [mock].");
            return $this->gateways['mock'];
        }

        return $this->gateways[$key];
    }

    /**
     * Get all available/configured gateways for checkout.
     */
    public function getAvailableGateways(): array
    {
        return $this->gateways;
    }

    /**
     * Create an order for unlocking a premium template.
     * Enforces authoritative server-side price lookup.
     */
    public function createOrderForTemplate(User $user, CvTemplate $template, string $gatewayKey = 'mock'): Order
    {
        if (!$template->is_premium) {
            throw new \InvalidArgumentException("Template [{$template->name}] is free and does not require an order.");
        }

        // Authoritative price check
        $product = Product::where('cv_template_id', $template->id)->first();
        $price = $product ? (float) $product->price : (float) $template->getPrice();
        $currency = $product?->currency ?? 'USD';

        return DB::transaction(function () use ($user, $template, $product, $price, $currency, $gatewayKey) {
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'total_amount' => $price,
                'currency' => $currency,
                'status' => 'pending',
                'payment_provider' => $gatewayKey,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product?->id,
                'item_name' => "Premium Template: {$template->name}",
                'price' => $price,
                'quantity' => 1,
                'subtotal' => $price,
            ]);

            return $order;
        });
    }

    /**
     * Initiate payment transaction with the selected gateway.
     */
    public function initiatePayment(Order $order, array $options = []): array
    {
        $gateway = $this->getGateway($order->payment_provider);
        return $gateway->initiatePayment($order, $options);
    }

    /**
     * Verify payment status and fulfill entitlement idempotently.
     */
    public function verifyAndFulfill(Order $order, array $payload = []): PaymentResult
    {
        // If already paid, return successful immediately (idempotent safeguard)
        if ($order->isPaid()) {
            return PaymentResult::success($order->transaction_id, 'Order already confirmed as paid.');
        }

        $gateway = $this->getGateway($order->payment_provider);
        $result = $gateway->verifyPayment($order, $payload);

        DB::transaction(function () use ($order, $result) {
            if ($result->isSuccessful()) {
                $order->update([
                    'status' => 'paid',
                    'transaction_id' => $result->getTransactionId(),
                    'payment_details' => $result->getRawPayload(),
                    'paid_at' => now(),
                ]);

                // Fulfill entitlements for all items in order
                foreach ($order->items as $item) {
                    $templateId = $item->product?->cv_template_id;

                    // If not found through product, find by name or fallback
                    if (!$templateId && $order->items->first()) {
                        // Extract template name from item_name if direct link was null
                        $templateName = str_replace('Premium Template: ', '', $item->item_name);
                        $templateId = CvTemplate::where('name', $templateName)->value('id');
                    }

                    UserEntitlement::firstOrCreate(
                        [
                            'user_id' => $order->user_id,
                            'cv_template_id' => $templateId,
                            'status' => 'active',
                        ],
                        [
                            'product_id' => $item->product_id,
                            'order_id' => $order->id,
                            'entitlement_type' => 'template_unlock',
                            'granted_at' => now(),
                        ]
                    );
                }
            } elseif ($result->isCancelled()) {
                $order->update([
                    'status' => 'cancelled',
                    'payment_details' => $result->getRawPayload(),
                ]);
            } else {
                $order->update([
                    'status' => 'failed',
                    'payment_details' => $result->getRawPayload(),
                ]);
            }
        });

        return $result;
    }

    /**
     * Handle asynchronous webhook from payment gateway.
     */
    public function handleWebhook(string $gatewayKey, Request $request): PaymentResult
    {
        $gateway = $this->getGateway($gatewayKey);
        $result = $gateway->handleWebhook($request);

        if ($result->isSuccessful()) {
            $orderId = $request->input('client_reference_id') ?? $request->input('order_id');
            if ($orderId) {
                $order = Order::find($orderId);
                if ($order && !$order->isPaid()) {
                    $this->verifyAndFulfill($order, $request->all());
                }
            }
        }

        return $result;
    }
}
