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
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
        <a href="{{ route('audit.export', request()->only(['action','causer','from','to'])) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-download me-1"></i> CSV</a>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Time</th><th>Action</th><th>Causer</th><th>Subject</th><th>IP</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($activities as $log)
                <tr>
                    <td class="text-muted">{{ $activities->firstItem() + $loop->index }}</td>
                    <td class="text-muted small">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        @php
                            $actionColor = [
                                'role_created' => 'success', 'permission_created' => 'success', 'user_created' => 'success', 'login_success' => 'success',
                                'role_updated' => 'warning', 'permission_updated' => 'warning', 'user_updated' => 'warning',
                                'role_deleted' => 'danger', 'permission_deleted' => 'danger', 'user_deleted' => 'danger', 'login_failed' => 'danger',
                                'user_restored' => 'info',
                                'logout' => 'secondary', 'password_reset' => 'secondary', 'email_verified' => 'secondary',
                            ];
                            $c = $actionColor[$log->description] ?? 'dark';
                        @endphp
                        <span class="badge text-bg-{{ $c }}">{{ $log->description }}</span>
                    </td>
                    <td>{{ $log->causer->username ?? ($log->properties['identifier'] ?? '-') }}</td>
                    <td>
                        <span class="text-muted small">{{ class_basename($log->subject_type) }}</span>
                        @if ($log->subject)
                            <span class="fw-semibold text-body">{{ $log->subject->name ?? $log->subject->username ?? '' }}</span>
                        @endif
                        <span class="text-muted">#{{ $log->subject_id }}</span>
                    </td>
                    <td class="text-muted small">{{ $log->properties['ip'] ?? '-' }}</td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-light border rounded-2 audit-detail-btn"
                                data-bs-toggle="modal" data-bs-target="#auditDetailModal"
                                data-action="{{ $log->description }}"
                                data-time="{{ $log->created_at->format('Y-m-d H:i:s') }}"
                                data-causer="{{ $log->causer->username ?? ($log->properties['identifier'] ?? '-') }}"
                                data-ip="{{ $log->properties['ip'] ?? '' }}"
                                data-agent="{{ $log->properties['user_agent'] ?? '' }}">
                            <i class="bi bi-eye"></i>
                        </button>
                        <script type="application/json" class="audit-detail-data">@json([
                            'old' => $log->properties['old'] ?? new \stdClass(),
                            'new' => $log->properties['new'] ?? new \stdClass(),
                        ])</script>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No activity.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@include('partials.pagination-info', ['items' => $activities])
{{ $activities->links() }}

{{-- Detail modal --}}
<div class="modal fade" id="auditDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="auditDetailAction"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body small">
                <dl class="row mb-2">
                    <dt class="col-4 text-muted">Time</dt><dd class="col-8" id="auditDetailTime"></dd>
                    <dt class="col-4 text-muted">Causer</dt><dd class="col-8" id="auditDetailCauser"></dd>
                    <dt class="col-4 text-muted">IP</dt><dd class="col-8" id="auditDetailIp"></dd>
                    <dt class="col-4 text-muted">User agent</dt><dd class="col-8 text-break" id="auditDetailAgent"></dd>
                </dl>
                <div id="auditDetailChanges"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('auditDetailModal')?.addEventListener('show.bs.modal', function (e) {
    const b = e.relatedTarget;
    document.getElementById('auditDetailAction').textContent = b.dataset.action;
    document.getElementById('auditDetailTime').textContent = b.dataset.time;
    document.getElementById('auditDetailCauser').textContent = b.dataset.causer;
    document.getElementById('auditDetailIp').textContent = b.dataset.ip || '-';
    document.getElementById('auditDetailAgent').textContent = b.dataset.agent || '-';

    const changes = JSON.parse(b.parentElement.querySelector('.audit-detail-data')?.textContent || '{}');
    const oldMap = changes.old ?? {};
    const newMap = changes.new ?? {};
    const keys = [...new Set([...Object.keys(oldMap), ...Object.keys(newMap)])];
    const box = document.getElementById('auditDetailChanges');
    if (!keys.length) { box.innerHTML = '<p class="text-muted mb-0">No field changes recorded.</p>'; return; }

    const cell = (v) => v === null ? '<em class="text-muted">null</em>' : String(v);
    let html = '<table class="table table-sm mb-0"><thead><tr><th>Field</th><th>Old</th><th>New</th></tr></thead><tbody>';
    keys.forEach(k => {
        html += `<tr><td class="text-capitalize text-muted">${k}</td><td class="text-break">${cell(oldMap[k])}</td><td class="text-break">${cell(newMap[k])}</td></tr>`;
    });
    box.innerHTML = html + '</tbody></table>';
});
</script>
@endpush
