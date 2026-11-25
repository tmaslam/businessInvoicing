<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Business Invoicing') · Business Invoicing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .navbar-brand { font-weight: 700; letter-spacing: .3px; }
        .card { border: 0; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .table thead th { background: #2f3e4e; color: #fff; font-weight: 500; white-space: nowrap; }
        .table td { vertical-align: middle; }
        .actions .btn { padding: .15rem .45rem; }
        .row-today { background: #e8f7ee !important; }
        .row-blocked td { color: #999; text-decoration: line-through; }
        [contenteditable="true"] { background: #fff8e1; cursor: text; outline: 1px dashed #f0b429; }
    </style>
    @stack('head')
</head>
<body>
@php($u = auth()->user())
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}"><i class="bi bi-receipt"></i> Business Invoicing</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                @if($u->hasRole('admin'))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">Customers</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('customers.create') }}">Add Customer</a></li>
                            <li><a class="dropdown-item" href="{{ route('customers.index') }}">List Customers</a></li>
                        </ul>
                    </li>
                @endif
                @if($u->hasRole('admin', 'qa'))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('users.*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">Users</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('users.create') }}">Add User</a></li>
                            <li><a class="dropdown-item" href="{{ route('users.index') }}">List Users</a></li>
                        </ul>
                    </li>
                @endif
                @if($u->hasRole('admin', 'csr', 'qa'))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">Orders</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('orders.create') }}">Add Order</a></li>
                            <li><a class="dropdown-item" href="{{ route('orders.index') }}">List Orders</a></li>
                        </ul>
                    </li>
                @endif
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('work.*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                        {{ $u->hasRole('csr') ? 'Complete Order' : 'Designer Orders' }}
                    </a>
                    <ul class="dropdown-menu">
                        @if($u->hasRole('designer', 'csr'))
                            <li><a class="dropdown-item" href="{{ route('work.create') }}">Complete Order</a></li>
                        @endif
                        <li><a class="dropdown-item" href="{{ route('work.index') }}">Completed Orders</a></li>
                    </ul>
                </li>
                @if($u->hasRole('admin'))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('codes.*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">Error Codes</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('codes.create') }}">Add Code</a></li>
                            <li><a class="dropdown-item" href="{{ route('codes.index') }}">Codes Listing</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">Expense</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('expenses.create') }}">Add Expense</a></li>
                            <li><a class="dropdown-item" href="{{ route('expenses.index') }}">Expense Listing</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('profit') ? 'active' : '' }}" href="{{ route('profit') }}">Profit Distribution</a></li>
                @endif
            </ul>
            <div class="dropdown">
                <a class="nav-link text-light dropdown-toggle" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i> {{ $u->User_Name }} <span class="badge text-bg-secondary">{{ $u->role_name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('password.edit') }}"><i class="bi bi-key"></i> Change password</a></li>
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
</nav>

<main class="container-fluid px-3 px-lg-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h4 mb-0">@yield('title')</h1>
        <div>@yield('actions')</div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @yield('content')
</main>

<footer class="text-center text-muted small py-4">Copyright &copy; 2010-{{ date('Y') }} Business Invoicing. All Rights Reserved.</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Confirm dialogs for destructive forms: <form data-confirm="...">
    document.addEventListener('submit', function (e) {
        const msg = e.target.getAttribute('data-confirm');
        if (msg && !confirm(msg)) { e.preventDefault(); }
    });
</script>
@stack('scripts')
</body>
</html>
