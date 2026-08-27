@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<h3>Audit Log</h3>
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="action" class="form-select form-select-sm">
            <option value="">All actions</option>
            @foreach ($actions as $a)
                <option value="{{ $a }}" {{ request('action') == $a ? 'selected' : '' }}>{{ $a }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3"><input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" placeholder="From"></div>
    <div class="col-md-3"><input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}" placeholder="To"></div>
    <div class="col-md-3"><button class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel me-1"></i> Filter</button></div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Time</th><th>Action</th><th>Causer</th><th>Subject</th><th>IP</th></tr>
            </thead>
            <tbody>
                @forelse ($activities as $log)
                <tr>
                    <td class="text-muted">{{ $activities->firstItem() + $loop->index }}</td>
                    <td class="text-muted small">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        @php $c = \Illuminate\Support\Str::contains($log->description, ['delete','removed']) ? 'danger' : (\Illuminate\Support\Str::contains($log->description, ['create','login','added']) ? 'success' : 'secondary'); @endphp
                        <span class="badge bg-{{ $c }}-subtle text-{{ $c }} border border-{{ $c }}-subtle">{{ $log->description }}</span>
                    </td>
                    <td>{{ $log->causer->username ?? ($log->properties['identifier'] ?? '-') }}</td>
                    <td class="text-muted">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</td>
                    <td class="text-muted small">{{ $log->properties['ip'] ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No activity.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@include('partials.pagination-info', ['items' => $activities])
{{ $activities->links() }}
@endsection
