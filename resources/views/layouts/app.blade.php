<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · Business Invoicing</title>
    <script>
        try { const t = localStorage.getItem('bi-theme'); if (t) document.documentElement.setAttribute('data-bs-theme', t); } catch (e) {}
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" rel="stylesheet">
    @stack('head')
</head>
<body>
@php
    $u = auth()->user();
    $initials = strtoupper(mb_substr((string) $u->FName, 0, 1).mb_substr((string) $u->LName, 0, 1)) ?: strtoupper(mb_substr($u->User_Name, 0, 2));
    // [label, icon, url, active-route-pattern]
    $nav = [['Dashboard', 'bi-grid-1x2', route('dashboard'), 'dashboard']];
    if ($u->hasRole('admin', 'csr', 'qa')) { $nav[] = ['Orders', 'bi-box-seam', route('orders.index'), 'orders.*']; }
    if ($u->hasRole('designer', 'csr')) { $nav[] = ['Complete Order', 'bi-check2-square', route('work.create'), 'work.create']; }
    $nav[] = ['Completed', 'bi-clipboard-check', route('work.index'), 'work.index'];
    if ($u->hasRole('admin')) { $nav[] = ['Customers', 'bi-buildings', route('customers.index'), 'customers.*']; }
    if ($u->hasRole('admin', 'qa')) { $nav[] = ['Users', 'bi-people', route('users.index'), 'users.*']; }
    if ($u->hasRole('admin')) {
        $nav[] = ['Finance', 'bi-wallet2', [
            ['Expenses', 'bi-receipt', route('expenses.index')],
            ['Profit Distribution', 'bi-pie-chart', route('profit')],
        ], 'expenses.*|profit'];
        $nav[] = ['Error Codes', 'bi-bug', route('codes.index'), 'codes.*'];
    }
@endphp

<div class="nav-progress" id="navProgress"></div>

<div class="app-shell">
    <header class="topnav">
        <div class="topnav-inner">
            <button class="icon-btn nav-toggle" id="navToggle" aria-label="Menu" aria-expanded="false"><i class="bi bi-list fs-5"></i></button>
            <a href="{{ route('dashboard') }}" class="brand">
                <span class="brand-mark"><i class="bi bi-receipt-cutoff"></i></span>
                <span class="brand-text">Business Invoicing</span>
            </a>

            <ul class="mainnav" id="mainnav">
                @foreach($nav as [$label, $icon, $target, $pattern])
                    @php($active = collect(explode('|', $pattern))->contains(fn ($p) => request()->routeIs($p)))
                    @if(is_array($target))
                        <li class="dropdown">
                            <button class="nav-item-link dropdown-toggle {{ $active ? 'active' : '' }}" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi {{ $icon }}"></i>{{ $label }}
                            </button>
                            <ul class="dropdown-menu">
                                @foreach($target as [$subLabel, $subIcon, $subUrl])
                                    <li><a class="dropdown-item {{ url()->current() === $subUrl ? 'active' : '' }}" href="{{ $subUrl }}"><i class="bi {{ $subIcon }}"></i>{{ $subLabel }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <li><a href="{{ $target }}" class="nav-item-link {{ $active ? 'active' : '' }}" @if($active) aria-current="page" @endif><i class="bi {{ $icon }}"></i>{{ $label }}</a></li>
                    @endif
                @endforeach
            </ul>

            <div class="nav-right">
                <button class="icon-btn" id="themeToggle" title="Dark / light mode" aria-label="Toggle dark mode"><i class="bi bi-moon-stars"></i></button>
                <div class="dropdown">
                    <button class="user-btn" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar avatar-sm">{{ $initials }}</span>
                        <span class="d-none d-md-block text-start">{{ $u->User_Name }}<span class="role">{{ $u->role_name }}</span></span>
                        <i class="bi bi-chevron-down small text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="px-2 py-1">
                            <div class="fw-semibold">{{ $u->full_name ?: $u->User_Name }}</div>
                            <div class="small text-muted">{{ $u->role_name }} · {{ $u->User_Name }}</div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('password.edit') }}"><i class="bi bi-shield-lock"></i> Change password</a></li>
                        <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#shortcutsModal"><i class="bi bi-keyboard"></i> Keyboard shortcuts</button></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> Log out</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <main class="content">
        <div class="page-header">
            <div>
                <h1 class="page-title">@yield('title', 'Dashboard')</h1>
                @hasSection('subtitle')<p class="page-subtitle">@yield('subtitle')</p>@endif
            </div>
            @hasSection('actions')<div class="page-actions">@yield('actions')</div>@endif
        </div>

        @if($errors->any())
            <div class="alert alert-danger d-flex gap-2" role="alert">
                <i class="bi bi-exclamation-octagon mt-1"></i>
                <div>
                    <strong>Please fix the following:</strong>
                    <ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="app-footer">© 2010–{{ date('Y') }} Business Invoicing · All rights reserved.</footer>
</div>

<div class="toast-stack" id="toasts" aria-live="polite">
    @if(session('success'))
        <div class="app-toast" role="status">
            <span class="toast-icon tone-success"><i class="bi bi-check-lg"></i></span>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" aria-label="Close"></button>
        </div>
    @endif
</div>

{{-- Confirm dialog used by any <form data-confirm="..."> --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content">
            <div class="modal-body p-4">
                <div class="modal-icon tone-danger" id="confirmIcon"><i class="bi bi-exclamation-triangle"></i></div>
                <h5 class="mb-1" id="confirmTitle">Are you sure?</h5>
                <p class="text-muted mb-0" id="confirmText"></p>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmOk">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="shortcutsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px">
        <div class="modal-content">
            <div class="modal-body p-4">
                <h5 class="mb-3"><i class="bi bi-keyboard"></i> Keyboard shortcuts</h5>
                <table class="table table-sm mb-0">
                    <tr><td><kbd>/</kbd></td><td>Jump to search</td></tr>
                    <tr><td><kbd>N</kbd></td><td>New item on this page (order, customer…)</td></tr>
                    <tr><td><kbd>Enter</kbd></td><td>Submit the current form</td></tr>
                    <tr><td><kbd>Esc</kbd></td><td>Close dialogs and menus</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
@stack('scripts')
</body>
</html>
