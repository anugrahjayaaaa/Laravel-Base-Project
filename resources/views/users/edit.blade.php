@extends('layouts.app')
@section('content')
<h3>{{ isset($user) ? 'Edit User' : 'New User' }}</h3>

{{-- Account status / admin actions: placed OUTSIDE the main update form to avoid nested-form submit --}}
@if (isset($user) && !$user->trashed() && $user->id !== auth()->id() && (auth()->user()->can('user.lock') || auth()->user()->can('user.edit')))
    <div class="alert alert-light d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <span>Status:
                @if ($user->isPermanentlyLocked())
                    <span class="badge text-bg-danger">perm locked</span>
                @elseif ($user->isLocked())
                    <span class="badge text-bg-warning">locked</span>
                @else
                    <span class="badge text-bg-success">active</span>
                @endif
            </span>
            @if ($user->last_login_at)
                <span class="text-muted small">Last login: {{ $user->last_login_at->format('Y-m-d H:i') }} ({{ $user->last_login_ip }})</span>
            @endif
            @if (auth()->user()->can('user.lock'))
                @if ($user->isLocked())
                    <span>Unlock:
                        <form method="POST" action="{{ route('users.unlock', $user) }}" class="d-inline">@csrf
                            <button class="btn btn-sm btn-warning">Unlock</button>
                        </form>
                    </span>
                @else
                    <span>Lock:
                        <form method="POST" action="{{ route('users.lock', $user) }}" class="d-inline">@csrf
                            <button class="btn btn-sm btn-danger">Lock</button>
                        </form>
                    </span>
                @endif
            @endif
        </div>
        @if (auth()->user()->can('user.edit'))
        <span>Send reset email:
            <form method="POST" action="{{ route('users.reset-link', $user) }}" class="d-inline">@csrf
                <button class="btn btn-sm btn-secondary">Send reset email</button>
            </form>
        </span>
        @endif
    </div>
@endif

<form method="POST" action="{{ isset($user) ? route('users.update', $user) : route('users.store') }}">
    @csrf
    @if (isset($user)) @method('PUT') @endif
    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="{{ old('username', $user->username ?? '') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone (E.164)</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password {{ isset($user) ? '(leave blank to keep)' : '' }}</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ isset($user) ? '' : 'required' }}>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror">
                    @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Roles</label>
                    <div class="row">
                        @foreach ($roles as $role)
                        <div class="col-6 col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}"
                                    {{ (isset($user) && $user->roles->contains($role->id)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role_{{ $role->id }}">{{ $role->name }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('users.index') }}" class="btn btn-link">Cancel</a>
            <button class="btn btn-primary">Save</button>
        </div>
    </div>
</form>
@endsection
