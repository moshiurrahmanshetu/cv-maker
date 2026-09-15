@extends('layouts.admin')

@section('title', 'AI Assistant Telemetry - Admin')
@section('page-title', 'AI Assistant Management')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="h4 fw-bold mb-1">AI Assistant Telemetry & Logs</h2>
        <p class="text-muted small mb-0">Monitor AI generation metrics, token consumption, feature usage, and server-side provider status.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge {{ $aiEnabled ? 'badge-saas-published' : 'badge-saas-draft' }} p-2">
            <i class="bi {{ $aiEnabled ? 'bi-check-circle-fill' : 'bi-pause-circle-fill' }} me-1"></i>
            {{ $aiEnabled ? 'AI Service Online' : 'AI Disabled' }}
        </span>
    </div>
</div>

<!-- Metrics Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-widget">
            <div>
                <div class="stat-widget-label">Total AI Requests</div>
                <div class="stat-widget-number">{{ number_format($totalRequests) }}</div>
            </div>
            <div class="stat-widget-icon">
                <i class="bi bi-robot"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-widget">
            <div>
                <div class="stat-widget-label">Successful Outputs</div>
                <div class="stat-widget-number text-success">{{ number_format($successfulRequests) }}</div>
            </div>
            <div class="stat-widget-icon">
                <i class="bi bi-check2-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-widget">
            <div>
                <div class="stat-widget-label">Total Tokens Tracked</div>
                <div class="stat-widget-number">{{ number_format($totalTokens) }}</div>
            </div>
            <div class="stat-widget-icon">
                <i class="bi bi-cpu"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-widget">
            <div>
                <div class="stat-widget-label">Active Provider</div>
                <div class="fw-bold fs-6 text-uppercase text-dark mt-1">{{ $activeProvider }}</div>
                <div class="small text-muted font-monospace" style="font-size: 0.72rem;">{{ $providers[$activeProvider]['model'] ?? 'default' }}</div>
            </div>
            <div class="stat-widget-icon">
                <i class="bi bi-hdd-network"></i>
            </div>
        </div>
    </div>
</div>

<!-- Configured Capabilities & Architecture Info -->
<div class="card card-saas mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <h3 class="h6 fw-bold mb-0 text-dark">
            <i class="bi bi-sliders me-1"></i> Feature Availability & Provider Architecture
        </h3>
        <span class="small text-muted">Configured via server-side <code>.env</code> & <code>config/ai.php</code></span>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            @foreach($features as $featureKey => $isEnabled)
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="p-3 bg-surface-subtle border rounded d-flex align-items-center justify-content-between">
                        <span class="small fw-semibold text-dark text-capitalize">{{ str_replace('_', ' ', $featureKey) }}</span>
                        @if($isEnabled)
                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.72rem;">Active</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.72rem;">Inactive</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card card-saas mb-4 p-3">
    <form method="GET" action="{{ route('admin.ai.index') }}" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search by user name or email..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="feature" class="form-select" onchange="this.form.submit()">
                <option value="">All AI Features</option>
                <option value="profile_summary" {{ request('feature') === 'profile_summary' ? 'selected' : '' }}>Profile Summary</option>
                <option value="career_objective" {{ request('feature') === 'career_objective' ? 'selected' : '' }}>Career Objective</option>
                <option value="experience_rewrite" {{ request('feature') === 'experience_rewrite' ? 'selected' : '' }}>Experience Rewrite</option>
                <option value="project_rewrite" {{ request('feature') === 'project_rewrite' ? 'selected' : '' }}>Project Rewrite</option>
                <option value="skills_suggestion" {{ request('feature') === 'skills_suggestion' ? 'selected' : '' }}>Skills Suggestion</option>
                <option value="content_improve" {{ request('feature') === 'content_improve' ? 'selected' : '' }}>Content Improve</option>
                <option value="cover_letter" {{ request('feature') === 'cover_letter' ? 'selected' : '' }}>Cover Letter</option>
                <option value="motivation_letter" {{ request('feature') === 'motivation_letter' ? 'selected' : '' }}>Motivation Letter</option>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success Only</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed Only</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn-saas-secondary flex-grow-1 justify-content-center">
                Filter
            </button>
            @if(request()->hasAny(['search', 'feature', 'status']))
                <a href="{{ route('admin.ai.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center" title="Clear Filters">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Telemetry Audit Table -->
<div class="card card-saas overflow-hidden border">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-surface-subtle border-bottom">
                <tr>
                    <th class="ps-4">User</th>
                    <th>Document</th>
                    <th>Feature</th>
                    <th>Provider / Model</th>
                    <th>Tokens</th>
                    <th>Status</th>
                    <th class="pe-4 text-end">Timestamp</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold text-dark small">{{ $log->user?->name ?? 'System / Anonymous' }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">{{ $log->user?->email ?? '-' }}</div>
                        </td>
                        <td>
                            @if($log->cv)
                                <a href="{{ route('admin.cvs.show', $log->cv) }}" class="small fw-semibold text-decoration-none text-dark text-truncate d-inline-block" style="max-width: 180px;">
                                    {{ $log->cv->title }}
                                </a>
                            @else
                                <span class="text-muted small">Standalone</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.72rem;">
                                {{ str_replace('_', ' ', $log->feature) }}
                            </span>
                        </td>
                        <td>
                            <div class="small fw-semibold text-dark text-uppercase">{{ $log->provider }}</div>
                            <div class="text-muted font-monospace" style="font-size: 0.7rem;">{{ $log->model }}</div>
                        </td>
                        <td>
                            <span class="small font-monospace text-muted">{{ number_format($log->total_tokens) }}</span>
                        </td>
                        <td>
                            @if($log->status === 'success')
                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.72rem;">Success</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.72rem;" title="{{ $log->error_message }}">Failed</span>
                            @endif
                        </td>
                        <td class="pe-4 text-end text-muted small" style="font-size: 0.78rem;">
                            {{ $log->created_at->format('M d, Y h:i A') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                            No AI generation logs found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="card-footer bg-white border-top p-3 d-flex justify-content-center">
            {{ $logs->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
