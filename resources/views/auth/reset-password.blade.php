@extends('layouts.auth')

@section('content')
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center"><span class="h1">{{ config('app.name', 'Laravel') }}</span></div>
        <div class="card-body">
            <p class="login-box-msg">{{ ui('reset_your_password') }}</p>

            @if ($errors->any())
                <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="input-group mb-3">
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" placeholder="{{ ui('email') }}" value="{{ old('email', $email) }}" required autofocus aria-describedby="email-error" @error('email') aria-invalid="true" @enderror>
                    <label for="email" class="sr-only">{{ ui('email') }}</label>
                    <div class="input-group-text"><i class="bi bi-envelope"></i></div>
                    @error('email')<div id="email-error" class="invalid-feedback d-block w-100" role="alert" aria-live="polite">{{ $message }}</div>@enderror
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ ui('new_password') }}" required aria-describedby="password-error" @error('password') aria-invalid="true" @enderror>
                    <label for="password" class="sr-only">{{ ui('new_password') }}</label>
                    <div class="input-group-text"><i class="bi bi-lock"></i></div>
                    @error('password')<div id="password-error" class="invalid-feedback d-block w-100" role="alert" aria-live="polite">{{ $message }}</div>@enderror
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" placeholder="{{ ui('confirm_password') }}" required aria-describedby="password_confirmation-error" @error('password_confirmation') aria-invalid="true" @enderror>
                    <label for="password_confirmation" class="sr-only">{{ ui('confirm_password') }}</label>
                    <div class="input-group-text"><i class="bi bi-lock"></i></div>
                    @error('password_confirmation')<div id="password_confirmation-error" class="invalid-feedback d-block w-100" role="alert" aria-live="polite">{{ $message }}</div>@enderror
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">{{ ui('reset_password') }}</button>
                    </div>
                </div>
            </form>

            <p class="mb-0 mt-2"><a href="{{ route('login') }}">{{ ui('back_to_login') }}</a></p>
        </div>
    </div>
</div>
@endsection
