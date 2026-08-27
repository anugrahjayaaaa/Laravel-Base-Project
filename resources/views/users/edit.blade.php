@extends('layouts.app')
@section('content')
<h3>{{ isset($user) ? 'Edit User' : 'New User' }}</h3>
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
                    <input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required' }}>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
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
        <div class="card-footer">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('users.index') }}" class="btn btn-link">Cancel</a>
        </div>
    </div>
</form>
@endsection
