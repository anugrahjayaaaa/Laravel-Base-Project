@extends('layouts.auth')

@section('content')
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center"><span class="h1">{{ config('app.name', 'Laravel') }}</span></div>
        <div class="card-body">
            <p class="login-box-msg">Forgot your password? Enter your email and we'll send a reset link.</p>

            @if ($errors->any())
                <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
            @endif

            @if (session('status'))
                <div class="alert alert-success py-2">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}" required autofocus>
                    <div class="input-group-text"><i class="bi bi-envelope"></i></div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">Send reset link</button>
                    </div>
                </div>
            </form>

            <p class="mb-0 mt-2"><a href="{{ route('login') }}">Back to login</a></p>
        </div>
    </div>
</div>
@endsection
