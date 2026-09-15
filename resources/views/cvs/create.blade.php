@extends('layouts.app')

@section('title', 'Create New CV - CV Maker')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">
        <div class="mb-4">
            <a href="{{ route('cvs.index') }}" class="small text-muted text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Back to My CVs
            </a>
            <h1 class="h3 fw-bold mt-2 mb-1">Create New CV</h1>
            <p class="text-muted small mb-0">Start with a title. Your CV will be initialized as a draft so you can fill sections at your own pace.</p>
        </div>

        <div class="card card-saas">
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('cvs.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="title" class="form-label">CV / Resume Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" placeholder="e.g. Senior Software Engineer Resume" required autofocus>
                        <div class="form-text text-muted">A private label to identify this resume version in your dashboard.</div>
                        @error('title')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="job_title" class="form-label">Target Job Title (Optional)</label>
                        <input type="text" class="form-control @error('job_title') is-invalid @enderror" id="job_title" name="job_title" value="{{ old('job_title') }}" placeholder="e.g. Full Stack Developer, Product Manager">
                        <div class="form-text text-muted">The primary headline displayed on your resume.</div>
                        @error('job_title')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4 p-3 bg-surface-subtle border rounded-2">
                        <div class="d-flex align-items-center gap-2 mb-1 fw-semibold small text-dark">
                            <i class="bi bi-info-circle text-muted"></i>
                            <span>Draft-First Architecture</span>
                        </div>
                        <p class="small text-muted mb-0">
                            You are not required to complete all sections right now. You can save your progress as a draft and return whenever you wish.
                        </p>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                        <a href="{{ route('cvs.index') }}" class="btn-saas-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn-saas-primary px-4">
                            <i class="bi bi-arrow-right"></i> Create Draft & Continue
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
