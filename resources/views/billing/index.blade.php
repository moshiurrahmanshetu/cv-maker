@extends('layouts.app')

@section('title', 'My Purchases & Billing - CV Maker')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Billing & Purchases</h1>
            <p class="text-muted small mb-0">View your active entitlements, purchase history, and download receipts.</p>
        </div>
        <div>
            <a href="{{ route('cvs.index') }}" class="btn-saas-secondary btn-sm">
                <i class="bi bi-file-earmark-text me-1"></i> My Documents
            </a>
        </div>
    </div>

    <!-- Overview Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card-saas p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-palette fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Unlocked Templates</div>
                    <div class="h4 fw-bold text-dark mb-0">{{ $stats['unlocked_count'] ?? $entitlements->count() }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-4">
            <div class="card-saas p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-receipt fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Completed Orders</div>
                    <div class="h4 fw-bold text-dark mb-0">{{ $stats['completed_orders'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-4">
            <div class="card-saas p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-currency-dollar fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Total Spent</div>
                    <div class="h4 fw-bold text-dark mb-0">${{ number_format($stats['total_spent'] ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Entitlements Section -->
    <div class="card-saas mb-4">
        <div class="card-header bg-transparent border-bottom py-3 px-4">
            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-patch-check-fill text-primary"></i>
                Active Premium Entitlements
            </h5>
        </div>
        <div class="card-body p-4">
            @if($entitlements->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-unlock fs-2 d-block mb-2 text-secondary"></i>
                    <p class="mb-1 fw-medium">You haven't unlocked any premium templates yet.</p>
                    <p class="small text-muted mb-0">Free templates are always available to create and export unlimited documents.</p>
                </div>
            @else
                <div class="row g-3">
                    @foreach($entitlements as $entitlement)
                        <div class="col-md-6 col-lg-4">
                            <div class="border rounded-3 p-3 bg-light d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-bold text-dark">
                                        {{ $entitlement->template ? $entitlement->template->name : ($entitlement->item_id ?? 'Premium Template') }}
                                    </div>
                                    <div class="text-muted small">
                                        Unlocked: {{ $entitlement->created_at->format('M d, Y') }}
                                    </div>
                                </div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle-fill me-1"></i> Active
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Orders History Table -->
    <div class="card-saas">
        <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-secondary"></i>
                Order & Payment History
            </h5>
        </div>
        <div class="card-body p-0">
            @if($orders->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-receipt-cutoff fs-2 d-block mb-2 text-secondary"></i>
                    <p class="mb-0 fw-medium">No payment transactions found.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Order Number</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Payment Method</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td class="ps-4 font-monospace fw-semibold text-dark">
                                        {{ $order->order_number }}
                                    </td>
                                    <td class="small text-muted">
                                        {{ $order->created_at->format('M d, Y') }}
                                    </td>
                                    <td>
                                        @foreach($order->items as $item)
                                            <div class="small fw-semibold text-dark">{{ $item->product_name }}</div>
                                        @endforeach
                                    </td>
                                    <td class="small text-capitalize">
                                        {{ $order->payment_provider }}
                                    </td>
                                    <td class="fw-bold text-dark">
                                        {{ $order->getFormattedTotal() }}
                                    </td>
                                    <td>
                                        @if($order->status === 'paid')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Paid</span>
                                        @elseif($order->status === 'pending')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Pending</span>
                                        @elseif($order->status === 'failed')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Failed</span>
                                        @elseif($order->status === 'refunded')
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Refunded</span>
                                        @else
                                            <span class="badge bg-light text-dark border">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('billing.orders.show', $order) }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-eye me-1"></i> Receipt
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($orders->hasPages())
                    <div class="p-3 border-top d-flex justify-content-end">
                        {{ $orders->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
