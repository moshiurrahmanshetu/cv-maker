@extends('layouts.app')

@section('title', 'Payment Successful - CV Maker')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 text-center">
            <div class="card-saas p-5 mb-4 shadow-sm border-0">
                <!-- Success Animated/Solid Icon -->
                <div class="mb-4">
                    <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-check-lg display-4 fw-bold"></i>
                    </div>
                </div>

                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 fw-semibold small mb-2 d-inline-block">
                    Payment Completed
                </span>
                <h2 class="h3 fw-bold text-dark mb-2">Thank you! Your access is now unlocked.</h2>
                <p class="text-muted mb-4">
                    Your transaction has been confirmed. You now have lifetime access to your purchased template and high-resolution PDF exports.
                </p>

                <!-- Order Details Receipt Card -->
                <div class="bg-light rounded-3 p-4 text-start border mb-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <div>
                            <span class="text-muted small">Order Number</span>
                            <div class="fw-bold font-monospace text-dark">{{ $order->order_number }}</div>
                        </div>
                        <div class="text-end">
                            <span class="text-muted small">Amount Paid</span>
                            <div class="fw-bold fs-5 text-primary">{{ $order->getFormattedTotal() }}</div>
                        </div>
                    </div>

                    <div class="row g-3 small">
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Payment Method:</span>
                            <span class="fw-semibold text-dark text-capitalize">{{ $order->payment_provider }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Transaction Date:</span>
                            <span class="fw-semibold text-dark">{{ $order->paid_at ? $order->paid_at->format('M d, Y h:i A') : $order->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                        <div class="col-12">
                            <span class="text-muted d-block">Item(s) Unlocked:</span>
                            @foreach($order->items as $item)
                                <div class="d-flex align-items-center gap-2 mt-1 text-dark fw-semibold">
                                    <i class="bi bi-patch-check-fill text-primary"></i>
                                    <span>{{ $item->product_name }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Next Action Buttons -->
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    @if(request()->query('return_url'))
                        <a href="{{ request()->query('return_url') }}" class="btn-saas-primary py-2 px-4 fw-semibold">
                            <i class="bi bi-arrow-left-circle me-1"></i> Return to Document
                        </a>
                    @else
                        <a href="{{ route('cvs.index') }}" class="btn-saas-primary py-2 px-4 fw-semibold">
                            <i class="bi bi-file-earmark-text me-1"></i> View My Documents
                        </a>
                    @endif
                    <a href="{{ route('billing.orders.show', $order) }}" class="btn btn-outline-secondary py-2 px-4 fw-medium">
                        <i class="bi bi-receipt me-1"></i> View Receipt
                    </a>
                </div>
            </div>

            <div class="text-muted small">
                A confirmation email has been logged to your account. Need assistance? Contact our support team.
            </div>
        </div>
    </div>
</div>
@endsection
