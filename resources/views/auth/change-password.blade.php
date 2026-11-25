@extends('layouts.app')
@section('title', 'Change Password')
@section('subtitle', 'Use at least 6 characters. You will stay signed in on this device.')
@section('content')
<div class="card card-xs">
    <form method="POST" action="{{ route('password.change') }}">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Current password <span class="req">*</span></label>
                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required autocomplete="current-password">
            </div>
            <div class="mb-3">
                <label class="form-label">New password <span class="req">*</span></label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
            </div>
            <div>
                <label class="form-label">Confirm new password <span class="req">*</span></label>
                <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
            </div>
        </div>
        <div class="form-footer">
            <a href="{{ route('dashboard') }}" class="btn btn-light">Cancel</a>
            <button class="btn btn-primary"><i class="bi bi-check2"></i> Update password</button>
        </div>
    </form>
</div>
@endsection
