@extends('layouts.app')
@section('title', 'User Listing')
@section('actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add User</a>
@endsection
@section('content')
@php($isAdmin = auth()->user()->isAdmin())
<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-auto">
                <label class="form-label small mb-0">Date From</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ $range->from->toDateString() }}">
            </div>
            <div class="col-auto">
                <label class="form-label small mb-0">Date To</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ $range->to->toDateString() }}">
            </div>
            @if(count($roles) > 1)
                <div class="col-auto">
                    <label class="form-label small mb-0">Type</label>
                    <select name="role" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($roles as $r)
                            <option value="{{ $r }}" @selected((int) request('role') === $r)>{{ config('binvoice.role_names')[$r] }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-auto">
                <label class="form-label small mb-0">Search</label>
                <input name="q" class="form-control form-control-sm" placeholder="ID, username or name" value="{{ request('q') }}">
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-outline-primary"><i class="bi bi-funnel"></i> Apply</button></div>
        </form>
        <p class="small text-muted mb-2">The date range is used by the report, Excel and chart actions.</p>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm">
                <thead><tr><th>ID</th><th>User Name</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Type</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse($users as $usr)
                    @php($p = ['user' => $usr->User_Id] + $range->query())
                    <tr class="{{ $usr->isBlocked() ? 'row-blocked' : '' }}">
                        <td>{{ $usr->User_Id }}</td>
                        <td>{{ $usr->User_Name }}</td>
                        <td>{{ $usr->FName }}</td>
                        <td>{{ $usr->LName }}</td>
                        <td>{{ $usr->Email }}</td>
                        <td>{{ $usr->role_name }} @if($usr->isBlocked())<span class="badge text-bg-danger">Blocked</span>@endif</td>
                        <td class="text-end actions text-nowrap">
                            @unless($usr->isBlocked())
                                <a class="btn btn-sm btn-outline-secondary" title="Progress report" href="{{ route('users.report', $p) }}"><i class="bi bi-list-ul"></i></a>
                                <a class="btn btn-sm btn-outline-success" title="Excel report" href="{{ route('users.report.excel', $p) }}"><i class="bi bi-file-earmark-excel"></i></a>
                                <a class="btn btn-sm btn-outline-info" title="Chart" href="{{ route('users.chart', $p) }}"><i class="bi bi-bar-chart"></i></a>
                            @endunless
                            <a class="btn btn-sm btn-outline-primary" title="Edit" href="{{ route('users.edit', $usr->User_Id) }}"><i class="bi bi-pencil"></i></a>
                            <form class="d-inline" method="POST" action="{{ route('users.toggle', $usr->User_Id) }}" data-confirm="Are you sure to {{ $usr->isBlocked() ? 'reactivate' : 'block' }} this user?">
                                @csrf
                                <button class="btn btn-sm btn-outline-warning" title="{{ $usr->isBlocked() ? 'Reactivate' : 'Block' }}"><i class="bi {{ $usr->isBlocked() ? 'bi-unlock' : 'bi-slash-circle' }}"></i></button>
                            </form>
                            @if($isAdmin)
                                <form class="d-inline" method="POST" action="{{ route('users.destroy', $usr->User_Id) }}" data-confirm="Are you sure to delete this?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">No user found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    </div>
</div>
@endsection
