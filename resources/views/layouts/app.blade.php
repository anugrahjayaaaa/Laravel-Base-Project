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

            <ul class="navbar-nav ms-auto align-items-center">
                {{-- Theme toggle --}}
                <li class="nav-item">
                    <button class="btn btn-link nav-link px-2" id="theme-toggle" type="button" title="Toggle light/dark" aria-label="Toggle theme">
                        <i class="bi bi-moon-stars fs-5" id="theme-icon"></i>
                    </button>
                </li>

                {{-- Notifications --}}
                <li class="nav-item dropdown">
                    <a class="nav-link position-relative px-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications" aria-label="Notifications">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle border" style="width:36px;height:36px;background:var(--lbp-surface-2);border-color:var(--lbp-border)">
                            <i class="bi bi-bell fs-5"></i>
                        </span>
                        @if (($notifications['unread'] ?? 0) > 0)
                            <span class="position-absolute badge rounded-pill text-bg-danger" style="top:2px;right:2px;font-size:10px;padding:2px 5px">{{ $notifications['unread'] }}</span>
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-lg py-0" style="width:320px">
                        <span class="dropdown-item dropdown-header d-flex justify-content-between"><span>Recent activity</span><span class="badge bg-primary-subtle text-primary">{{ $notifications['items']->count() }}</span></span>
                        <div class="dropdown-divider my-0"></div>
                        <div style="max-height:280px;overflow:auto">
                        @forelse ($notifications['items'] as $n)
                            <a href="{{ route('audit.index') }}" class="dropdown-item py-2 border-bottom" style="white-space:normal">
                                <div class="d-flex gap-2">
                                    <i class="bi bi-activity text-primary mt-1"></i>
                                    <div class="flex-grow-1">
                                        <div class="small">{{ \Illuminate\Support\Str::limit($n->description, 40) }}</div>
                                        <div class="text-muted" style="font-size:11px">{{ $n->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <span class="dropdown-item text-muted">No activity yet.</span>
                        @endforelse
                        </div>
                        <div class="dropdown-divider my-0"></div>
                        <a href="{{ route('audit.index') }}" class="dropdown-item dropdown-footer text-center text-primary">View all audit log</a>
                    </div>
                </li>

                {{-- User menu --}}
                @auth
                <li class="nav-item dropdown">
                    <a class="nav-link d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:34px;height:34px">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="d-none d-md-inline fw-medium">{{ auth()->user()->name }}</span>
                        <i class="bi bi-chevron-down small opacity-75"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end py-1" style="min-width:220px">
                        <div class="dropdown-item-text d-flex align-items-center gap-2 pb-2">
                            <span class="avatar rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:38px;height:38px">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            <div class="text-truncate">
                                <div class="fw-medium">{{ auth()->user()->name }}</div>
                                <div class="text-muted small text-truncate" style="max-width:150px">{{ auth()->user()->email }}</div>
                            </div>
                        </div>
                        <div class="dropdown-divider my-0"></div>
                        <a href="{{ route('profile.show') }}" class="dropdown-item py-2"><i class="bi bi-person me-2"></i> Profile</a>
                        <a href="{{ route('sessions.index') }}" class="dropdown-item py-2"><i class="bi bi-pc-display me-2"></i> Sessions</a>
                        <div class="dropdown-divider my-0"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item py-2 text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
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
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#tpl-ui" role="button" aria-expanded="false">
                            <i class="nav-icon bi bi-collection"></i> <span>UI Elements</span> <i class="bi bi-chevron-down ms-auto small"></i>
                        </a>
                        <ul class="nav nav-treeview collapse" id="tpl-ui" style="padding-left:1rem">
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/forms/elements.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-ui-checks"></i> <span>Forms</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/forms/advanced.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-sliders"></i> <span>Advanced Forms</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/tables/simple.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-table"></i> <span>Tables</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/tables/data.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-grid-1x2"></i> <span>Data Tables</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/widgets/cards.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-card-heading"></i> <span>Cards</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/widgets/info-box.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-info-circle"></i> <span>Info Box</span></a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#tpl-pages" role="button" aria-expanded="false">
                            <i class="nav-icon bi bi-files"></i> <span>Pages</span> <i class="bi bi-chevron-down ms-auto small"></i>
                        </a>
                        <ul class="nav nav-treeview collapse" id="tpl-pages" style="padding-left:1rem">
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/profile.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-person"></i> <span>Profile</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/projects.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-kanban"></i> <span>Projects</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/kanban.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-columns-gap"></i> <span>Kanban</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/calendar.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-calendar"></i> <span>Calendar</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/invoice.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-receipt"></i> <span>Invoice</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/gallery.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-images"></i> <span>Gallery</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/pricing.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-tag"></i> <span>Pricing</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/chat.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-chat-dots"></i> <span>Chat</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/faq.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-question-circle"></i> <span>FAQ</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/settings.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-gear"></i> <span>Settings</span></a></li>
                        </ul>
                    </li>
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
        icon.className = saved === 'dark' ? 'bi bi-moon-stars fs-5' : 'bi bi-sun fs-5';
        document.getElementById('theme-toggle').addEventListener('click', function () {
            const next = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-bs-theme', next);
            localStorage.setItem('theme', next);
            icon.className = next === 'dark' ? 'bi bi-moon-stars fs-5' : 'bi bi-sun fs-5';
        });
        // tooltips for action/icon buttons
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    })();
</script>
@stack('scripts')
</body>
</html>
