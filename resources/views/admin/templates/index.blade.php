@extends('layouts.admin')

@section('title', 'Template Management - Admin')
@section('page-title', 'Template Management')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="h4 fw-bold mb-1">Career Document & CV Templates Directory</h2>
        <p class="text-muted small mb-0">Manage all visual layouts, Free/Premium tiers, preview assets, and compatible career document types.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.document-types.index') }}" class="btn-saas-secondary">
            <i class="bi bi-file-earmark-ruled"></i> Document Types
        </a>
        <a href="{{ route('admin.templates.categories.index') }}" class="btn-saas-secondary">
            <i class="bi bi-tags"></i> Categories
        </a>
        <a href="{{ route('admin.templates.create') }}" class="btn-saas-primary">
            <i class="bi bi-plus-lg"></i> Add New Template
        </a>
    </div>
</div>

<!-- Search & Filters -->
<div class="card card-saas mb-4 p-3">
    <form method="GET" action="{{ route('admin.templates.index') }}" class="row g-2 align-items-center">
        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name or key..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="document_type_id" class="form-select" onchange="this.form.submit()">
                <option value="">All Document Types</option>
                @foreach($documentTypes as $docType)
                    <option value="{{ $docType->id }}" {{ request('document_type_id') == $docType->id ? 'selected' : '' }}>
                        {{ $docType->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="category_id" class="form-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="type" class="form-select" onchange="this.form.submit()">
                <option value="">All Tiers</option>
                <option value="free" {{ request('type') === 'free' ? 'selected' : '' }}>Free Only</option>
                <option value="premium" {{ request('type') === 'premium' ? 'selected' : '' }}>Premium Only</option>
            </select>
        </div>
        <div class="col-6 col-md-2 d-flex gap-1">
            <button type="submit" class="btn-saas-secondary flex-grow-1 justify-content-center" title="Search">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>
            @if(request()->hasAny(['search', 'document_type_id', 'category_id', 'type', 'status']))
                <a href="{{ route('admin.templates.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center" title="Clear Filters">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Templates Table -->
<div class="card card-saas overflow-hidden border">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-surface-subtle border-bottom">
                <tr>
                    <th class="ps-4" style="width: 70px;">Preview</th>
                    <th>Template Name</th>
                    <th>Compatible Documents</th>
                    <th>Category</th>
                    <th>Access Tier</th>
                    <th>Status</th>
                    <th>Usage</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $template)
                    <tr>
                        <td class="ps-4">
                            <img src="{{ $template->preview_image_url }}" alt="{{ $template->name }}" class="rounded border shadow-sm object-fit-contain bg-white" style="width: 50px; height: 65px;">
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $template->name }}</div>
                            <div class="small text-muted font-monospace">{{ $template->key }}</div>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1" style="max-width: 220px;">
                                @forelse($template->documentTypes as $docType)
                                    <span class="badge bg-light text-dark border small" style="font-size: 0.68rem;">
                                        <i class="bi {{ $docType->icon }} me-1 text-primary"></i>{{ $docType->name }}
                                    </span>
                                @empty
                                    <span class="text-muted small">All / General</span>
                                @endforelse
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-secondary border">{{ $template->category->name }}</span>
                        </td>
                        <td>
                            <form action="{{ route('admin.templates.toggle-premium', $template) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent" title="Click to toggle Free/Premium">
                                    @if($template->is_premium)
                                        <span class="badge-saas-draft cursor-pointer">★ Premium</span>
                                    @else
                                        <span class="badge-saas-published cursor-pointer">Free</span>
                                    @endif
                                </button>
                            </form>
                        </td>
                        <td>
                            <form action="{{ route('admin.templates.toggle-status', $template) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent" title="Click to toggle status">
                                    @if($template->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle cursor-pointer">
                                            <i class="bi bi-check-circle me-1"></i> Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle cursor-pointer">
                                            <i class="bi bi-dash-circle me-1"></i> Inactive
                                        </span>
                                    @endif
                                </button>
                            </form>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border px-2 py-1">
                                {{ $template->cvs_count }} Doc{{ $template->cvs_count === 1 ? '' : 's' }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('templates.preview', $template) }}" target="_blank" class="btn btn-sm btn-saas-secondary py-1 px-2" title="Preview Template">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.templates.edit', $template) }}" class="btn btn-sm btn-saas-secondary py-1 px-2" title="Edit Metadata">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2" data-bs-toggle="modal" data-bs-target="#deleteTemplateModal{{ $template->id }}" title="Delete Template">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Delete Confirmation Modal -->
                    <div class="modal fade" id="deleteTemplateModal{{ $template->id }}" tabindex="-1" aria-labelledby="deleteTemplateModalLabel{{ $template->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border card-saas">
                                <div class="modal-header border-bottom">
                                    <h5 class="modal-title fw-bold fs-6" id="deleteTemplateModalLabel{{ $template->id }}">Delete Template</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body py-4">
                                    @if($template->cvs_count > 0)
                                        <div class="alert alert-warning mb-0">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                            <strong>Cannot Delete:</strong> This template is actively assigned to <strong>{{ $template->cvs_count }}</strong> document(s). Reassign them first.
                                        </div>
                                    @else
                                        <p class="mb-0 text-secondary">
                                            Are you sure you want to permanently delete the template <strong>"{{ $template->name }}"</strong>?
                                        </p>
                                    @endif
                                </div>
                                <div class="modal-footer border-top">
                                    <button type="button" class="btn btn-sm btn-saas-secondary" data-bs-dismiss="modal">Cancel</button>
                                    @if($template->cvs_count === 0)
                                        <form action="{{ route('admin.templates.destroy', $template) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Confirm Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No templates found matching your search.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($templates->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $templates->links() }}
        </div>
    @endif
</div>
@endsection
