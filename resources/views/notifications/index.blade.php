@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<h3>Notifications</h3>
<p class="text-muted">Your recent account and admin activity.</p>

<div class="card">
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @forelse ($notifications as $n)
                <div class="list-group-item {{ $n->read_at ? '' : 'list-group-item-primary' }}">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div><i class="bi bi-activity text-primary me-1"></i>{{ $n->data['action'] ?? 'notification' }}</div>
                            <div class="text-muted small">
                                @if (!empty($n->data['ip']))
                                    · {{ $n->data['ip'] }}
                                @endif
                                @if ($n->read_at)
                                    · read {{ $n->read_at->diffForHumans() }}
                                @else
                                    · unread
                                @endif
                            </div>
                        </div>
                        <span class="text-muted small text-nowrap">{{ $n->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <div class="list-group-item text-muted">No notifications yet.</div>
            @endforelse
        </div>
    </div>
</div>
{{ $notifications->links() }}
@endsection
