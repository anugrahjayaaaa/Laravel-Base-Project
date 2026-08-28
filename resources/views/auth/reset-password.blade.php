@extends('layouts.auth')

@section('content')
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center"><span class="h1">{{ config('app.name', 'Laravel') }}</span></div>
        <div class="card-body">
            <p class="login-box-msg">Reset your password</p>

            @if ($errors->any())
                <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email', $email) }}" required autofocus>
                    <div class="input-group-text"><i class="bi bi-envelope"></i></div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="New password" required>
                    <div class="input-group-text"><i class="bi bi-lock"></i></div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm password" required>
                    <div class="input-group-text"><i class="bi bi-lock"></i></div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">Reset password</button>
                    </div>
                </div>
            </form>

            <p class="mb-0 mt-2"><a href="{{ route('login') }}">Back to login</a></p>
        </div>
    </div>
</div>
@endsection
