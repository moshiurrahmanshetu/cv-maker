<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    /**
     * Display a listing of all platform purchase orders.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.product'])->latest('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_provider')) {
            $query->where('payment_provider', $request->payment_provider);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        // Calculate summary metrics
        $metrics = [
            'total_revenue' => Order::where('status', 'paid')->sum('total_amount'),
            'paid_count' => Order::where('status', 'paid')->count(),
            'pending_count' => Order::where('status', 'pending')->count(),
            'failed_count' => Order::whereIn('status', ['failed', 'cancelled'])->count(),
        ];

        return view('admin.orders.index', compact('orders', 'metrics'));
    }

    /**
     * Display individual order details.
     */
    public function show(Order $order)
    {
        $order->load(['user', 'items.product.template', 'entitlements']);

        return view('admin.orders.show', compact('order'));
    }
}
