@extends('layouts.guest')

@section('title', 'Set New Password - CV Maker')

@section('content')
<div class="mb-4">
    <h2 class="h4 fw-bold mb-1">Set New Password</h2>
    <p class="text-muted small mb-0">Enter your new password below to reset your credentials.</p>
</div>

<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
        @error('email')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">New Password</label>
        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="At least 8 characters">
        @error('password')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="password-confirm" class="form-label">Confirm New Password</label>
        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Re-type new password">
    </div>

    <div class="d-grid mb-3">
        <button type="submit" class="btn-saas-primary w-100 justify-content-center py-2">
            <i class="bi bi-shield-check"></i> Reset Password
        </button>
    </div>
</form>
@endsection
