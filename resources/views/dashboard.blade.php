@extends('layouts.app')
@section('content')
<div class="row g-3 mb-3">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="rounded-circle d-flex align-items-center justify-content-center text-bg-primary" style="width:48px;height:48px"><i class="bi bi-people fs-4"></i></span>
                <div><div class="text-muted small">Users</div><div class="fs-4 fw-semibold">{{ $userCount ?? 0 }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="rounded-circle d-flex align-items-center justify-content-center text-bg-success" style="width:48px;height:48px"><i class="bi bi-shield fs-4"></i></span>
                <div><div class="text-muted small">Roles</div><div class="fs-4 fw-semibold">{{ $roleCount ?? 0 }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="rounded-circle d-flex align-items-center justify-content-center text-bg-warning" style="width:48px;height:48px"><i class="bi bi-journal-text fs-4"></i></span>
                <div><div class="text-muted small">Audit Entries</div><div class="fs-4 fw-semibold">{{ $auditCount ?? 0 }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="rounded-circle d-flex align-items-center justify-content-center text-bg-info" style="width:48px;height:48px"><i class="bi bi-hdd-network fs-4"></i></span>
                <div><div class="text-muted small">Database</div><div class="fs-6 fw-semibold">{{ $dbName ?? 'n/a' }}</div></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <h5 class="mb-1">Welcome, {{ auth()->user()->name }} 👋</h5>
        <p class="text-muted mb-0">Laravel Base Project scaffold — manage users, roles, and monitor activity from the sidebar.</p>
    </div>
</div>
@endsection
