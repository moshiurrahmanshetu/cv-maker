@extends('layouts.app')

@section('title', 'Checkout - ' . $template->name . ' - CV Maker')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Breadcrumb / Back Link -->
            <div class="mb-4">
                <a href="{{ $returnUrl ?? route('dashboard') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Back to previous page
                </a>
            </div>

            <div class="row g-4">
                <!-- Left: Order Summary & Item Details -->
                <div class="col-lg-7">
                    <div class="card-saas h-100 p-4">
                        <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-4">
                            <div>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 small fw-semibold">
                                    <i class="bi bi-star-fill text-warning me-1"></i> Premium Template
                                </span>
                                <h3 class="h4 fw-bold text-dark mt-2 mb-0">Unlock Premium Design</h3>
                            </div>
                            <div class="text-end">
                                <span class="text-muted small">One-time payment</span>
                                <div class="h3 fw-bold text-primary mb-0">{{ $template->getFormattedPrice() }}</div>
                            </div>
                        </div>

                        <!-- Template Feature Card -->
                        <div class="d-flex gap-4 p-3 bg-light rounded-3 border mb-4">
                            @if($template->thumbnail)
                                <img src="{{ asset($template->thumbnail) }}" alt="{{ $template->name }}" class="rounded border bg-white" style="width: 100px; height: 130px; object-fit: cover;">
                            @else
                                <div class="rounded border bg-white d-flex align-items-center justify-content-center text-muted" style="width: 100px; height: 130px;">
                                    <i class="bi bi-file-earmark-pdf fs-1"></i>
                                </div>
                            @endif
                            <div class="d-flex flex-column justify-content-center">
                                <h5 class="fw-bold mb-1">{{ $template->name }}</h5>
                                <p class="text-muted small mb-2">{{ $template->description ?? 'Executive-level layout optimized for modern recruitment and ATS screening.' }}</p>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-secondary-subtle text-secondary small"><i class="bi bi-check-circle-fill text-success me-1"></i> Unlimited PDF Exports</span>
                                    <span class="badge bg-secondary-subtle text-secondary small"><i class="bi bi-check-circle-fill text-success me-1"></i> Lifetime Access</span>
                                    <span class="badge bg-secondary-subtle text-secondary small"><i class="bi bi-check-circle-fill text-success me-1"></i> ATS Optimized</span>
                                </div>
                            </div>
                        </div>

                        <!-- Included Benefits List -->
                        <div class="mb-3">
                            <h6 class="fw-bold text-dark mb-3">What's included in this purchase:</h6>
                            <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
                                <li class="d-flex align-items-center gap-2 small text-secondary">
                                    <i class="bi bi-check2-circle text-primary fs-6"></i>
                                    <span><strong>Lifetime full access</strong> to the <em>{{ $template->name }}</em> template across all your career documents.</span>
                                </li>
                                <li class="d-flex align-items-center gap-2 small text-secondary">
                                    <i class="bi bi-check2-circle text-primary fs-6"></i>
                                    <span><strong>High-resolution PDF generation</strong> with pixel-perfect typography and custom color palettes.</span>
                                </li>
                                <li class="d-flex align-items-center gap-2 small text-secondary">
                                    <i class="bi bi-check2-circle text-primary fs-6"></i>
                                    <span><strong>Recruiter-tested ATS formatting</strong> ensuring your resume passes applicant tracking scanners.</span>
                                </li>
                                <li class="d-flex align-items-center gap-2 small text-secondary">
                                    <i class="bi bi-check2-circle text-primary fs-6"></i>
                                    <span><strong>Instant server-side unlock</strong> — download your PDF immediately after checkout.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Right: Payment Method & Authorization -->
                <div class="col-lg-5">
                    <div class="card-saas p-4">
                        <h4 class="h5 fw-bold text-dark mb-3">Payment Method</h4>

                        @if(session('error'))
                            <div class="alert alert-danger d-flex align-items-center gap-2 py-2 small mb-3">
                                <i class="bi bi-exclamation-octagon-fill fs-6 flex-shrink-0"></i>
                                <div>{{ session('error') }}</div>
                            </div>
                        @endif

                        <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
                            @csrf
                            <input type="hidden" name="payable_type" value="template">
                            <input type="hidden" name="payable_id" value="{{ $template->id }}">
                            @if(isset($product) && $product)
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                            @endif
                            @if(isset($returnUrl))
                                <input type="hidden" name="return_url" value="{{ $returnUrl }}">
                            @endif

                            <!-- Gateway Selection -->
                            <div class="d-flex flex-column gap-3 mb-4">
                                @if(config('services.payment.default_gateway') === 'stripe' || config('services.stripe.key'))
                                    <!-- Stripe / Credit Card -->
                                    <label class="form-check-label border rounded-3 p-3 d-flex align-items-center justify-content-between cursor-pointer payment-option-card">
                                        <div class="d-flex align-items-center gap-3">
                                            <input class="form-check-input mt-0" type="radio" name="payment_gateway" value="stripe" checked>
                                            <div>
                                                <div class="fw-semibold text-dark">Credit or Debit Card</div>
                                                <div class="small text-muted">Visa, Mastercard, Amex via Stripe</div>
                                            </div>
                                        </div>
                                        <i class="bi bi-credit-card fs-4 text-primary"></i>
                                    </label>
                                @endif

                                <!-- Sandbox Mock Provider for Instant Testing / Dev -->
                                <label class="form-check-label border rounded-3 p-3 d-flex align-items-center justify-content-between cursor-pointer payment-option-card bg-light">
                                    <div class="d-flex align-items-center gap-3">
                                        <input class="form-check-input mt-0" type="radio" name="payment_gateway" value="mock" {{ config('services.payment.default_gateway') === 'mock' || !config('services.stripe.key') ? 'checked' : '' }}>
                                        <div>
                                            <div class="fw-semibold text-dark d-flex align-items-center gap-2">
                                                Test Simulator (Sandbox)
                                                <span class="badge bg-info-subtle text-info small">Instant Test</span>
                                            </div>
                                            <div class="small text-muted">Zero-cost simulation for evaluation</div>
                                        </div>
                                    </div>
                                    <i class="bi bi-shield-check fs-4 text-info"></i>
                                </label>
                            </div>

                            <!-- Cost Breakdown -->
                            <div class="border-top pt-3 mb-4">
                                <div class="d-flex justify-content-between small text-muted mb-2">
                                    <span>Item Subtotal</span>
                                    <span>{{ $template->getFormattedPrice() }}</span>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mb-2">
                                    <span>VAT / Tax</span>
                                    <span>$0.00</span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold text-dark fs-5 border-top pt-2">
                                    <span>Total Due</span>
                                    <span class="text-primary">{{ $template->getFormattedPrice() }}</span>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn-saas-primary w-100 py-3 fw-bold fs-6 d-flex align-items-center justify-content-center gap-2 shadow-sm" id="payBtn">
                                <i class="bi bi-lock-fill"></i> Pay & Unlock Now
                            </button>
                        </form>

                        <!-- Trust / Security Signals -->
                        <div class="text-center mt-4 pt-3 border-top">
                            <div class="d-flex align-items-center justify-content-center gap-2 text-muted small mb-1">
                                <i class="bi bi-shield-lock text-success fs-5"></i>
                                <span class="fw-medium">Bank-grade 256-Bit SSL Encryption</span>
                            </div>
                            <p class="text-muted" style="font-size: 0.75rem;">
                                Your payment details are processed securely. We do not store raw card numbers on our servers.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.payment-option-card {
    transition: all 0.2s ease-in-out;
}
.payment-option-card:hover {
    border-color: #4f46e5 !important;
    background-color: #fafafa;
}
.cursor-pointer {
    cursor: pointer;
}
</style>
@endpush
