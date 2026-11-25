@extends('layouts.app')
@section('title', 'Change Password')
@section('content')
<div class="card" style="max-width:480px">
    <div class="card-body">
        <form method="POST" action="{{ route('password.change') }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Current password</label>
                <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
            </div>
            <div class="mb-3">
                <label class="form-label">New password</label>
                <input type="password" name="password" class="form-control" required autocomplete="new-password">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm new password</label>
                <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
            </div>
            <button class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
@endsection
