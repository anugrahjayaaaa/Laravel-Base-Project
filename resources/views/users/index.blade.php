@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Users</h3>
    <a href="{{ route('users.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New User</a>
</div>
<form method="GET" class="mb-3">
    <div class="input-group" style="max-width:360px">
        <input type="text" name="q" class="form-control" placeholder="Search name/username/email" value="{{ request('q') }}">
        <button class="btn btn-outline-secondary">Search</button>
    </div>
</form>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>#</th><th>Name</th><th>Username</th><th>Email</th><th>Roles</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse ($users as $user)
                <tr class="{{ $user->trashed() ? 'table-secondary' : '' }}">
                    <td>{{ $users->firstItem() + $loop->index }}</td>
                    <td>{{ $user->name }} @if($user->trashed())<span class="badge bg-danger">deleted</span>@endif</td>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</td>
                    <td class="text-end">
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        @if (!$user->trashed())
                            @if ($user->id !== auth()->id())
                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-action="{{ route('users.destroy', $user) }}"><i class="bi bi-trash"></i></button>
                            @endif
                        @else
                            <a href="{{ route('users.restore', $user->id) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-counterclockwise"></i></a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">No users.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('partials.pagination-info', ['items' => $users])
{{ $users->links() }}
@include('partials.delete-modal')
@endsection
