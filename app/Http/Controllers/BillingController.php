<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    /**
     * Display the user's purchases, active entitlements, and invoices.
     */
    public function index()
    {
        $user = Auth::user();

        $orders = $user->orders()
            ->with(['items.product'])
            ->latest()
            ->paginate(10);

        $entitlements = $user->entitlements()
            ->with(['template.category', 'order'])
            ->where('status', 'active')
            ->latest('granted_at')
            ->get();

        return view('billing.index', compact('orders', 'entitlements'));
    }

    /**
     * Display a specific order receipt.
     */
    public function showOrder(Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access to order receipt.');
        }

        $order->load(['items.product.template', 'user']);

        return view('billing.show', compact('order'));
    }
}
