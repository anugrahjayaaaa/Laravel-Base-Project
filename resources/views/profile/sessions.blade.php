@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<h3>Active Sessions</h3>
<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>IP</th><th>User Agent</th><th>Last Activity</th><th class="text-end">Current</th></tr></thead>
            <tbody>
                @forelse ($sessions as $s)
                <tr>
                    <td>{{ $s->ip_address }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($s->user_agent, 60) }}</td>
                    <td>{{ \Carbon\Carbon::createFromTimestamp($s->last_activity)->toDateTimeString() }}</td>
                    <td class="text-end">{{ $s->id === session()->getId() ? '✓' : '' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted">No sessions.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<form method="POST" action="{{ route('sessions.logoutOthers') }}" class="mt-3" style="max-width:360px">
    @csrf
    <div class="input-group">
        <input type="password" name="password" class="form-control" placeholder="Password to confirm" required>
        <button class="btn btn-danger">Log out other devices</button>
    </div>
</form>
@endsection
