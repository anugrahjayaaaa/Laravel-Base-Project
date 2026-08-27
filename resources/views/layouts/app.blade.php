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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('vendor/app-theme.css') }}">
</head>
<body class="layout-fixed sidebar-open">
<div class="app-wrapper">

    {{-- Header --}}
    <nav class="app-header navbar navbar-expand bg-body border-bottom">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="#" data-lte-toggle="sidebar" role="button" title="Toggle sidebar">
                        <i class="bi bi-list fs-4"></i>
                    </a>
                </li>
            </ul>

            {{-- Global search --}}
            <ul class="navbar-nav ms-auto flex-grow-1 px-2 d-none d-md-flex">
                <li class="nav-item w-100" style="max-width:420px">
                    <form action="{{ route('users.index') }}" method="GET" class="d-flex">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-body border-0"><i class="bi bi-search"></i></span>
                            <input type="search" name="q" class="form-control bg-body border-0" placeholder="Search users, roles…" value="{{ request('q') }}">
                        </div>
                    </form>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">
                {{-- Theme toggle --}}
                <li class="nav-item">
                    <button class="btn btn-link nav-link" id="theme-toggle" type="button" title="Toggle light/dark">
                        <i class="bi bi-moon-stars" id="theme-icon"></i>
                    </button>
                </li>

                {{-- Notifications --}}
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown" title="Notifications">
                        <i class="bi bi-bell fs-5"></i>
                        @if (($notifications['unread'] ?? 0) > 0)
                            <span class="badge text-bg-danger position-absolute top-0 start-100 translate-middle-y rounded-pill">{{ $notifications['unread'] }}</span>
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-lg">
                        <span class="dropdown-item dropdown-header">Recent activity ({{ $notifications['items']->count() }})</span>
                        <div class="dropdown-divider"></div>
                        @forelse ($notifications['items'] as $n)
                            <a href="{{ route('audit.index') }}" class="dropdown-item">
                                <i class="bi bi-activity text-secondary me-2"></i> {{ \Illuminate\Support\Str::limit($n->description, 28) }}
                                <span class="text-muted float-end small">{{ $n->created_at->diffForHumans() }}</span>
                            </a>
                        @empty
                            <span class="dropdown-item text-muted">No activity yet.</span>
                        @endforelse
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('audit.index') }}" class="dropdown-item dropdown-footer">View all audit log</a>
                    </div>
                </li>

                {{-- User menu --}}
                @auth
                <li class="nav-item dropdown">
                    <a class="nav-link d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <span class="avatar avatar-sm rounded-circle bg-primary text-white me-2 d-flex align-items-center justify-content-center" style="width:32px;height:32px">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                        <i class="bi bi-chevron-down ms-1"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="{{ route('profile.show') }}" class="dropdown-item"><i class="bi bi-person me-2"></i> Profile</a>
                        <a href="{{ route('sessions.index') }}" class="dropdown-item"><i class="bi bi-pc-display me-2"></i> Sessions</a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                        </form>
                    </div>
                </li>
                @endauth
            </ul>
        </div>
    </nav>

    {{-- Sidebar --}}
    <aside class="app-sidebar sidebar bg-body-secondary" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="{{ url('/') }}" class="brand-link">
                <i class="bi bi-hexagon-fill brand-image text-primary"></i>
                <span class="brand-text fw-light">{{ config('app.name', 'Laravel') }}</span>
            </a>
        </div>
        <div class="sidebar-wrapper">
            <nav class="mt-3 sidebar-nav">
                <ul class="nav flex-column">
                    <li class="nav-header">MAIN MENU</li>
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-speedometer2"></i> <span>Dashboard</span>
                        </a>
                    </li>
                    @can('user.view')
                    <li class="nav-item"><a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="nav-icon bi bi-people"></i> <span>Users</span></a></li>
                    @endcan
                    @can('role.view')
                    <li class="nav-item"><a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"><i class="nav-icon bi bi-shield"></i> <span>Roles</span></a></li>
                    @endcan
                    @can('permission.view')
                    <li class="nav-item"><a href="{{ route('permissions.index') }}" class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}"><i class="nav-icon bi bi-key"></i> <span>Permissions</span></a></li>
                    @endcan
                    @can('audit.view')
                    <li class="nav-item"><a href="{{ route('audit.index') }}" class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}"><i class="nav-icon bi bi-journal-text"></i> <span>Audit Log</span></a></li>
                    @endcan
                    <li class="nav-item"><a href="{{ route('profile.show') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"><i class="nav-icon bi bi-person"></i> <span>Profile</span></a></li>
                    @can('session.view')
                    <li class="nav-item"><a href="{{ route('sessions.index') }}" class="nav-link {{ request()->routeIs('sessions.*') ? 'active' : '' }}"><i class="nav-icon bi bi-pc-display"></i> <span>Sessions</span></a></li>
                    @endcan
                    @can('api-token.view')
                    <li class="nav-item"><a href="{{ route('api-tokens.index') }}" class="nav-link {{ request()->routeIs('api-tokens.*') ? 'active' : '' }}"><i class="nav-icon bi bi-hdd-network"></i> <span>API Tokens</span></a></li>
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
    (function () {
        const root = document.documentElement;
        const icon = document.getElementById('theme-icon');
        const saved = localStorage.getItem('theme') || 'dark';
        root.setAttribute('data-bs-theme', saved);
        icon.className = saved === 'dark' ? 'bi bi-moon-stars' : 'bi bi-sun';
        document.getElementById('theme-toggle').addEventListener('click', function () {
            const next = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-bs-theme', next);
            localStorage.setItem('theme', next);
            icon.className = next === 'dark' ? 'bi bi-moon-stars' : 'bi bi-sun';
        });
    })();
</script>
@stack('scripts')
</body>
</html>
