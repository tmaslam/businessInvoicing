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
    $nav = [];
    $nav['Overview'][] = ['Dashboard', 'bi-grid-1x2', route('dashboard'), 'dashboard'];
    if ($u->hasRole('admin', 'csr', 'qa')) {
        $nav['Work'][] = ['Orders', 'bi-box-seam', route('orders.index'), 'orders.*'];
    }
    if ($u->hasRole('designer', 'csr')) {
        $nav['Work'][] = ['Complete Order', 'bi-check2-square', route('work.create'), 'work.create'];
    }
    $nav['Work'][] = ['Completed Orders', 'bi-clipboard-check', route('work.index'), 'work.index'];
    if ($u->hasRole('admin')) {
        $nav['Business'][] = ['Customers', 'bi-buildings', route('customers.index'), 'customers.*'];
    }
    if ($u->hasRole('admin', 'qa')) {
        $nav['Business'][] = ['Users', 'bi-people', route('users.index'), 'users.*'];
    }
    if ($u->hasRole('admin')) {
        $nav['Finance'][] = ['Expenses', 'bi-wallet2', route('expenses.index'), 'expenses.*'];
        $nav['Finance'][] = ['Profit Distribution', 'bi-pie-chart', route('profit'), 'profit'];
        $nav['Settings'][] = ['Error Codes', 'bi-bug', route('codes.index'), 'codes.*'];
    }
@endphp

<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            <span class="brand-mark"><i class="bi bi-receipt-cutoff"></i></span>
            <span>Business Invoicing<span class="brand-sub">Digitizing order management</span></span>
        </a>
        <nav class="sidebar-nav">
            @foreach($nav as $section => $items)
                <div class="nav-section">{{ $section }}</div>
                @foreach($items as [$label, $icon, $url, $pattern])
                    <a href="{{ $url }}" class="sidebar-link {{ request()->routeIs($pattern) ? 'active' : '' }}">
                        <i class="bi {{ $icon }}"></i><span>{{ $label }}</span>
                    </a>
                @endforeach
            @endforeach
        </nav>
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <span class="avatar">{{ $initials }}</span>
                <div class="min-w-0">
                    <div class="text-truncate fw-semibold">{{ $u->full_name ?: $u->User_Name }}</div>
                    <small>{{ $u->role_name }} · {{ $u->User_Name }}</small>
                </div>
            </div>
        </div>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="main">
        <header class="topbar">
            <button class="icon-btn d-lg-none" id="sidebarToggle" aria-label="Open menu"><i class="bi bi-list fs-5"></i></button>
            <div class="crumbs d-none d-sm-block">Business Invoicing <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i> <strong>@yield('title', 'Dashboard')</strong></div>
            <div class="ms-auto d-flex align-items-center gap-2">
                @yield('topbar-actions')
                <button class="icon-btn" id="themeToggle" title="Toggle dark mode" aria-label="Toggle dark mode"><i class="bi bi-moon-stars"></i></button>
                <div class="dropdown">
                    <button class="btn btn-light d-flex align-items-center gap-2 px-2" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar avatar-sm">{{ $initials }}</span>
                        <span class="d-none d-md-inline">{{ $u->User_Name }}</span>
                        <i class="bi bi-chevron-down small text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="px-2 py-1 small text-muted">Signed in as <strong class="text-body">{{ $u->role_name }}</strong></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('password.edit') }}"><i class="bi bi-shield-lock"></i> Change password</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> Log out</button>
                            </form>
                        </li>
                    </ul>
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
                <div class="alert alert-danger d-flex gap-2">
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
</div>

<div class="toast-stack" id="toasts">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    // Sidebar (mobile)
    const sb = document.getElementById('sidebar'), bd = document.getElementById('sidebarBackdrop');
    const toggleSb = (open) => { sb.classList.toggle('open', open); bd.classList.toggle('show', open); };
    document.getElementById('sidebarToggle')?.addEventListener('click', () => toggleSb(true));
    bd.addEventListener('click', () => toggleSb(false));

    // Theme
    const root = document.documentElement, themeBtn = document.getElementById('themeToggle');
    const syncIcon = () => themeBtn.querySelector('i').className = root.getAttribute('data-bs-theme') === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
    syncIcon();
    themeBtn.addEventListener('click', () => {
        const next = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-bs-theme', next);
        try { localStorage.setItem('bi-theme', next); } catch (e) {}
        syncIcon();
        document.dispatchEvent(new CustomEvent('themechange'));
    });

    // Toasts
    window.toast = function (message, tone = 'success') {
        const icons = {success: 'bi-check-lg', danger: 'bi-x-lg', warning: 'bi-exclamation-lg', info: 'bi-info-lg'};
        const el = document.createElement('div');
        el.className = 'app-toast';
        el.innerHTML = '<span class="toast-icon tone-' + tone + '"><i class="bi ' + (icons[tone] || icons.info) + '"></i></span><div></div><button type="button" class="btn-close" aria-label="Close"></button>';
        el.querySelector('div').textContent = message;
        document.getElementById('toasts').appendChild(el);
        wire(el);
    };
    function wire(el) {
        const close = () => { el.style.transition = 'opacity .2s'; el.style.opacity = 0; setTimeout(() => el.remove(), 200); };
        el.querySelector('.btn-close').addEventListener('click', close);
        setTimeout(close, 5000);
    }
    document.querySelectorAll('#toasts .app-toast').forEach(wire);

    // Confirm modal for destructive forms
    const modalEl = document.getElementById('confirmModal'), modal = new bootstrap.Modal(modalEl);
    let pending = null;
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form.dataset.confirm || form.dataset.confirmed) return;
        e.preventDefault();
        pending = form;
        const tone = form.dataset.tone || 'danger';
        document.getElementById('confirmTitle').textContent = form.dataset.title || 'Are you sure?';
        document.getElementById('confirmText').textContent = form.dataset.confirm;
        document.getElementById('confirmIcon').className = 'modal-icon tone-' + tone;
        document.getElementById('confirmIcon').innerHTML = '<i class="bi ' + (form.dataset.icon || 'bi-exclamation-triangle') + '"></i>';
        const ok = document.getElementById('confirmOk');
        ok.textContent = form.dataset.ok || 'Confirm';
        ok.className = 'btn ' + (tone === 'danger' ? 'btn-danger' : 'btn-primary');
        modal.show();
    });
    document.getElementById('confirmOk').addEventListener('click', function () {
        if (!pending) return;
        pending.dataset.confirmed = '1';
        modal.hide();
        pending.requestSubmit ? pending.requestSubmit() : pending.submit();
    });

    // Tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"], .act[title]').forEach(el => new bootstrap.Tooltip(el, {container: 'body'}));
})();
</script>
@stack('scripts')
</body>
</html>
