@extends('layouts.auth')

@section('content')
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center"><span class="h1">{{ config('app.name', 'Laravel') }}</span></div>
        <div class="card-body">
            <p class="login-box-msg">Sign in with email or username</p>

            @if ($errors->any())
                <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
            @endif

            @if (session('status'))
                <div class="alert alert-success py-2">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="input-group mb-3">
                    <input type="text" name="identifier" class="form-control" placeholder="Email or Username" value="{{ old('identifier') }}" required autofocus>
                    <div class="input-group-text"><i class="bi bi-person"></i></div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                    <button type="button" class="input-group-text" id="toggle-password" aria-label="Show password" style="cursor:pointer">
                        <i class="bi bi-eye" id="password-icon"></i>
                    </button>
                </div>
                <div class="row">
                    <div class="col-8">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary w-100">Sign in</button>
                    </div>
                </div>
            </form>

            <p class="mb-0 mt-2">
                <a href="{{ route('password.request') }}">Forgot your password?</a>
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const pwd = document.getElementById('password');
        const btn = document.getElementById('toggle-password');
        const icon = document.getElementById('password-icon');
        btn.addEventListener('click', function () {
            const show = pwd.type === 'password';
            pwd.type = show ? 'text' : 'password';
            icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    })();
</script>
@endpush
@endsection
