<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · Business Invoicing</title>
    <script>
        try { const t = localStorage.getItem('bi-theme'); if (t) document.documentElement.setAttribute('data-bs-theme', t); } catch (e) {}
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" rel="stylesheet">
</head>
<body>
<div class="auth">
    <section class="auth-aside">
        <div class="d-flex align-items-center gap-2 fw-bold text-white">
            <span class="brand-mark"><i class="bi bi-receipt-cutoff"></i></span> Business Invoicing
        </div>
        <div>
            <h2 class="mb-3">Orders, designers and invoices — in one place.</h2>
            <p>Track every digitizing order from assignment to QA, price it automatically, and send invoices in a click.</p>
            <div class="auth-feature"><i class="bi bi-lightning-charge"></i> Automatic stitch-based pricing</div>
            <div class="auth-feature"><i class="bi bi-people"></i> Designer, CSR and QA workflows</div>
            <div class="auth-feature"><i class="bi bi-file-earmark-spreadsheet"></i> Excel invoices and progress reports</div>
        </div>
        <div class="small" style="color:#64748b">© 2010–{{ date('Y') }} Business Invoicing</div>
    </section>
    <main class="auth-main">
        <div class="auth-card">
            <div class="d-lg-none d-flex align-items-center gap-2 fw-bold mb-4">
                <span class="brand-mark"><i class="bi bi-receipt-cutoff"></i></span> Business Invoicing
            </div>
            @if(session('status'))
                <div class="alert alert-success d-flex gap-2"><i class="bi bi-check-circle mt-1"></i><div>{{ session('status') }}</div></div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger d-flex gap-2"><i class="bi bi-exclamation-circle mt-1"></i><div>{{ $errors->first() }}</div></div>
            @endif
            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
