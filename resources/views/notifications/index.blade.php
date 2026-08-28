@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<h3>Notifications</h3>
<p class="text-muted">Recent account and admin activity. (Mirrors the audit feed; no read/unread state.)</p>

<div class="card">
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @forelse ($activities as $a)
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div><i class="bi bi-activity text-primary me-1"></i>{{ $a->description }}</div>
                            <div class="text-muted small">
                                @if ($a->causer)
                                    by {{ $a->causer->name ?? $a->causer->email }}
                                @endif
                                @if (!empty($a->properties['ip']))
                                    · {{ $a->properties['ip'] }}
                                @endif
                            </div>
                        </div>
                        <span class="text-muted small text-nowrap">{{ $a->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <div class="list-group-item text-muted">No activity yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
