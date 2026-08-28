@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Users</h3>
    @can('user.create')
    <a href="{{ route('users.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> New User</a>
    @endcan
</div>

<form method="GET" class="mb-3">
    <div class="input-group input-group-sm shadow-sm" style="max-width:380px">
        <span class="input-group-text bg-body border-0"><i class="bi bi-search"></i></span>
        <input type="text" name="q" class="form-control bg-body border-0" placeholder="Search name, username, email…" value="{{ request('q') }}">
        <button class="btn btn-primary px-3" type="submit">Search</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle m-0">
            <thead>
                <tr><th>#</th><th>User</th><th>Username</th><th>Email</th><th>Roles</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                <tr class="{{ $user->trashed() ? 'table-secondary' : '' }}">
                    <td class="text-muted">{{ $users->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:32px;height:32px">{{ strtoupper(substr($user->name,0,1)) }}</span>
                            <div>
                                <div class="fw-medium">{{ $user->name }}</div>
                                @if($user->trashed())<span class="badge text-bg-danger">deleted</span>@endif
                            </div>
                        </div>
                    </td>
                    <td>{{ $user->username }}</td>
                    <td class="text-muted">{{ $user->email }}</td>
                    <td>
                        @foreach ($user->roles as $role)
                            <span class="badge text-bg-info me-1">{{ $role->name }}</span>
                        @endforeach
                        @if ($user->roles->isEmpty())<span class="text-muted">-</span>@endif
                    </td>
                    <td>
                        @if ($user->isLocked())
                            <span class="badge text-bg-warning">locked</span>
                        @else
                            <span class="badge text-bg-success">active</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                            <x-action-buttons
                                :edit="!$user->trashed() ? route('users.edit', $user) : null"
                                :restore="$user->trashed() && auth()->user()->can('user.restore') ? route('users.restore', $user->id) : null"
                                :delete="!$user->trashed() && $user->id !== auth()->id() ? route('users.destroy', $user) : null"
                                :forceDelete="$user->trashed() && auth()->user()->can('user.force-delete') ? route('users.forceDelete', $user->id) : null" />
                            @if (!$user->trashed() && $user->id !== auth()->id() && auth()->user()->can('user.edit'))
                            <form method="POST" action="{{ route('users.reset-link', $user) }}" class="d-inline">@csrf
                                <button type="submit" class="btn btn-sm btn-light border rounded-2" data-bs-toggle="tooltip" data-bs-title="Send reset link" aria-label="Send reset link" style="min-width:38px">
                                    <i class="bi bi-envelope"></i>
                                </button>
                            </form>
                            @if ($user->isLocked())
                            <form method="POST" action="{{ route('users.unlock', $user) }}" class="d-inline">@csrf
                                <button type="submit" class="btn btn-sm btn-light border rounded-2 text-warning" data-bs-toggle="tooltip" data-bs-title="Unlock account" aria-label="Unlock account" style="min-width:38px">
                                    <i class="bi bi-unlock-fill"></i>
                                </button>
                            </form>
                            @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@include('partials.pagination-info', ['items' => $users])
{{ $users->links() }}
@include('partials.delete-modal')
@include('partials.force-delete-modal')
@endsection
