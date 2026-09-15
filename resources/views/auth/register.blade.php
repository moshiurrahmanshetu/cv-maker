@extends('layouts.guest')

@section('title', 'Create Account - CV Maker')

@section('content')
<div class="mb-4">
    <h2 class="h4 fw-bold mb-1">Create your account</h2>
    <p class="text-muted small mb-0">Start building and managing professional CVs in minutes.</p>
</div>

<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="e.g. John Doe">
        @error('name')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="name@company.com">
        @error('email')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="At least 8 characters">
        @error('password')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="password-confirm" class="form-label">Confirm Password</label>
        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Re-type password">
    </div>

    <div class="d-grid mb-4">
        <button type="submit" class="btn-saas-primary w-100 justify-content-center py-2">
            <i class="bi bi-person-plus"></i> Create Account
        </button>
    </div>

    <div class="text-center">
        <span class="small text-muted">Already have an account?</span>
        <a href="{{ route('login') }}" class="small text-dark fw-semibold text-decoration-none ms-1">Sign In</a>
    </div>
</form>
@endsection
