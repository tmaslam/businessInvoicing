@extends('layouts.guest')
@section('title', 'Reset password')
@section('content')
<a href="{{ route('login') }}" class="small fw-medium d-inline-flex align-items-center gap-1 mb-4"><i class="bi bi-arrow-left"></i> Back to sign in</a>
<h1 class="mb-1">Reset your password</h1>
<p class="text-muted mb-4">Enter your username and the email on your account. We'll email you a new password.</p>
<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="username">Username</label>
        <div class="input-icon"><i class="bi bi-person"></i>
            <input class="form-control" id="username" name="username" value="{{ old('username') }}" required autofocus placeholder="Enter your username">
        </div>
    </div>
    <div class="mb-4">
        <label class="form-label" for="email">Email address</label>
        <div class="input-icon"><i class="bi bi-envelope"></i>
            <input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="you@company.com">
        </div>
    </div>
    <button class="btn btn-primary w-100">Email me a new password</button>
</form>
@endsection
