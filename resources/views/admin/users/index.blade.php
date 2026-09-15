@extends('layouts.admin')

@section('title', 'Users Management - CV Maker')
@section('page-title', 'Users Directory')

@section('content')
<div class="card card-saas mb-4 p-3">
    <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-center">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name or email..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="role" class="form-select" onchange="this.form.submit()">
                <option value="">All Roles</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admins Only</option>
                <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Users Only</option>
            </select>
        </div>
        <div class="col-6 col-md-3 d-flex gap-2">
            <button type="submit" class="btn-saas-secondary w-100 justify-content-center">
                Filter
            </button>
            @if(request('search') || request('role'))
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center" title="Clear Filters">
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
                        <th class="ps-4">User</th>
                        <th>Role</th>
                        <th>CVs Created</th>
                        <th>Registered Date</th>
                        <th class="pe-4 text-end">Role Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-person-circle fs-5 text-secondary"></i>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $u->name }}</div>
                                        <div class="small text-muted">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($u->isAdmin())
                                    <span class="badge-saas-role-admin">Administrator</span>
                                @else
                                    <span class="badge-saas-role-user">Standard User</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $u->cvs_count }}</span>
                            </td>
                            <td class="small text-muted">
                                {{ $u->created_at->format('M d, Y') }}
                            </td>
                            <td class="pe-4 text-end">
                                @if($u->id !== auth()->id())
                                    <form action="{{ route('admin.users.toggle-role', $u) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-saas-secondary py-1 px-2" title="Toggle Role">
                                            <i class="bi bi-arrow-repeat me-1"></i> Make {{ $u->isAdmin() ? 'User' : 'Admin' }}
                                        </button>
                                    </form>
                                @else
                                    <span class="small text-muted fst-italic">Current Session</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                No users matched your search criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $users->links('pagination::bootstrap-5') }}
</div>
@endsection
