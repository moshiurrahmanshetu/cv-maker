@extends('layouts.admin')

@section('title', 'Add New Template - Admin')
@section('page-title', 'Add New Template')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">
        <div class="mb-4">
            <a href="{{ route('admin.templates.index') }}" class="small text-muted text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Back to Templates
            </a>
            <h2 class="h4 fw-bold mt-2 mb-1">Register New Template</h2>
            <p class="text-muted small mb-0">Register a new Blade template directory and metadata into the platform catalog.</p>
        </div>

        <div class="card card-saas">
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('admin.templates.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">Template Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g. Executive Minimalist" required autofocus>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="category_id" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">Select Category...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="key" class="form-label fw-semibold">Directory Key <span class="text-danger">*</span></label>
                            <input type="text" class="form-control font-monospace @error('key') is-invalid @enderror" id="key" name="key" value="{{ old('key') }}" placeholder="e.g. executive-minimalist" required>
                            <div class="form-text text-muted">Matches <code>resources/views/cv-templates/{key}/template.blade.php</code></div>
                            @error('key')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="slug" class="form-label fw-semibold">URL Slug (Optional)</label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug') }}" placeholder="Auto-generated if blank">
                            @error('slug')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">Template Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Brief summary of typography, layout style, and ideal career use cases...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="preview_image" class="form-label fw-semibold">Preview Thumbnail Image</label>
                        <input type="file" class="form-control @error('preview_image') is-invalid @enderror" id="preview_image" name="preview_image" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                        <div class="form-text text-muted">Upload high-res PNG, JPG, WEBP, or SVG preview (Max 2MB). If omitted, default SVG asset will be used.</div>
                        @error('preview_image')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="sort_order" class="form-label fw-semibold">Display Sort Order</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                            @error('sort_order')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_premium" name="is_premium" value="1" {{ old('is_premium') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_premium">Premium Template Tier</label>
                            </div>
                        </div>

                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">Active & Available</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                        <a href="{{ route('admin.templates.index') }}" class="btn-saas-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn-saas-primary px-4">
                            <i class="bi bi-check2-circle"></i> Save Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
