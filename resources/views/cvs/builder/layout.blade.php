@extends('layouts.app')

@section('title', 'CV Builder: ' . $cv->title)

@section('content')
<div class="mb-4 pb-3 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <a href="{{ route('cvs.index') }}" class="text-muted text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i> My CVs
            </a>
            <span class="text-muted">/</span>
            @if($cv->isPublished())
                <span class="badge-saas-published">Published</span>
            @else
                <span class="badge-saas-draft">Draft</span>
            @endif
            <span class="small text-muted">({{ $cv->completion_percentage }}% complete)</span>
        </div>
        <h1 class="h3 fw-bold mb-0 text-dark">{{ $cv->title }}</h1>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ route('cvs.show', $cv) }}" class="btn-saas-secondary" target="_blank">
            <i class="bi bi-eye"></i> Preview Document
        </a>

        <!-- Quick Status Toggle -->
        <form action="{{ route('cvs.toggle-status', $cv) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn {{ $cv->isPublished() ? 'btn-saas-secondary' : 'btn-saas-success' }}">
                <i class="bi {{ $cv->isPublished() ? 'bi-arrow-counterclockwise' : 'bi-check2-circle' }}"></i>
                {{ $cv->isPublished() ? 'Revert to Draft' : 'Publish / Mark Ready' }}
            </button>
        </form>
    </div>
</div>

<!-- Mobile Section Selector (Visible only on < lg screens) -->
<div class="d-lg-none card card-saas mb-4 p-3">
    <label class="form-label small fw-bold text-muted mb-2">Switch CV Section</label>
    <select class="form-select" onchange="window.location.href=this.value">
        @foreach($checklist as $key => $sec)
            <option value="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => $key]) }}" {{ $activeSection === $key ? 'selected' : '' }}>
                {{ $sec['label'] }} {{ $sec['count'] > 0 ? "({$sec['count']})" : '' }} {{ $sec['is_complete'] ? '✓' : '' }}
            </option>
        @endforeach
    </select>
</div>

<div class="row g-4">
    <!-- Left Navigation Column (Desktop) -->
    <div class="col-lg-3 d-none d-lg-block">
        <div class="card card-saas sticky-top" style="top: 84px;">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h2 class="h6 fw-bold mb-0 text-dark">
                    <i class="bi bi-list-nested me-1 text-muted"></i> CV Sections
                </h2>
            </div>
            <div class="list-group list-group-flush p-2">
                @foreach($checklist as $key => $sec)
                    <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => $key]) }}" 
                       class="list-group-item list-group-item-action d-flex align-items-center justify-content-between rounded-2 px-3 py-2 mb-1 border-0 {{ $activeSection === $key ? 'bg-dark text-white fw-semibold' : 'text-secondary' }}"
                       style="transition: all 0.15s ease;">
                        <div class="d-flex align-items-center gap-2 text-truncate">
                            <i class="bi {{ $sec['icon'] }} {{ $activeSection === $key ? 'text-white' : 'text-muted' }} fs-6"></i>
                            <span class="small text-truncate">{{ $sec['label'] }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            @if($sec['count'] > 0 && !in_array($key, ['personal-info', 'summary']))
                                <span class="badge {{ $activeSection === $key ? 'bg-light text-dark' : 'bg-surface-muted text-muted' }}" style="font-size: 0.7rem;">
                                    {{ $sec['count'] }}
                                </span>
                            @endif
                            @if($sec['is_complete'])
                                <i class="bi bi-check-circle-fill {{ $activeSection === $key ? 'text-white' : 'text-success' }}" style="font-size: 0.8rem;"></i>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Center Column: Active Section Workspace -->
    <div class="col-lg-6">
        @include('cvs.builder.sections.' . $activeSection)
    </div>

    <!-- Right Sidebar Column: Progress & Checklist -->
    <div class="col-lg-3">
        <div class="card card-saas sticky-top" style="top: 84px;">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h2 class="h6 fw-bold mb-0 text-dark">
                    <i class="bi bi-speedometer2 me-1 text-muted"></i> Resume Health
                </h2>
            </div>
            <div class="card-body p-4">
                <!-- Completion Progress -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-bold text-dark">Completion Score</span>
                        <span class="small fw-bold text-dark">{{ $cv->completion_percentage }}%</span>
                    </div>
                    <div class="progress-saas mb-2">
                        <div class="progress-saas-bar" style="width: {{ $cv->completion_percentage }}%;"></div>
                    </div>
                    <div class="small text-muted" style="font-size: 0.78rem;">
                        {{ $cv->completion_percentage >= 80 ? 'Great progress! Your resume is ready for review.' : 'Fill key sections to strengthen your resume presentation.' }}
                    </div>
                </div>

                <!-- Section Checklist -->
                <h3 class="small fw-bold text-uppercase text-muted mb-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">Section Checklist</h3>
                <ul class="list-unstyled mb-4 d-flex flex-column gap-2" style="font-size: 0.82rem;">
                    @foreach($checklist as $key => $sec)
                        <li class="d-flex align-items-center justify-content-between text-secondary">
                            <a href="{{ route('cvs.builder.show', ['cv' => $cv, 'section' => $key]) }}" class="text-decoration-none text-secondary d-flex align-items-center gap-2 text-truncate">
                                <i class="bi {{ $sec['icon'] }} text-muted"></i>
                                <span class="text-truncate">{{ $sec['label'] }}</span>
                            </a>
                            @if($sec['is_complete'])
                                <span class="badge-saas-published py-0 px-2" style="font-size: 0.7rem;">Done</span>
                            @else
                                <span class="text-muted" style="font-size: 0.7rem;">Pending</span>
                            @endif
                        </li>
                    @endforeach
                </ul>

                <!-- Primary Action Buttons -->
                <div class="d-grid gap-2 pt-3 border-top">
                    <a href="{{ route('cvs.show', $cv) }}" class="btn-saas-secondary w-100 justify-content-center" target="_blank">
                        <i class="bi bi-file-earmark-text"></i> Full Document Preview
                    </a>
                    <a href="{{ route('cvs.index') }}" class="btn-saas-secondary w-100 justify-content-center">
                        <i class="bi bi-grid-1x2"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
