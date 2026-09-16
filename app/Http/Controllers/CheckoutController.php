<?php

namespace App\Http\Controllers;

use App\Models\CvTemplate;
use App\Models\Order;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Display checkout review page for a premium template.
     */
    public function showTemplate(Request $request, CvTemplate $template)
    {
        $user = Auth::user();

        // Check if user already owns access to this template
        if ($user && $user->hasAccessToTemplate($template)) {
            return redirect()->route('billing.index')
                ->with('info', "You already own the '{$template->name}' template.");
        }

        $returnUrl = $request->query('return_url');
        $availableGateways = $this->paymentService->getAvailableGateways();

        return view('checkout.show', compact('template', 'availableGateways', 'returnUrl'));
    }

    /**
     * Process checkout form and initiate payment transaction.
     */
    public function process(Request $request)
    {
        $templateId = $request->input('template_id') ?? $request->input('payable_id');
        $provider = $request->input('payment_provider') ?? $request->input('payment_gateway') ?? 'mock';

        $template = CvTemplate::findOrFail($templateId);
        $user = Auth::user();

        // Prevent double purchase if already owned
        if ($user->hasAccessToTemplate($template)) {
            return redirect()->route('billing.index')
                ->with('info', "You already have access to the '{$template->name}' template.");
        }

        $order = $this->paymentService->createOrderForTemplate($user, $template, $provider);

        $options = [];
        if ($provider === 'mock') {
            $options['mock_action'] = $request->input('mock_action', 'success');
        }

        try {
            $initResult = $this->paymentService->initiatePayment($order, $options);
            return redirect()->away($initResult['redirect_url']);
        } catch (\Exception $e) {
            return back()->with('error', 'Unable to initiate payment: ' . $e->getMessage());
        }
    }

    /**
     * Handle payment return callback from gateway.
     */
    public function callback(Request $request, Order $order)
    {
        // Enforce user authorization on order
        if ($order->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access to order.');
        }

        $result = $this->paymentService->verifyAndFulfill($order, $request->all());

        if ($result->isSuccessful()) {
            return redirect()->route('checkout.success', $order)
                ->with('success', 'Payment confirmed! Your template is now unlocked.');
        }

        return redirect()->route('checkout.failed', ['order' => $order->id, 'reason' => $result->getMessage()]);
    }

    /**
     * Display order confirmation screen upon successful payment.
     */
    public function success(Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access to order.');
        }

        $order->load(['items.product.template', 'user']);

        return view('checkout.success', compact('order'));
    }

    /**
     * Display payment failed / cancelled screen with recovery actions.
     */
    public function failed(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access to order.');
        }

        $reason = $request->query('reason', $order->payment_details['message'] ?? 'Payment could not be completed.');
        $order->load(['items.product.template']);

        return view('checkout.failed', compact('order', 'reason'));
    }

    /**
     * Asynchronous webhook handler for payment providers.
     */
    public function webhook(Request $request, string $gateway)
    {
        $result = $this->paymentService->handleWebhook($gateway, $request);

        return response()->json([
            'status' => $result->getStatus(),
            'message' => $result->getMessage(),
        ], 200);
    }
}
