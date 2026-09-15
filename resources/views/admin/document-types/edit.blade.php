@extends('layouts.app')

@section('title', 'Edit Document Type - Admin')

@section('content')
<div class="mb-4 pb-3 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <a href="{{ route('admin.document-types.index') }}" class="text-muted text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i> Document Types
            </a>
            <span class="text-muted">/</span>
            <span class="small text-muted">Edit: {{ $documentType->name }}</span>
        </div>
        <h1 class="h3 fw-bold mb-0 text-dark">Edit Document Type</h1>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-saas shadow-sm border-0 p-4">
            <form method="POST" action="{{ route('admin.document-types.update', $documentType) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $documentType->name) }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Slug</label>
                        <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $documentType->slug) }}">
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Icon Class</label>
                        <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror" value="{{ old('icon', $documentType->icon) }}">
                        @error('icon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $documentType->sort_order) }}" min="0">
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $documentType->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12 pt-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveCheck" {{ old('is_active', $documentType->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="isActiveCheck">
                                Active (Available for user selection)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between pt-4 mt-4 border-top">
                    <a href="{{ route('admin.document-types.index') }}" class="btn btn-saas-secondary">Cancel</a>
                    <button type="submit" class="btn btn-saas-primary px-4">
                        <i class="bi bi-floppy me-1"></i> Update Document Type
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
