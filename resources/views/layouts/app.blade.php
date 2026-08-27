<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/css/adminlte.min.css') }}">
</head>
<body class="layout-fixed sidebar-open">
<div class="app-wrapper">
    {{-- Header --}}
    <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="#" data-lte-toggle="sidebar" role="button">
                        <i class="bi bi-list"></i>
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <button class="btn btn-link nav-link" id="theme-toggle" type="button" title="Toggle theme">
                        <i class="bi bi-circle-half"></i>
                    </button>
                </li>
                @auth
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-link nav-link" type="submit"><i class="bi bi-box-arrow-right"></i> Logout</button>
                    </form>
                </li>
                @endauth
            </ul>
        </div>
    </nav>

    {{-- Sidebar --}}
    <aside class="app-sidebar sidebar bg-body-secondary" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="{{ url('/') }}" class="brand-link">
                <i class="bi bi-hexagon-fill brand-image"></i>
                <span class="brand-text fw-light">{{ config('app.name', 'Laravel') }}</span>
            </a>
        </div>
        <div class="sidebar-wrapper">
            <nav class="mt-3 sidebar-nav">
                <ul class="nav flex-column">
                    <li class="nav-header">MAIN MENU</li>
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link">
                            <i class="nav-icon bi bi-speedometer2"></i> <span>Dashboard</span>
                        </a>
                    </li>
                    @can('user.view')
                    <li class="nav-item"><a href="{{ route('users.index') }}" class="nav-link"><i class="nav-icon bi bi-people"></i> <span>Users</span></a></li>
                    @endcan
                    @can('role.view')
                    <li class="nav-item"><a href="{{ route('roles.index') }}" class="nav-link"><i class="nav-icon bi bi-shield"></i> <span>Roles</span></a></li>
                    @endcan
                    @can('permission.view')
                    <li class="nav-item"><a href="{{ route('permissions.index') }}" class="nav-link"><i class="nav-icon bi bi-key"></i> <span>Permissions</span></a></li>
                    @endcan
                    @can('audit.view')
                    <li class="nav-item"><a href="{{ route('audit.index') }}" class="nav-link"><i class="nav-icon bi bi-journal-text"></i> <span>Audit Log</span></a></li>
                    @endcan
                    <li class="nav-item"><a href="{{ route('profile.show') }}" class="nav-link"><i class="nav-icon bi bi-person"></i> <span>Profile</span></a></li>
                    @can('session.view')
                    <li class="nav-item"><a href="{{ route('sessions.index') }}" class="nav-link"><i class="nav-icon bi bi-pc-display"></i> <span>Sessions</span></a></li>
                    @endcan
                    @can('api-token.view')
                    <li class="nav-item"><a href="{{ route('api-tokens.index') }}" class="nav-link"><i class="nav-icon bi bi-hdd-network"></i> <span>API Tokens</span></a></li>
                    @endcan

                    <li class="nav-header">TEMPLATE</li>
                    <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/UI/general.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-card-list"></i> <span>Components</span></a></li>
                    <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/forms/general.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-ui-checks"></i> <span>Forms</span></a></li>
                    <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/tables/general.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-table"></i> <span>Tables</span></a></li>
                    <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/charts/chartjs.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-bar-chart"></i> <span>Charts</span></a></li>
                </ul>
            </nav>
        </div>
    </aside>

    {{-- Main --}}
    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6"><h3 class="mb-0">{{ $title ?? '' }}</h3></div>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </main>

    <footer class="app-footer">
        <div class="ms-auto">Laravel Base Project</div>
    </footer>
</div>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/js/adminlte.min.js') }}"></script>
<script>
    // ponytail: localStorage theme only; DB-per-user wiring added in profile phase
    (function () {
        const saved = localStorage.getItem('theme');
        if (saved) document.documentElement.setAttribute('data-bs-theme', saved);
        document.getElementById('theme-toggle').addEventListener('click', function () {
            const cur = document.documentElement.getAttribute('data-bs-theme');
            const next = cur === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', next);
            localStorage.setItem('theme', next);
        });
    })();
</script>
@stack('scripts')
</body>
</html>
