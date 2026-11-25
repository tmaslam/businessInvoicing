<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · Business Invoicing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg, #2f3e4e, #1b2631); }
        .auth-card { max-width: 420px; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">
<div class="card auth-card w-100 shadow">
    <div class="card-body p-4">
        <h1 class="h4 text-center mb-4">Business Invoicing</h1>
        @if(session('status'))
            <div class="alert alert-success small">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger small mb-3">{{ $errors->first() }}</div>
        @endif
        @yield('content')
    </div>
</div>
</body>
</html>
