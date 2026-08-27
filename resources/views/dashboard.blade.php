@extends('layouts.app')
@section('content')
<div class="row">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon text-bg-primary shadow-sm"><i class="bi bi-people"></i></span>
            <div class="info-box-content"><span class="info-box-text">Users</span><span class="info-box-number">{{ $userCount ?? 0 }}</span></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon text-bg-success shadow-sm"><i class="bi bi-shield"></i></span>
            <div class="info-box-content"><span class="info-box-text">Roles</span><span class="info-box-number">{{ $roleCount ?? 0 }}</span></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon text-bg-warning shadow-sm"><i class="bi bi-journal-text"></i></span>
            <div class="info-box-content"><span class="info-box-text">Audit Entries</span><span class="info-box-number">{{ $auditCount ?? 0 }}</span></div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon text-bg-info shadow-sm"><i class="bi bi-hdd-network"></i></span>
            <div class="info-box-content"><span class="info-box-text">DB</span><span class="info-box-number">{{ $dbName ?? 'n/a' }}</span></div>
        </div>
    </div>
</div>
<div class="card"><div class="card-body">Welcome to the Laravel Base Project scaffold.</div></div>
@endsection
