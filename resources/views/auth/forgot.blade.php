@extends('layouts.guest')
@section('title', 'Forgot password')
@section('content')
<p class="text-muted small">Enter your username and the email address on your account. A new password will be emailed to you.</p>
<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="username">Username</label>
        <input class="form-control" id="username" name="username" value="{{ old('username') }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="email">Email address</label>
        <input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" required>
    </div>
    <button class="btn btn-primary w-100">Send new password</button>
    <div class="text-center mt-3"><a href="{{ route('login') }}" class="small">Back to login</a></div>
</form>
@endsection
