@extends('layouts.guest')
@section('title', 'Sign in')
@section('content')
<h1 class="mb-1">Welcome back</h1>
<p class="text-muted mb-4">Sign in to your account to continue.</p>
<form method="POST" action="{{ route('login.attempt') }}" novalidate>
    @csrf
    <div class="mb-3">
        <label class="form-label" for="username">Username</label>
        <div class="input-icon">
            <i class="bi bi-person"></i>
            <input class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" placeholder="Enter your username">
        </div>
    </div>
    <div class="mb-3">
        <div class="d-flex justify-content-between">
            <label class="form-label" for="password">Password</label>
            <a href="{{ route('password.request') }}" class="small fw-medium">Forgot password?</a>
        </div>
        <div class="input-icon position-relative">
            <i class="bi bi-lock"></i>
            <input class="form-control pe-5" id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
            <button type="button" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-1 text-muted border-0" id="togglePw" aria-label="Show password"><i class="bi bi-eye"></i></button>
        </div>
    </div>
    <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
        <label class="form-check-label small" for="remember">Keep me signed in</label>
    </div>
    <button class="btn btn-primary w-100">Sign in <i class="bi bi-arrow-right"></i></button>
</form>
<script>
    document.getElementById('togglePw').addEventListener('click', function () {
        const pw = document.getElementById('password'), show = pw.type === 'password';
        pw.type = show ? 'text' : 'password';
        this.innerHTML = '<i class="bi ' + (show ? 'bi-eye-slash' : 'bi-eye') + '"></i>';
    });
</script>
@endsection
