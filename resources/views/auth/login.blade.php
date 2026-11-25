@extends('layouts.guest')
@section('title', 'Log in')
@section('content')
<form method="POST" action="{{ route('login.attempt') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="username">Username</label>
        <input class="form-control" id="username" name="username" value="{{ old('username') }}" required autofocus autocomplete="username">
    </div>
    <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <input class="form-control" id="password" type="password" name="password" required autocomplete="current-password">
    </div>
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
        <label class="form-check-label" for="remember">Remember me</label>
    </div>
    <button class="btn btn-primary w-100">Log in</button>
    <div class="text-center mt-3"><a href="{{ route('password.request') }}" class="small">Forgot password?</a></div>
</form>
@endsection
