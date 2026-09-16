@extends('layouts.admin')

@section('title', 'Order ' . $order->order_number . ' - Admin Panel')
@section('page-title', 'Order Details: ' . $order->order_number)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.orders.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i> Back to Orders List
    </a>
</div>

<div class="row g-4">
    <!-- Left Column: Order Meta & Line Items -->
    <div class="col-lg-8">
        <!-- Order Overview Card -->
        <div class="card card-saas mb-4">
            <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-receipt fs-5 text-primary"></i>
                    <h5 class="fw-bold mb-0 text-dark">Order Overview</h5>
                </div>
                <div>
                    @if($order->status === 'paid')
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">PAID</span>
                    @elseif($order->status === 'pending')
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1">PENDING</span>
                    @elseif($order->status === 'failed')
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1">FAILED</span>
                    @elseif($order->status === 'refunded')
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1">REFUNDED</span>
                    @else
                        <span class="badge bg-light text-dark border px-3 py-1">{{ strtoupper($order->status) }}</span>
                    @endif
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Order Number</span>
                        <div class="fw-bold font-monospace text-dark">{{ $order->order_number }}</div>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Total Amount</span>
                        <div class="fw-bold fs-5 text-primary">{{ $order->getFormattedTotal() }}</div>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Payment Provider</span>
                        <div class="fw-semibold text-dark text-capitalize">{{ $order->payment_provider }}</div>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Provider Reference ID</span>
                        <div class="font-monospace text-dark">{{ $order->reference_id ?? 'N/A' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Created At</span>
                        <div class="text-dark">{{ $order->created_at->format('M d, Y H:i:s') }}</div>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Paid At</span>
                        <div class="text-dark">{{ $order->paid_at ? $order->paid_at->format('M d, Y H:i:s') : 'Not settled' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchased Line Items Card -->
        <div class="card card-saas mb-4">
            <div class="card-header bg-transparent border-bottom py-3 px-4">
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-box-seam text-secondary"></i>
                    Purchased Items
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Item Name</th>
                                <th>Type</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end pe-4">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold text-dark">{{ $item->product_name }}</div>
                                        <div class="small text-muted font-monospace">Payable ID: {{ $item->payable_id }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary small text-uppercase">{{ $item->payable_type }}</span>
                                    </td>
                                    <td class="text-center text-muted">{{ $item->quantity }}</td>
                                    <td class="text-end text-muted">{{ $order->currency === 'USD' ? '$' : $order->currency . ' ' }}{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end pe-4 fw-bold text-dark">{{ $order->currency === 'USD' ? '$' : $order->currency . ' ' }}{{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Raw Metadata / Gateway Payload Inspector -->
        @if($order->payment_details)
            <div class="card card-saas">
                <div class="card-header bg-transparent border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-code-slash text-secondary"></i>
                        Gateway Response Payload
                    </h5>
                </div>
                <div class="card-body p-3">
                    <pre class="bg-light p-3 rounded border mb-0" style="font-size: 0.8rem; max-height: 250px; overflow-y: auto;"><code>{{ json_encode($order->payment_details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                </div>
            </div>
        @endif
    </div>

    <!-- Right Column: Customer Info & Entitlements Status -->
    <div class="col-lg-4">
        <!-- Customer Profile Card -->
        <div class="card card-saas mb-4">
            <div class="card-header bg-transparent border-bottom py-3 px-4">
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-person text-secondary"></i>
                    Customer Details
                </h5>
            </div>
            <div class="card-body p-4">
                @if($order->user)
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-person-circle fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">{{ $order->user->name }}</div>
                            <div class="small text-muted">{{ $order->user->email }}</div>
                        </div>
                    </div>
                    <div class="border-top pt-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">User ID:</span>
                            <span class="font-monospace text-dark">#{{ $order->user->id }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Role:</span>
                            <span class="text-capitalize text-dark">{{ $order->user->role }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Joined:</span>
                            <span class="text-dark">{{ $order->user->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                @else
                    <div class="text-muted small">User record has been removed.</div>
                @endif
            </div>
        </div>

        <!-- Entitlements Generated -->
        <div class="card card-saas">
            <div class="card-header bg-transparent border-bottom py-3 px-4">
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check text-success"></i>
                    Active User Entitlements
                </h5>
            </div>
            <div class="card-body p-4">
                @if(isset($entitlements) && $entitlements->isNotEmpty())
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                        @foreach($entitlements as $ent)
                            <li class="p-2 bg-light rounded border small d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold text-dark">{{ $ent->item_type }}: {{ $ent->item_id }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">Granted: {{ $ent->created_at->format('M d, Y H:i') }}</div>
                                </div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted small mb-0">No active entitlements generated for this order.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
