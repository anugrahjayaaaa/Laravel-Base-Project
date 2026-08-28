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
    <style>
        /* keep header user dropdown above header bar (AdminLTE can clip it) */
        .app-header { overflow: visible; }
        .app-header .dropdown-menu { z-index: 1030; }
        /* native <details> user menu (no Bootstrap/Popper dependency) */
        .user-menu > summary { list-style: none; cursor: pointer; }
        .user-menu > summary::-webkit-details-marker { display: none; }
        .user-menu .dropdown-menu { display: none; position: absolute; right: 0; z-index: 1030; }
        .user-menu[open] .dropdown-menu { display: block; }
    </style>
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

            {{-- Global menu search (dropdown of matches; click navigates. No backend.) --}}
            <ul class="navbar-nav ms-auto flex-grow-1 px-2 d-none d-md-flex">
                <li class="nav-item w-100 position-relative" style="max-width:420px">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-body border-0"><i class="bi bi-search"></i></span>
                        <input type="search" id="menu-search" class="form-control bg-body border-0" placeholder="Search menu…" autocomplete="off" aria-label="Search menu" aria-expanded="false" aria-controls="menu-search-results">
                    </div>
                    <ul id="menu-search-results" class="dropdown-menu w-100 py-1 shadow-sm" style="display:none;max-height:320px;overflow:auto"></ul>
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
                                        <div class="small">{{ \Illuminate\Support\Str::limit($n->data['label'] ?? ($n->data['action'] ?? 'notification'), 40) }}</div>
                                        <div class="text-muted" style="font-size:11px">{{ $n->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <span class="dropdown-item text-muted">No activity yet.</span>
                        @endforelse
                        </div>
                        <div class="dropdown-divider my-0"></div>
                        <a href="{{ route('notifications.index') }}" class="dropdown-item dropdown-footer text-center text-primary">View all notifications</a>
                    </div>
                </li>

                {{-- User menu (native <details>, no JS dependency) --}}
                @auth
                <details class="nav-item dropdown user-menu">
                    <summary class="nav-link d-flex align-items-center gap-2" aria-label="User menu">
                        <span class="avatar avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:34px;height:34px">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="d-none d-md-inline fw-medium">{{ auth()->user()->name }}</span>
                        <i class="bi bi-chevron-down small opacity-75"></i>
                    </summary>
                    <ul class="dropdown-menu dropdown-menu-end py-1" style="min-width:220px">
                        <li class="dropdown-item-text d-flex align-items-center gap-2 pb-2">
                            <span class="avatar rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:38px;height:38px">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            <div class="text-truncate">
                                <div class="fw-medium">{{ auth()->user()->name }}</div>
                                <div class="text-muted small text-truncate" style="max-width:150px">{{ auth()->user()->email }}</div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider my-0"></li>
                        <li><a href="{{ route('profile.show') }}" class="dropdown-item py-2"><i class="bi bi-person me-2"></i> Profile</a></li>
                        <li><a href="{{ route('sessions.index') }}" class="dropdown-item py-2"><i class="bi bi-pc-display me-2"></i> Sessions</a></li>
                        <li><hr class="dropdown-divider my-0"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item py-2 text-danger w-100 text-start"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                            </form>
                        </li>
                    </ul>
                </details>
                @endauth
            </ul>
        </div>
    </nav>

    {{-- Sidebar --}}
    <aside class="app-sidebar sidebar bg-body-secondary shadow">
        <div class="sidebar-brand">
            <a href="{{ url('/') }}" class="brand-link">
                <i class="bi bi-hexagon-fill brand-image text-primary"></i>
                <span class="brand-text fw-light">{{ config('app.name', 'Laravel') }}</span>
            </a>
        </div>
        <div class="sidebar-wrapper">
            <nav class="mt-3 sidebar-nav">
                <ul class="nav sidebar-menu nav-indent flex-column" data-lte-toggle="treeview" data-accordion="false">
                    <li class="nav-header">MAIN MENU</li>
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" data-menu-text="Dashboard" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-speedometer2"></i> <span>Dashboard</span>
                        </a>
                    </li>
                    @can('user.view')
                    @if(featureVisible('users'))
                    <li class="nav-item"><a href="{{ route('users.index') }}" data-menu-text="Users" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="nav-icon bi bi-people"></i> <span>Users</span></a></li>
                    @endif
                    @endcan
                    @can('role.view')
                    @if(featureVisible('roles'))
                    <li class="nav-item"><a href="{{ route('roles.index') }}" data-menu-text="Roles" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"><i class="nav-icon bi bi-shield"></i> <span>Roles</span></a></li>
                    @endif
                    @endcan
                    @can('permission.view')
                    @if(featureVisible('permissions'))
                    <li class="nav-item"><a href="{{ route('permissions.index') }}" data-menu-text="Permissions" class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}"><i class="nav-icon bi bi-key"></i> <span>Permissions</span></a></li>
                    @endif
                    @endcan
                    @can('audit.view')
                    @if(featureVisible('audit'))
                    <li class="nav-item"><a href="{{ route('audit.index') }}" data-menu-text="Audit Log" class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}"><i class="nav-icon bi bi-journal-text"></i> <span>Audit Log</span></a></li>
                    @endif
                    @endcan
                    <li class="nav-item"><a href="{{ route('profile.show') }}" data-menu-text="Profile" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"><i class="nav-icon bi bi-person"></i> <span>Profile</span></a></li>
                    @can('session.view')
                    @if(featureVisible('sessions'))
                    <li class="nav-item"><a href="{{ route('sessions.index') }}" data-menu-text="Sessions" class="nav-link {{ request()->routeIs('sessions.*') ? 'active' : '' }}"><i class="nav-icon bi bi-pc-display"></i> <span>Sessions</span></a></li>
                    @endif
                    @endcan
                    @can('api-token.view')
                    @if(featureVisible('api-tokens'))
                    <li class="nav-item"><a href="{{ route('api-tokens.index') }}" data-menu-text="API Tokens" class="nav-link {{ request()->routeIs('api-tokens.*') ? 'active' : '' }}"><i class="nav-icon bi bi-hdd-network"></i> <span>API Tokens</span></a></li>
                    @endif
                    @endcan
                    @can('feature.manage')
                    <li class="nav-item {{ request()->routeIs('features.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link"><i class="nav-icon bi bi-gear"></i> <span>Settings</span><i class="nav-arrow bi bi-chevron-right"></i></a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ route('features.index') }}" data-menu-text="Features" class="nav-link {{ request()->routeIs('features.*') ? 'active' : '' }}"><i class="nav-icon bi bi-toggle-on"></i> <span>Features</span></a></li>
                        </ul>
                    </li>
                    @endcan

                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="nav-link border-0 bg-transparent text-start w-100"><i class="nav-icon bi bi-box-arrow-right"></i> <span>Logout</span></button>
                        </form>
                    </li>

                    <li class="nav-header">TEMPLATE</li>

                    {{-- Widgets --}}
                    <li class="nav-item">
                        <a href="#" class="nav-link"><i class="nav-icon bi bi-box-seam-fill"></i> <span>Widgets</span><i class="nav-arrow bi bi-chevron-right"></i></a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/widgets/small-box.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Small Box</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/widgets/info-box.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Info Box</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/widgets/cards.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Cards</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/widgets/social.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Social &amp; Post</span></a></li>
                        </ul>
                    </li>

                    {{-- Layout Options --}}
                    <li class="nav-item">
                        <a href="#" class="nav-link"><i class="nav-icon bi bi-clipboard-fill"></i> <span>Layout Options</span><i class="nav-arrow bi bi-chevron-right"></i></a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/layout/unfixed-sidebar.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Default Sidebar</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/layout/fixed-sidebar.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Fixed Sidebar</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/layout/fixed-header.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Fixed Header</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/layout/fixed-footer.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Fixed Footer</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/layout/fixed-complete.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Fixed Complete</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/layout/layout-custom-area.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Layout + Custom Area</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/layout/sidebar-mini.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Sidebar Mini</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/layout/collapsed-sidebar.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Sidebar Mini + Collapsed</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/layout/collapsed-sidebar-without-hover.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Sidebar Mini + No Hover</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/layout/logo-switch.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Sidebar Mini + Logo Switch</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/layout/top-nav.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Top Nav + No Sidebar</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/layout/layout-rtl.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Layout RTL</span></a></li>
                        </ul>
                    </li>

                    {{-- UI Elements --}}
                    <li class="nav-item">
                        <a href="#" class="nav-link"><i class="nav-icon bi bi-tree-fill"></i> <span>UI Elements</span><i class="nav-arrow bi bi-chevron-right"></i></a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/UI/general.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>General</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/UI/icons.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Icons</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/UI/timeline.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Timeline</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/UI/ribbons.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Ribbons</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/UI/colors.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Colors</span></a></li>
                        </ul>
                    </li>

                    {{-- Mailbox --}}
                    <li class="nav-item">
                        <a href="#" class="nav-link"><i class="nav-icon bi bi-envelope"></i> <span>Mailbox</span><i class="nav-arrow bi bi-chevron-right"></i></a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/mailbox/inbox.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Inbox</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/mailbox/read.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Read Message</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/mailbox/compose.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Compose</span></a></li>
                        </ul>
                    </li>

                    {{-- Forms --}}
                    <li class="nav-item">
                        <a href="#" class="nav-link"><i class="nav-icon bi bi-pencil-square"></i> <span>Forms</span><i class="nav-arrow bi bi-chevron-right"></i></a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/forms/elements.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Elements</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/forms/layout.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Layout</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/forms/validation.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Validation</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/forms/wizard.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Wizard</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/forms/advanced.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Advanced Elements</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/forms/editors.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Editors</span></a></li>
                        </ul>
                    </li>

                    {{-- Tables --}}
                    <li class="nav-item">
                        <a href="#" class="nav-link"><i class="nav-icon bi bi-table"></i> <span>Tables</span><i class="nav-arrow bi bi-chevron-right"></i></a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/tables/simple.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Simple Tables</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/tables/data.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Data Tables</span></a></li>
                        </ul>
                    </li>

                    {{-- Charts --}}
                    <li class="nav-item">
                        <a href="#" class="nav-link"><i class="nav-icon bi bi-graph-up"></i> <span>Charts</span><i class="nav-arrow bi bi-chevron-right"></i></a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/charts/apexcharts.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>ApexCharts</span></a></li>
                        </ul>
                    </li>

                    {{-- Pages --}}
                    <li class="nav-item">
                        <a href="#" class="nav-link"><i class="nav-icon bi bi-file-earmark-text"></i> <span>Pages</span><i class="nav-arrow bi bi-chevron-right"></i></a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/profile.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Profile</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/settings.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Settings</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/invoice.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Invoice</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/calendar.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Calendar</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/kanban.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Kanban</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/chat.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Chat</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/file-manager.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>File Manager</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/projects.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Projects</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/gallery.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Gallery</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/search-results.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Search Results</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/pricing.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Pricing</span></a></li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/faq.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>FAQ</span></a></li>
                            <li class="nav-item">
                                <a href="#" class="nav-link"><i class="nav-icon bi bi-circle"></i> <span>Error</span><i class="nav-arrow bi bi-chevron-right"></i></a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/404.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-record-circle-fill"></i> <span>404</span></a></li>
                                    <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/500.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-record-circle-fill"></i> <span>500</span></a></li>
                                    <li class="nav-item"><a href="{{ asset('vendor/adminlte/pages/maintenance.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-record-circle-fill"></i> <span>Maintenance</span></a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>

                    {{-- Auth examples --}}
                    <li class="nav-item">
                        <a href="#" class="nav-link"><i class="nav-icon bi bi-box-arrow-in-right"></i> <span>Auth</span><i class="nav-arrow bi bi-chevron-right"></i></a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="#" class="nav-link"><i class="nav-icon bi bi-box-arrow-in-right"></i> <span>Version 1</span><i class="nav-arrow bi bi-chevron-right"></i></a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item"><a href="{{ asset('vendor/adminlte/examples/login.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Login</span></a></li>
                                    <li class="nav-item"><a href="{{ asset('vendor/adminlte/examples/register.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Register</span></a></li>
                                    <li class="nav-item"><a href="{{ asset('vendor/adminlte/examples/forgot-password.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Forgot Password</span></a></li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link"><i class="nav-icon bi bi-box-arrow-in-right"></i> <span>Version 2</span><i class="nav-arrow bi bi-chevron-right"></i></a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item"><a href="{{ asset('vendor/adminlte/examples/login-v2.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Login</span></a></li>
                                    <li class="nav-item"><a href="{{ asset('vendor/adminlte/examples/register-v2.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Register</span></a></li>
                                </ul>
                            </li>
                            <li class="nav-item"><a href="{{ asset('vendor/adminlte/examples/lockscreen.html') }}" class="nav-link" target="_blank"><i class="nav-icon bi bi-circle"></i> <span>Lockscreen</span></a></li>
                        </ul>
                    </li>

                    {{-- Multi Level Example --}}
                    <li class="nav-header">MULTI LEVEL EXAMPLE</li>
                    <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon bi bi-circle-fill"></i> <span>Level 1</span></a></li>
                    <li class="nav-item">
                        <a href="#" class="nav-link"><i class="nav-icon bi bi-circle-fill"></i> <span>Level 1</span><i class="nav-arrow bi bi-chevron-right"></i></a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon bi bi-circle"></i> <span>Level 2</span></a></li>
                            <li class="nav-item">
                                <a href="#" class="nav-link"><i class="nav-icon bi bi-circle"></i> <span>Level 2</span><i class="nav-arrow bi bi-chevron-right"></i></a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon bi bi-record-circle-fill"></i> <span>Level 3</span></a></li>
                                    <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon bi bi-record-circle-fill"></i> <span>Level 3</span></a></li>
                                    <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon bi bi-record-circle-fill"></i> <span>Level 3</span></a></li>
                                </ul>
                            </li>
                            <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon bi bi-circle"></i> <span>Level 2</span></a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon bi bi-circle-fill"></i> <span>Level 1</span></a></li>

                    {{-- Labels --}}
                    <li class="nav-header">LABELS</li>
                    <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon bi bi-circle text-danger"></i> <span>Important</span></a></li>
                    <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon bi bi-circle text-warning"></i> <span>Warning</span></a></li>
                    <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon bi bi-circle text-info"></i> <span>Informational</span></a></li>
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

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
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

        // Live header search -> dropdown of matching menu links (no sidebar filtering; click navigates)
        (function () {
            const input = document.getElementById('menu-search');
            const results = document.getElementById('menu-search-results');
            if (!input || !results) return;
            const items = [...document.querySelectorAll('.sidebar-nav a[data-menu-text]')]
                .map(a => ({ text: a.dataset.menuText, href: a.getAttribute('href') }));

            const render = (q) => {
                const matches = q === '' ? [] : items.filter(i => i.text.toLowerCase().includes(q));
                results.innerHTML = matches.length
                    ? matches.map(i => `<li><a class="dropdown-item py-2" href="${i.href}"><i class="bi bi-box-arrow-up-right me-2 opacity-50"></i>${i.text}</a></li>`).join('')
                    : `<li><span class="dropdown-item text-muted">No menu found</span></li>`;
                results.style.display = 'block';
                input.setAttribute('aria-expanded', 'true');
            };
            const close = () => { results.style.display = 'none'; input.setAttribute('aria-expanded', 'false'); };

            input.addEventListener('input', () => render(input.value.trim().toLowerCase()));
            input.addEventListener('focus', () => { if (input.value.trim() !== '') render(input.value.trim().toLowerCase()); });
            input.addEventListener('keydown', e => { if (e.key === 'Escape') { input.value = ''; close(); } });
            document.addEventListener('click', e => { if (!e.target.closest('.position-relative')) close(); });
        })();
    })();
</script>
@stack('scripts')
</body>
</html>
