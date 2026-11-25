@extends('layouts.app')
@section('title', 'Users')
@section('subtitle', 'Team members, their access and performance reports.')
@section('actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary" data-shortcut="n" title="Shortcut: N"><i class="bi bi-person-plus"></i> Add user</a>
@endsection
@section('content')
@php($isAdmin = auth()->user()->isAdmin())
@php($tones = [2 => 'primary', 3 => 'info', 4 => 'warning'])
<div class="card">
    @include('partials.date-range', ['keep' => array_filter(request()->only('role', 'q'))])
    <form method="GET" class="filter-bar pt-0" style="border-bottom:1px solid var(--bi-border)">
        <input type="hidden" name="from" value="{{ $range->from->toDateString() }}"><input type="hidden" name="to" value="{{ $range->to->toDateString() }}">
        @if(request('role'))<input type="hidden" name="role" value="{{ (int) request('role') }}">@endif
        <div class="grow">
            <div class="input-icon"><i class="bi bi-search"></i>
                <input name="q" class="form-control form-control-sm" placeholder="Search by ID, username or name…" value="{{ request('q') }}">
            </div>
        </div>
        @if(count($roles) > 1)
            <div class="btn-group btn-group-sm" role="group">
                <a href="{{ request()->fullUrlWithQuery(['role' => null, 'page' => null]) }}" class="btn btn-sm {{ request('role') ? 'btn-light' : 'btn-primary' }}">All</a>
                @foreach($roles as $r)
                    <a href="{{ request()->fullUrlWithQuery(['role' => $r, 'page' => null]) }}" class="btn btn-sm {{ (int) request('role') === $r ? 'btn-primary' : 'btn-light' }}">{{ config('binvoice.role_names')[$r] }}s</a>
                @endforeach
            </div>
        @endif
        <button class="btn btn-sm btn-light"><i class="bi bi-search"></i> Search</button>
    </form>
    <div class="filter-note"><i class="bi bi-info-circle"></i> Report, Excel and chart actions use the period <strong class="mx-1">{{ $range->from->format('d M Y') }} – {{ $range->to->format('d M Y') }}</strong></div>
    <div class="table-wrap">
        <table class="table table-hover">
            <thead><tr><th>User</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($users as $usr)
                @php($p = ['user' => $usr->User_Id] + $range->query())
                @php($ini = strtoupper(mb_substr((string) $usr->FName, 0, 1).mb_substr((string) $usr->LName, 0, 1)))
                <tr class="{{ $usr->isBlocked() ? 'row-blocked' : '' }}">
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm" style="background:hsl({{ ($usr->User_Id * 47) % 360 }} 60% 55%)">{{ $ini ?: '?' }}</span>
                            <div><span class="cell-strong">{{ $usr->full_name }}</span><span class="cell-sub">ID {{ $usr->User_Id }}</span></div>
                        </div>
                    </td>
                    <td class="cell-mono">{{ $usr->User_Name }}</td>
                    <td class="text-muted">{{ $usr->Email }}</td>
                    <td><span class="pill no-dot tone-{{ $tones[$usr->Role_ID] ?? 'neutral' }}">{{ $usr->role_name }}</span></td>
                    <td>@if($usr->isBlocked())<span class="pill tone-danger">Blocked</span>@else<span class="pill tone-success">Active</span>@endif</td>
                    <td class="text-end">
                        <div class="row-actions">
                            @unless($usr->isBlocked())
                                <a class="act act-primary" title="Progress report" href="{{ route('users.report', $p) }}"><i class="bi bi-list-check"></i></a>
                                <a class="act act-success" title="Excel report" href="{{ route('users.report.excel', $p) }}"><i class="bi bi-file-earmark-spreadsheet"></i></a>
                                <a class="act act-info" title="Progress chart" href="{{ route('users.chart', $p) }}"><i class="bi bi-bar-chart"></i></a>
                            @endunless
                            <a class="act act-primary" title="Edit" href="{{ route('users.edit', $usr->User_Id) }}"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('users.toggle', $usr->User_Id) }}"
                                  data-confirm="{{ $usr->isBlocked() ? $usr->full_name.' will be able to sign in again.' : $usr->full_name.' will not be able to sign in.' }}"
                                  data-title="{{ $usr->isBlocked() ? 'Reactivate user?' : 'Block user?' }}" data-tone="warning" data-icon="{{ $usr->isBlocked() ? 'bi-unlock' : 'bi-slash-circle' }}" data-ok="{{ $usr->isBlocked() ? 'Reactivate' : 'Block' }}">
                                @csrf
                                <button class="act act-warning" title="{{ $usr->isBlocked() ? 'Reactivate' : 'Block' }}"><i class="bi {{ $usr->isBlocked() ? 'bi-unlock' : 'bi-slash-circle' }}"></i></button>
                            </form>
                            @if($isAdmin)
                                <form method="POST" action="{{ route('users.destroy', $usr->User_Id) }}" data-confirm="{{ $usr->full_name }} will be removed. Their completed orders are kept." data-title="Delete user?" data-ok="Delete">
                                    @csrf @method('DELETE')
                                    <button class="act act-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <x-empty colspan="6" icon="bi-people" title="No users found">Try a different search.</x-empty>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-pager :items="$users" />
</div>
@endsection
