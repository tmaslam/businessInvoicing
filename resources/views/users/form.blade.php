@extends('layouts.app')
@section('title', $user->exists ? 'Edit user' : 'Add user')
@section('subtitle', $user->exists ? $user->full_name.' · '.$user->User_Name : 'Create a login for a designer, CSR or QA member.')
@section('actions')
    <a href="{{ route('users.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Users</a>
@endsection
@section('content')
@php
    $joining = (string) $user->Joining_Date;
    $joining = ($joining === '' || str_starts_with($joining, '0000')) ? now()->toDateString() : substr($joining, 0, 10);
    $icons = [2 => 'bi-brush', 3 => 'bi-headset', 4 => 'bi-patch-check'];
    $desc = [2 => 'Completes design orders', 3 => 'Adds and completes orders', 4 => 'Verifies designs, manages designers'];
@endphp
<div class="card card-narrow">
    <form method="POST" action="{{ $user->exists ? route('users.update', $user->User_Id) : route('users.store') }}" autocomplete="off">
        @csrf
        @if($user->exists) @method('PUT') @endif
        <div class="card-body">
            <div class="form-section">
                <div class="form-section-title">Role</div>
                <div class="row g-2">
                    @foreach($roles as $r)
                        <div class="col-md-4">
                            <input type="radio" class="btn-check" name="Role_ID" id="role{{ $r }}" value="{{ $r }}" @checked((int) old('Role_ID', $user->Role_ID) === $r)>
                            <label class="btn btn-light w-100 h-100 text-start d-block p-3" for="role{{ $r }}" style="white-space:normal">
                                <i class="bi {{ $icons[$r] }} fs-5 d-block mb-1"></i>
                                <span class="fw-semibold d-block">{{ config('binvoice.role_names')[$r] }}</span>
                                <span class="small text-muted">{{ $desc[$r] }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">Login</div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Username @unless($user->exists)<span class="req">*</span>@endunless</label>
                        <div class="input-icon"><i class="bi bi-at"></i>
                            <input name="User_Name" class="form-control @error('User_Name') is-invalid @enderror" value="{{ old('User_Name', $user->User_Name) }}" maxlength="50" @disabled($user->exists) @required(! $user->exists) autocomplete="off">
                        </div>
                        @if($user->exists)<div class="form-text">Usernames can't be changed.</div>@else<div class="form-text">Letters, numbers, dashes and underscores.</div>@endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password @unless($user->exists)<span class="req">*</span>@endunless</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" @required(! $user->exists) placeholder="{{ $user->exists ? 'Leave blank to keep current' : 'At least 6 characters' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm password</label>
                        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">Profile</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First name <span class="req">*</span></label>
                        <input name="FName" class="form-control @error('FName') is-invalid @enderror" value="{{ old('FName', $user->FName) }}" required maxlength="60">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last name <span class="req">*</span></label>
                        <input name="LName" class="form-control @error('LName') is-invalid @enderror" value="{{ old('LName', $user->LName) }}" required maxlength="60">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email <span class="req">*</span></label>
                        <div class="input-icon"><i class="bi bi-envelope"></i>
                            <input type="email" name="Email" class="form-control @error('Email') is-invalid @enderror" value="{{ old('Email', $user->Email) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telephone</label>
                        <div class="input-icon"><i class="bi bi-telephone"></i>
                            <input name="Contact_No" class="form-control" value="{{ old('Contact_No', $user->Contact_No) }}" maxlength="11">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Profit distribution <span class="req">*</span></label>
                        <input type="number" step="0.01" min="0" name="Profit_Distribution" class="form-control @error('Profit_Distribution') is-invalid @enderror" value="{{ old('Profit_Distribution', $user->Profit_Distribution) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Joining date <span class="req">*</span></label>
                        <input type="date" name="Joining_Date" class="form-control" value="{{ old('Joining_Date', $joining) }}" required>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-footer">
            <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a>
            <button class="btn btn-primary"><i class="bi bi-check2"></i> {{ $user->exists ? 'Save changes' : 'Create user' }}</button>
        </div>
    </form>
</div>
@endsection
@push('head')
<style>.btn-check:checked + .btn-light { border-color: var(--bi-primary); background: var(--bi-primary-soft); color: var(--bi-primary-text); box-shadow: 0 0 0 1px var(--bi-primary) inset; }</style>
@endpush
