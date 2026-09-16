@extends('layouts.admin')

@section('title', 'Orders & Payments - Admin Panel')
@section('page-title', 'Orders & Payment Transactions')

@section('content')
<!-- Summary Metrics Bar -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-saas p-3 d-flex flex-row align-items-center gap-3">
            <div class="rounded-3 bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center">
                <i class="bi bi-currency-dollar fs-4"></i>
            </div>
            <div>
                <div class="text-muted small fw-medium">Total Revenue</div>
                <div class="h4 fw-bold text-dark mb-0">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-saas p-3 d-flex flex-row align-items-center gap-3">
            <div class="rounded-3 bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center">
                <i class="bi bi-receipt fs-4"></i>
            </div>
            <div>
                <div class="text-muted small fw-medium">Total Orders</div>
                <div class="h4 fw-bold text-dark mb-0">{{ $stats['total_orders'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-saas p-3 d-flex flex-row align-items-center gap-3">
            <div class="rounded-3 bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center">
                <i class="bi bi-check-circle fs-4"></i>
            </div>
            <div>
                <div class="text-muted small fw-medium">Paid Orders</div>
                <div class="h4 fw-bold text-dark mb-0">{{ $stats['paid_orders'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-saas p-3 d-flex flex-row align-items-center gap-3">
            <div class="rounded-3 bg-warning-subtle text-warning-emphasis p-3 d-flex align-items-center justify-content-center">
                <i class="bi bi-exclamation-circle fs-4"></i>
            </div>
            <div>
                <div class="text-muted small fw-medium">Failed / Pending</div>
                <div class="h4 fw-bold text-dark mb-0">{{ $stats['failed_orders'] ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card card-saas mb-4 p-3">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search order #, customer name, email..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="provider" class="form-select" onchange="this.form.submit()">
                <option value="">All Gateways</option>
                <option value="stripe" {{ request('provider') === 'stripe' ? 'selected' : '' }}>Stripe</option>
                <option value="mock" {{ request('provider') === 'mock' ? 'selected' : '' }}>Mock</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn-saas-secondary w-100 justify-content-center">
                Filter
            </button>
            @if(request('search') || request('status') || request('provider'))
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center" title="Clear Filters">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="card card-saas">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-surface-subtle border-bottom">
                    <tr class="small text-muted text-uppercase">
                        <th class="ps-4">Order Number</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Gateway</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="pe-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="ps-4 font-monospace fw-semibold text-dark">
                                {{ $order->order_number }}
                            </td>
                            <td>
                                @if($order->user)
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $order->user->name }}</div>
                                        <div class="small text-muted">{{ $order->user->email }}</div>
                                    </div>
                                @else
                                    <span class="text-muted small">User deleted</span>
                                @endif
                            </td>
                            <td>
                                @foreach($order->items as $item)
                                    <div class="small fw-medium text-dark text-truncate" style="max-width: 200px;">
                                        {{ $item->product_name }}
                                    </div>
                                @endforeach
                            </td>
                            <td class="fw-bold text-dark">
                                {{ $order->getFormattedTotal() }}
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border text-capitalize">
                                    {{ $order->payment_provider }}
                                </span>
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
                            <td class="small text-muted">
                                {{ $order->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye me-1"></i> Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt-cutoff fs-2 d-block mb-2 text-secondary"></i>
                                No orders matching the criteria found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
