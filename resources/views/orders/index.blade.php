@extends('layouts.app')
@section('title', 'Order Listing')
@section('actions')
    <a href="{{ route('orders.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Order</a>
@endsection
@section('content')
@php($isAdmin = auth()->user()->isAdmin())
<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-sm-6 col-md-2">
                <label class="form-label small mb-0">Order ID</label>
                <input name="order_id" type="number" class="form-control form-control-sm" value="{{ $filters['order_id'] ?? '' }}">
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label small mb-0">Customer</label>
                <select name="customer" class="form-select form-select-sm">
                    <option value="">Please Select</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->Customer_ID }}" @selected(($filters['customer'] ?? null) == $c->Customer_ID)>{{ $c->CompanyName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label small mb-0">Design Name</label>
                <input name="design" class="form-control form-control-sm" value="{{ $filters['design'] ?? '' }}">
            </div>
            <div class="col-sm-6 col-md-2">
                <label class="form-label small mb-0">Order Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Please Select</option>
                    @foreach($statusOptions as $s)
                        <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Search</button>
                <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
        @if(! array_filter($filters))
            <p class="small text-muted mb-2">Showing open, completed and locked orders added since {{ now()->subMonthNoOverflow()->startOfMonth()->format('d M Y') }}. Use the search to see older or archived orders.</p>
        @endif
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm">
                <thead>
                <tr>
                    <th>Order Id</th><th>Customer</th><th>Design Name</th><th>No Of Stitches</th>
                    @if($isAdmin)<th>Price</th>@endif
                    <th>Status</th><th>Date Added</th><th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($orders as $o)
                    <tr>
                        <td>{{ $o->Order_ID }}</td>
                        <td>{{ $o->Customer_Name }}</td>
                        <td>{{ $o->Design_Name }}</td>
                        <td>{{ $o->No_Of_Stiches ?: '0' }}</td>
                        @if($isAdmin)<td>{{ number_format((float) $o->Order_Price, 2) }}</td>@endif
                        <td><span class="badge text-bg-{{ ['NEW' => 'primary', 'In Progress' => 'warning', 'Completed' => 'success', 'Lock' => 'secondary', 'Archive' => 'dark'][$o->Order_Status] ?? 'light' }}">{{ $o->Order_Status ?: 'New' }}</span></td>
                        <td class="text-nowrap">{{ $o->Effective_Date?->format('d-M-y H:i:s') }}</td>
                        <td class="text-end actions text-nowrap">
                            @if($o->isVerified())<i class="bi bi-patch-check-fill text-success me-1" title="QA verified"></i>@endif
                            <a class="btn btn-sm btn-outline-secondary" title="View detail" href="{{ route('orders.show', $o->Order_ID) }}"><i class="bi bi-eye"></i></a>
                            <a class="btn btn-sm btn-outline-primary" title="Edit" href="{{ route('orders.edit', $o->Order_ID) }}"><i class="bi bi-pencil"></i></a>
                            <form class="d-inline" method="POST" action="{{ route('orders.destroy', $o->Order_ID) }}" data-confirm="Are you sure to delete this?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">No record found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $orders->links() }}
    </div>
</div>
@endsection
