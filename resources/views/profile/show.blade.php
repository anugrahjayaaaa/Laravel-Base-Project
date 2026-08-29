@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">{{ ui('profile') }}</div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">{{ ui('name') }}</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ ui('username') }}</label>
                        <input type="text" class="form-control" value="{{ $user->username }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ ui('email') }}</label>
                        <input type="text" class="form-control" value="{{ $user->email }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ ui('phone') }}</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                    </div>
                    <button class="btn btn-primary">{{ ui('update_profile') }}</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">{{ ui('change_password') }}</div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">{{ ui('current_password') }}</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ ui('new_password_hint') }}</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ ui('confirm') }}</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <button class="btn btn-warning">{{ ui('change_password') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
