@extends('layouts.app')

@section('title', 'Payment Incomplete - CV Maker')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div class="card-saas p-5 mb-4 shadow-sm border-0">
                <!-- Failure / Warning Icon -->
                <div class="mb-4">
                    <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-x-circle-fill display-4"></i>
                    </div>
                </div>

                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 fw-semibold small mb-2 d-inline-block">
                    Payment Incomplete
                </span>
                <h2 class="h3 fw-bold text-dark mb-2">We couldn't complete your transaction</h2>
                <p class="text-muted mb-4">
                    {{ session('error') ?? 'The payment process was cancelled or declined by your payment provider. No funds were charged to your account.' }}
                </p>

                <!-- Order Reference if exists -->
                @if($order)
                    <div class="bg-light rounded-3 p-3 text-start border mb-4">
                        <div class="d-flex justify-content-between align-items-center text-muted small">
                            <span>Order Reference:</span>
                            <span class="font-monospace fw-semibold text-dark">{{ $order->order_number }}</span>
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    @if($order && $order->items->first() && $order->items->first()->payable_type === 'template')
                        <a href="{{ route('checkout.template', $order->items->first()->payable_id) }}" class="btn-saas-primary py-2 px-4 fw-semibold">
                            <i class="bi bi-arrow-repeat me-1"></i> Try Again
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn-saas-primary py-2 px-4 fw-semibold">
                            <i class="bi bi-arrow-repeat me-1"></i> Return to Dashboard
                        </a>
                    @endif
                    <a href="{{ route('cvs.index') }}" class="btn btn-outline-secondary py-2 px-4 fw-medium">
                        <i class="bi bi-file-earmark-text me-1"></i> My Documents
                    </a>
                </div>
            </div>

            <div class="text-muted small">
                If you believe this is an error or need help with alternate payment methods, please reach out to our support team.
            </div>
        </div>
    </div>
</div>
@endsection
