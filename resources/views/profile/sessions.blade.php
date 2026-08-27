@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<h3>Active Sessions</h3>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th>IP</th><th>User Agent</th><th>Last Activity</th><th class="text-end">Status</th></tr></thead>
            <tbody>
                @forelse ($sessions as $s)
                <tr>
                    <td>{{ $s->ip_address }}</td>
                    <td class="text-muted">{{ \Illuminate\Support\Str::limit($s->user_agent, 60) }}</td>
                    <td class="text-muted small">{{ \Carbon\Carbon::createFromTimestamp($s->last_activity)->toDateTimeString() }}</td>
                    <td class="text-end">
                        @if ($s->id === session()->getId())
                            <span class="badge bg-success-subtle text-success border border-success-subtle">This device</span>
                        @else
                            <span class="badge bg-light border text-muted">Other</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No sessions.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
<form method="POST" action="{{ route('sessions.logoutOthers') }}" class="mt-3" style="max-width:420px">
    @csrf
    <div class="input-group input-group-sm shadow-sm">
        <input type="password" name="password" class="form-control bg-body border-0" placeholder="Confirm password to log out other devices" required>
        <button class="btn btn-danger px-3"><i class="bi bi-box-arrow-right me-1"></i> Logout others</button>
    </div>
</form>
@endsection
