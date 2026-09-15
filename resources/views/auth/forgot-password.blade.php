@extends('layouts.guest')

@section('title', 'Forgot Password - CV Maker')

@section('content')
<div class="mb-4">
    <h2 class="h4 fw-bold mb-1">Reset Password</h2>
    <p class="text-muted small mb-0">Enter your registered email and we'll send you a password reset link.</p>
</div>

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="mb-4">
        <label for="email" class="form-label">Email Address</label>
        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="name@company.com">
        @error('email')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="d-grid mb-4">
        <button type="submit" class="btn-saas-primary w-100 justify-content-center py-2">
            <i class="bi bi-envelope"></i> Send Reset Link
        </button>
    </div>

    <div class="text-center">
        <a href="{{ route('login') }}" class="small text-muted text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Return to Sign In
        </a>
    </div>
</form>
@endsection
