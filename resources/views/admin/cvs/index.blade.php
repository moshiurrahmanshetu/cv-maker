@extends('layouts.admin')

@section('title', 'All CVs - Admin Panel')
@section('page-title', 'Global CV Directory')

@section('content')
<div class="card card-saas mb-4 p-3">
    <form method="GET" action="{{ route('admin.cvs.index') }}" class="row g-2 align-items-center">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search by title, owner name or email..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Drafts Only</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published Only</option>
            </select>
        </div>
        <div class="col-6 col-md-3 d-flex gap-2">
            <button type="submit" class="btn-saas-secondary w-100 justify-content-center">
                Filter
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.cvs.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center" title="Clear Filters">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<div class="card card-saas">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-surface-subtle border-bottom">
                    <tr class="small text-muted text-uppercase">
                        <th class="ps-4">Resume Title</th>
                        <th>Owner</th>
                        <th>Status</th>
                        <th>Template</th>
                        <th>Completion</th>
                        <th>Last Modified</th>
                        <th class="pe-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cvs as $cv)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold text-dark">{{ $cv->title }}</div>
                                <div class="small text-muted">{{ $cv->personalInfo?->job_title ?? 'No title' }}</div>
                            </td>
                            <td>
                                <div class="small fw-semibold">{{ $cv->user?->name ?? 'Deleted User' }}</div>
                                <div class="small text-muted">{{ $cv->user?->email }}</div>
                            </td>
                            <td>
                                @if($cv->isPublished())
                                    <span class="badge-saas-published">Published</span>
                                @else
                                    <span class="badge-saas-draft">Draft</span>
                                @endif
                            </td>
                            <td>
                                <span class="small text-secondary fw-medium text-capitalize">{{ $cv->template_key }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2" style="max-width: 110px;">
                                    <div class="progress-saas flex-grow-1">
                                        <div class="progress-saas-bar" style="width: {{ $cv->completion_percentage }}%;"></div>
                                    </div>
                                    <span class="small text-muted">{{ $cv->completion_percentage }}%</span>
                                </div>
                            </td>
                            <td class="small text-muted">
                                {{ $cv->updated_at->format('M d, Y') }}
                            </td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('admin.cvs.show', $cv) }}" class="btn btn-sm btn-saas-secondary py-1 px-2" title="View Full CV">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No resumes found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $cvs->links('pagination::bootstrap-5') }}
</div>
@endsection
