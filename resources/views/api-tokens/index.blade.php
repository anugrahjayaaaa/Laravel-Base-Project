@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<h3>API Tokens</h3>

@if($newToken)
<div class="alert alert-success">
    <strong>New token (copy now):</strong>
    <code class="d-block mt-1">{{ $newToken }}</code>
</div>
@endif

<form method="POST" action="{{ route('api-tokens.store') }}" class="row g-2 mb-3">
    @csrf
    <div class="col-md-4">
        <input type="text" name="name" class="form-control" placeholder="Token name (e.g. mobile-iphone)" required>
    </div>
    <div class="col-auto">
        <button class="btn btn-primary"><i class="bi bi-plus-circle"></i> Create</button>
    </div>
</form>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>Name</th><th>Abilities</th><th>Created</th><th>Last used</th><th></th></tr></thead>
            <tbody>
            @forelse($tokens as $token)
                <tr>
                    <td>{{ $token->name }}</td>
                    <td>{{ implode(', ', $token->abilities) }}</td>
                    <td>{{ $token->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td>{{ $token->last_used_at?->diffForHumans() ?? 'never' }}</td>
                    <td>
                        <form method="POST" action="{{ route('api-tokens.destroy', $token) }}" onsubmit="return confirm('Revoke this token?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted">No tokens yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
