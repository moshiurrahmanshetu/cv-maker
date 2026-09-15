@extends('layouts.app')

@section('title', 'Document Types Management - Admin')

@section('content')
<div class="mb-4 pb-3 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i> Admin Dashboard
            </a>
            <span class="text-muted">/</span>
            <span class="badge bg-dark text-white">System Settings</span>
        </div>
        <h1 class="h3 fw-bold mb-0 text-dark">
            <i class="bi bi-file-earmark-ruled me-2 text-primary"></i> Career Document Types
        </h1>
    </div>

    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.document-types.create') }}" class="btn-saas-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Document Type
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="card card-saas mb-4 p-3 bg-white">
    <form method="GET" action="{{ route('admin.document-types.index') }}" class="row g-2 align-items-center">
        <div class="col-md-6">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control form-control-sm border-start-0" placeholder="Search by name, slug or description..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-saas-primary flex-grow-1">Filter</button>
            <a href="{{ route('admin.document-types.index') }}" class="btn btn-sm btn-saas-secondary">Reset</a>
        </div>
    </form>
</div>

<!-- Document Types Table -->
<div class="card card-saas shadow-sm border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;">Icon</th>
                    <th>Document Type</th>
                    <th>Slug</th>
                    <th>Compatible Templates</th>
                    <th>Documents Created</th>
                    <th>Status</th>
                    <th class="text-end pe-4" style="width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documentTypes as $type)
                    <tr>
                        <td>
                            <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                <i class="bi {{ $type->icon ?: 'bi-file-earmark-text' }} text-primary fs-5"></i>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $type->name }}</div>
                            <div class="small text-muted text-truncate" style="max-width: 280px;">{{ $type->description }}</div>
                        </td>
                        <td>
                            <code class="small text-dark bg-light px-2 py-1 rounded">{{ $type->slug }}</code>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                {{ $type->templates_count }} Template(s)
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ $type->documents_count }} User Doc(s)
                            </span>
                        </td>
                        <td>
                            <form action="{{ route('admin.document-types.toggle-status', $type) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="badge border-0 {{ $type->is_active ? 'badge-saas-published' : 'badge-saas-draft' }}" style="cursor: pointer;">
                                    {{ $type->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex align-items-center justify-content-end gap-1">
                                <a href="{{ route('admin.document-types.edit', $type) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.document-types.destroy', $type) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete document type \'{{ $type->name }}\'?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" {{ $type->documents_count > 0 ? 'disabled' : '' }}>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No document types found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($documentTypes->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $documentTypes->links() }}
        </div>
    @endif
</div>
@endsection
