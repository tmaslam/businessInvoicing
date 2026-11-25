@extends('layouts.app')
@section('title', $user->exists ? 'Edit User' : 'Add User')
@section('content')
@php
    $joining = (string) $user->Joining_Date;
    $joining = ($joining === '' || str_starts_with($joining, '0000')) ? now()->toDateString() : substr($joining, 0, 10);
@endphp
<div class="card" style="max-width: 640px">
    <div class="card-body">
        <form method="POST" action="{{ $user->exists ? route('users.update', $user->User_Id) : route('users.store') }}" autocomplete="off">
            @csrf
            @if($user->exists) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">User Type *</label>
                    <select name="Role_ID" class="form-select">
                        @foreach($roles as $r)
                            <option value="{{ $r }}" @selected((int) old('Role_ID', $user->Role_ID) === $r)>{{ config('binvoice.role_names')[$r] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">User Name *</label>
                    <input name="User_Name" class="form-control" value="{{ old('User_Name', $user->User_Name) }}" maxlength="50" @disabled($user->exists) @required(! $user->exists) autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password {{ $user->exists ? '' : '*' }}</label>
                    <input type="password" name="password" class="form-control" autocomplete="new-password" @required(! $user->exists)>
                    @if($user->exists)<div class="form-text">Leave blank to keep the current password.</div>@endif
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                </div>
                <div class="col-md-6">
                    <label class="form-label">First Name *</label>
                    <input name="FName" class="form-control" value="{{ old('FName', $user->FName) }}" required maxlength="60">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Last Name *</label>
                    <input name="LName" class="form-control" value="{{ old('LName', $user->LName) }}" required maxlength="60">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email" name="Email" class="form-control" value="{{ old('Email', $user->Email) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Telephone</label>
                    <input name="Contact_No" class="form-control" value="{{ old('Contact_No', $user->Contact_No) }}" maxlength="11">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Profit Distribution *</label>
                    <input type="number" step="0.01" min="0" name="Profit_Distribution" class="form-control" value="{{ old('Profit_Distribution', $user->Profit_Distribution) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Joining Date *</label>
                    <input type="date" name="Joining_Date" class="form-control" value="{{ old('Joining_Date', $joining) }}" required>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('users.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
