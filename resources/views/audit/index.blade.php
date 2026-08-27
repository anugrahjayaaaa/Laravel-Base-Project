@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<h3>Audit Log</h3>
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="action" class="form-select">
            <option value="">All actions</option>
            @foreach ($actions as $a)
                <option value="{{ $a }}" {{ request('action') == $a ? 'selected' : '' }}>{{ $a }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3"><input type="date" name="from" class="form-control" value="{{ request('from') }}" placeholder="From"></div>
    <div class="col-md-3"><input type="date" name="to" class="form-control" value="{{ request('to') }}" placeholder="To"></div>
    <div class="col-md-3"><button class="btn btn-primary w-100">Filter</button></div>
</form>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>#</th><th>Time</th><th>Action</th><th>Causer</th><th>Subject</th><th>IP</th></tr></thead>
            <tbody>
                @forelse ($activities as $log)
                <tr>
                    <td>{{ $activities->firstItem() + $loop->index }}</td>
                    <td>{{ $log->created_at }}</td>
                    <td><span class="badge bg-secondary">{{ $log->description }}</span></td>
                    <td>{{ $log->causer->username ?? ($log->properties['identifier'] ?? '-') }}</td>
                    <td>{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</td>
                    <td>{{ $log->properties['ip'] ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">No activity.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('partials.pagination-info', ['items' => $activities])
{{ $activities->links() }}
@endsection
