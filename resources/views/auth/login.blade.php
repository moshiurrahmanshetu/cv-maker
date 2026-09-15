@extends('layouts.guest')

@section('title', 'Sign In - CV Maker')

@section('content')
<div class="mb-4">
    <h2 class="h4 fw-bold mb-1">Welcome back</h2>
    <p class="text-muted small mb-0">Enter your credentials to access your CV workspace.</p>
</div>

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="name@company.com">
        @error('email')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="form-label mb-0">Password</label>
            <a href="{{ route('password.request') }}" class="small text-muted text-decoration-none">Forgot password?</a>
        </div>
        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">
        @error('password')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="mb-4 form-check">
        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
        <label class="form-check-label small text-secondary" for="remember">
            Keep me signed in for 30 days
        </label>
    </div>

    <div class="d-grid mb-4">
        <button type="submit" class="btn-saas-primary w-100 justify-content-center py-2">
            <i class="bi bi-box-arrow-in-right"></i> Sign In
        </button>
    </div>

    <div class="text-center">
        <span class="small text-muted">Don't have an account?</span>
        <a href="{{ route('register') }}" class="small text-dark fw-semibold text-decoration-none ms-1">Create an account</a>
    </div>
</form>

<!-- Demo Credentials Helper Note -->
<div class="mt-4 pt-3 border-top border-light text-center">
    <div class="small text-muted mb-1 fw-semibold">Quick Demo Logins:</div>
    <div class="small text-secondary font-monospace" style="font-size: 0.78rem;">
        Admin: <strong>admin@cvmaker.local</strong> / <strong>password123</strong><br>
        User: <strong>user@cvmaker.local</strong> / <strong>password123</strong>
    </div>
</div>
@endsection
