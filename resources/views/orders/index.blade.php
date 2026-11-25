@extends('layouts.app')
@section('title', 'Orders')
@section('subtitle', 'Search, review and manage all design orders.')
@section('actions')
    <a href="{{ route('orders.create') }}" class="btn btn-primary" data-shortcut="n" title="Shortcut: N"><i class="bi bi-plus-lg"></i> New order</a>
@endsection
@section('content')
@php($isAdmin = auth()->user()->isAdmin())
@php($filtered = (bool) array_filter($filters))
<div class="card">
    <form method="GET" class="filter-bar">
        <div style="width:130px">
            <label class="form-label">Order ID</label>
            <input name="order_id" type="number" class="form-control form-control-sm" value="{{ $filters['order_id'] ?? '' }}" placeholder="#">
        </div>
        <div class="grow">
            <label class="form-label">Design name</label>
            <div class="input-icon"><i class="bi bi-search"></i>
                <input name="design" class="form-control form-control-sm" value="{{ $filters['design'] ?? '' }}" placeholder="Search designs…">
            </div>
        </div>
        <div class="grow">
            <label class="form-label">Customer</label>
            <select name="customer" class="form-select form-select-sm" data-autosubmit>
                <option value="">All customers</option>
                @foreach($customers as $c)
                    <option value="{{ $c->Customer_ID }}" @selected(($filters['customer'] ?? null) == $c->Customer_ID)>{{ $c->CompanyName }}</option>
                @endforeach
            </select>
        </div>
        <div style="width:160px">
            <label class="form-label">Status</label>
            <select name="status" class="form-select form-select-sm" data-autosubmit>
                <option value="">Any status</option>
                @foreach($statusOptions as $s)
                    <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ['Archive' => 'Paid (Archive)', 'Lock' => 'Locked'][$s] ?? $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="d-flex gap-1">
            <button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filter</button>
            @if($filtered)<a href="{{ route('orders.index') }}" class="btn btn-sm btn-light">Clear</a>@endif
        </div>
    </form>
    @unless($filtered)
        <div class="filter-note"><i class="bi bi-info-circle"></i> Showing new, in-progress, completed and locked orders since {{ now()->subMonthNoOverflow()->startOfMonth()->format('d M Y') }}. Use the filters to search older or paid orders.</div>
    @endunless
    <div class="table-wrap">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>Order</th><th>Design</th><th class="text-end">Stitches</th>
                @if($isAdmin)<th class="text-end">Price</th>@endif
                <th>Status</th><th>Added</th><th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($orders as $o)
                <tr>
                    <td class="cell-mono"><a href="{{ route('orders.show', $o->Order_ID) }}">#{{ $o->Order_ID }}</a></td>
                    <td class="min-w-0">
                        <span class="cell-strong">{{ $o->Design_Name }}</span>
                        <span class="cell-sub">{{ $o->Customer_Name }}</span>
                    </td>
                    <td class="text-end tabular">{{ number_format((int) $o->No_Of_Stiches) }}</td>
                    @if($isAdmin)<td class="text-end tabular">${{ number_format((float) $o->Order_Price, 2) }}</td>@endif
                    <td>
                        <x-status :status="$o->Order_Status" />
                        @if($o->isVerified())<i class="bi bi-patch-check-fill ms-1" style="color: var(--bi-success)" data-bs-toggle="tooltip" title="QA verified"></i>@endif
                    </td>
                    <td class="small text-muted text-nowrap">{{ $o->Effective_Date?->format('d M Y') }}<span class="cell-sub">{{ $o->Effective_Date?->format('g:i A') }}</span></td>
                    <td class="text-end">
                        <div class="row-actions">
                            <a class="act act-primary" title="View" href="{{ route('orders.show', $o->Order_ID) }}"><i class="bi bi-eye"></i></a>
                            <a class="act act-primary" title="Edit" href="{{ route('orders.edit', $o->Order_ID) }}"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('orders.destroy', $o->Order_ID) }}" data-confirm="Order #{{ $o->Order_ID }} ({{ $o->Design_Name }}) will be deleted." data-title="Delete order?" data-ok="Delete">
                                @csrf @method('DELETE')
                                <button class="act act-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <x-empty :colspan="$isAdmin ? 7 : 6" icon="bi-search" title="No orders found">Try changing or clearing the filters.</x-empty>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-pager :items="$orders" />
</div>
@endsection
