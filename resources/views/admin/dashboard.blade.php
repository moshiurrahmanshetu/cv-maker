@extends('layouts.admin')

@section('title', 'Admin Overview - CV Maker')
@section('page-title', 'Platform Overview')

@section('content')
<!-- Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-widget">
            <div>
                <div class="stat-widget-label">Total Users</div>
                <div class="stat-widget-number">{{ $totalUsers }}</div>
            </div>
            <div class="stat-widget-icon">
                <i class="bi bi-people"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-widget">
            <div>
                <div class="stat-widget-label">Administrators</div>
                <div class="stat-widget-number">{{ $adminUsers }}</div>
            </div>
            <div class="stat-widget-icon">
                <i class="bi bi-shield-check"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-widget">
            <div>
                <div class="stat-widget-label">Total Resumes</div>
                <div class="stat-widget-number">{{ $totalCvs }}</div>
            </div>
            <div class="stat-widget-icon">
                <i class="bi bi-file-earmark-text"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-widget">
            <div>
                <div class="stat-widget-label">Published / Ready</div>
                <div class="stat-widget-number">{{ $publishedCvs }}</div>
            </div>
            <div class="stat-widget-icon">
                <i class="bi bi-check2-circle"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Users Table -->
    <div class="col-lg-6">
        <div class="card card-saas h-100">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h2 class="h6 fw-bold mb-0">
                    <i class="bi bi-person-plus me-1 text-muted"></i> Recent Users
                </h2>
                <a href="{{ route('admin.users.index') }}" class="small text-muted text-decoration-none fw-semibold">
                    View All <i class="bi bi-chevron-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-surface-subtle border-bottom">
                            <tr class="small text-muted text-uppercase">
                                <th class="ps-4">User</th>
                                <th>Role</th>
                                <th class="pe-4 text-end">Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentUsers as $user)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                        <div class="small text-muted">{{ $user->email }}</div>
                                    </td>
                                    <td>
                                        @if($user->isAdmin())
                                            <span class="badge-saas-role-admin">Admin</span>
                                        @else
                                            <span class="badge-saas-role-user">User</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end small text-muted">
                                        {{ $user->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent CVs Table -->
    <div class="col-lg-6">
        <div class="card card-saas h-100">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <h2 class="h6 fw-bold mb-0">
                    <i class="bi bi-file-earmark-diff me-1 text-muted"></i> Latest CVs
                </h2>
                <a href="{{ route('admin.cvs.index') }}" class="small text-muted text-decoration-none fw-semibold">
                    View All <i class="bi bi-chevron-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-surface-subtle border-bottom">
                            <tr class="small text-muted text-uppercase">
                                <th class="ps-4">Title / Owner</th>
                                <th>Status</th>
                                <th class="pe-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentCvs as $cv)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold text-dark text-truncate" style="max-width: 220px;">
                                            {{ $cv->title }}
                                        </div>
                                        <div class="small text-muted">
                                            By {{ $cv->user?->name ?? 'Unknown' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($cv->isPublished())
                                            <span class="badge-saas-published">Published</span>
                                        @else
                                            <span class="badge-saas-draft">Draft</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="{{ route('admin.cvs.show', $cv) }}" class="btn btn-sm btn-saas-secondary py-1 px-2" title="Inspect CV">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
