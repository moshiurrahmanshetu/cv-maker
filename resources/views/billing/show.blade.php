@extends('layouts.app')

@section('title', 'Receipt #' . $order->order_number . ' - CV Maker')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Navigation / Back Actions -->
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <a href="{{ route('billing.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Back to Billing History
                </a>
                <button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print Receipt
                </button>
            </div>

            <!-- Receipt Container -->
            <div class="card-saas p-4 p-md-5 border-0 shadow-sm print-container">
                <!-- Receipt Header -->
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start border-bottom pb-4 mb-4 gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="brand-icon bg-primary text-white p-2 rounded-3 d-inline-flex">
                                <i class="bi bi-file-earmark-person fs-5"></i>
                            </div>
                            <span class="fs-4 fw-bold text-dark">CV Maker</span>
                        </div>
                        <div class="text-muted small">
                            Official Purchase Receipt & Tax Invoice
                        </div>
                    </div>
                    <div class="text-sm-end">
                        <div class="small text-muted">Receipt Number</div>
                        <div class="font-monospace fw-bold fs-5 text-dark">{{ $order->order_number }}</div>
                        <div class="small text-muted mt-1">
                            Date: {{ $order->created_at->format('M d, Y') }}
                        </div>
                        <div class="mt-2">
                            @if($order->status === 'paid')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 fw-semibold">PAID</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border px-3 py-1 fw-semibold text-uppercase">{{ $order->status }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Billed To & Payment Meta -->
                <div class="row g-3 mb-4 pb-3 border-bottom">
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Billed To</div>
                        <div class="fw-bold text-dark">{{ $order->user->name }}</div>
                        <div class="text-muted small">{{ $order->user->email }}</div>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Payment Details</div>
                        <div class="small text-muted">Method: <strong class="text-capitalize text-dark">{{ $order->payment_provider }}</strong></div>
                        @if($order->reference_id)
                            <div class="small text-muted">Ref: <span class="font-monospace text-dark">{{ $order->reference_id }}</span></div>
                        @endif
                        @if($order->paid_at)
                            <div class="small text-muted">Settled: <span class="text-dark">{{ $order->paid_at->format('M d, Y H:i') }}</span></div>
                        @endif
                    </div>
                </div>

                <!-- Line Items Table -->
                <div class="table-responsive mb-4">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="py-2">Item Description</th>
                                <th class="text-center py-2">Qty</th>
                                <th class="text-end py-2">Unit Price</th>
                                <th class="text-end py-2">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="py-3">
                                        <div class="fw-semibold text-dark">{{ $item->product_name }}</div>
                                        <div class="text-muted small">Lifetime access license & PDF export rights</div>
                                    </td>
                                    <td class="text-center py-3 text-muted">{{ $item->quantity }}</td>
                                    <td class="text-end py-3 text-muted">{{ $order->currency === 'USD' ? '$' : $order->currency . ' ' }}{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end py-3 fw-bold text-dark">{{ $order->currency === 'USD' ? '$' : $order->currency . ' ' }}{{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Totals Summary -->
                <div class="row justify-content-end mb-4">
                    <div class="col-sm-6 col-md-5">
                        <div class="d-flex justify-content-between py-1 text-muted small">
                            <span>Subtotal:</span>
                            <span>{{ $order->getFormattedTotal() }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 text-muted small">
                            <span>Tax / VAT (0%):</span>
                            <span>$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-top border-bottom my-2 fw-bold text-dark fs-5">
                            <span>Total Paid:</span>
                            <span class="text-primary">{{ $order->getFormattedTotal() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Receipt Footer -->
                <div class="text-center text-muted small border-top pt-4">
                    <p class="mb-1">Thank you for your business! This is an electronically generated receipt.</p>
                    <p class="mb-0" style="font-size: 0.75rem;">For questions regarding this purchase, contact support with your order reference #{{ $order->order_number }}.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    body * {
        visibility: hidden;
    }
    .print-container, .print-container * {
        visibility: visible;
    }
    .print-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
    .no-print {
        display: none !important;
    }
}
</style>
@endpush
