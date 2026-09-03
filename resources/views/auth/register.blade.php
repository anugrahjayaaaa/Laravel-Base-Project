@extends('layouts.auth')

@section('content')
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center"><span class="h1">{{ config('app.name', 'Laravel') }}</span></div>
        <div class="card-body">
            <p class="login-box-msg">{{ ui('sign_up_to_account') }}</p>

            @if ($errors->any())
                <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('register.store') }}">
                @csrf
                <div class="input-group mb-3">
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           placeholder="{{ ui('full_name') }}" value="{{ old('name') }}" required autofocus>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="input-group-text"><i class="bi bi-person"></i></div>
                </div>

                <div class="input-group mb-3">
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                           placeholder="{{ ui('username') }}" value="{{ old('username') }}" required>
                    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="input-group-text"><i class="bi bi-person-badge"></i></div>
                </div>

                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           placeholder="{{ ui('email') }}" value="{{ old('email') }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="input-group-text"><i class="bi bi-envelope"></i></div>
                </div>

                <div class="input-group mb-3">
                    <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror"
                           placeholder="{{ ui('phone_optional') }}" value="{{ old('phone') }}">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="input-group-text"><i class="bi bi-telephone"></i></div>
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
                           placeholder="{{ ui('password') }}" required>
                    <button type="button" class="input-group-text" id="toggle-password" aria-label="{{ ui('show_password') }}" style="cursor:pointer">
                        <i class="bi bi-eye" id="password-icon"></i>
                    </button>
                </div>
                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                <div class="input-group mb-3">
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="form-control @error('password_confirmation') is-invalid @enderror"
                           placeholder="{{ ui('confirm_password') }}" required>
                    <button type="button" class="input-group-text" id="toggle-password-confirm" aria-label="{{ ui('show_password') }}" style="cursor:pointer">
                        <i class="bi bi-eye" id="password-confirm-icon"></i>
                    </button>
                </div>
                @error('password_confirmation')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">{{ ui('sign_up') }}</button>
                    </div>
                </div>
            </form>

            <p class="mb-0 mt-2">
                <a href="{{ route('login') }}">{{ ui('already_have_account') }}</a>
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Toggle password visibility (shared logic — same as login page)
    (function () {
        for (const [field, btn, icon] of [
            ['password', 'toggle-password', 'password-icon'],
            ['password_confirmation', 'toggle-password-confirm', 'password-confirm-icon']
        ]) {
            const pwd = document.getElementById(field);
            const b = document.getElementById(btn);
            const i = document.getElementById(icon);
            b.addEventListener('click', function () {
                const show = pwd.type === 'password';
                pwd.type = show ? 'text' : 'password';
                i.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        }
    })();
</script>
@endpush
@endsection
