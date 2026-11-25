@extends('layouts.app')
@section('title', 'Customer Listing')
@section('actions')
    <a href="{{ route('customers.chart', ['customer' => 0] + $range->query()) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-bar-chart"></i> Overall Revenue</a>
    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Customer</a>
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        @include('partials.date-range')
        <p class="small text-muted mb-2">The date range above is used by the detail, invoice, chart, lock and pay actions.</p>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm">
                <thead><tr><th>Customer Code</th><th>Company Name</th><th>Contact Person</th><th>Email</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse($customers as $c)
                    @php($p = ['customer' => $c->Customer_ID] + $range->query())
                    <tr>
                        <td>{{ $c->Customer_Unique_ID }}</td>
                        <td>{{ $c->CompanyName }}</td>
                        <td>{{ $c->ContactPerson }}</td>
                        <td>{{ $c->Email }}</td>
                        <td class="text-end actions text-nowrap">
                            <a class="btn btn-sm btn-outline-secondary" title="Completed orders" href="{{ route('customers.orders', $p) }}"><i class="bi bi-list-ul"></i></a>
                            <a class="btn btn-sm btn-outline-success" title="Invoice (Excel)" href="{{ route('customers.invoice', $p) }}"><i class="bi bi-file-earmark-excel"></i></a>
                            <a class="btn btn-sm btn-outline-info" title="Revenue chart" href="{{ route('customers.chart', $p) }}"><i class="bi bi-bar-chart"></i></a>
                            <form class="d-inline" method="POST" action="{{ route('customers.lock', $c->Customer_ID) }}" data-confirm="Lock this customer's orders completed from {{ $range->from->toDateString() }} to {{ $range->to->toDateString() }}?">
                                @csrf <input type="hidden" name="from" value="{{ $range->from->toDateString() }}"><input type="hidden" name="to" value="{{ $range->to->toDateString() }}">
                                <button class="btn btn-sm btn-outline-warning" title="Lock orders"><i class="bi bi-lock"></i></button>
                            </form>
                            <form class="d-inline" method="POST" action="{{ route('customers.pay', $c->Customer_ID) }}" data-confirm="Mark this customer's orders completed from {{ $range->from->toDateString() }} to {{ $range->to->toDateString() }} as paid?">
                                @csrf <input type="hidden" name="from" value="{{ $range->from->toDateString() }}"><input type="hidden" name="to" value="{{ $range->to->toDateString() }}">
                                <button class="btn btn-sm btn-outline-success" title="Pay orders"><i class="bi bi-cash-coin"></i></button>
                            </form>
                            <a class="btn btn-sm btn-outline-dark" title="Price ranges" href="{{ route('customers.prices.index', $c->Customer_ID) }}"><i class="bi bi-tags"></i></a>
                            <a class="btn btn-sm btn-outline-primary" title="Edit" href="{{ route('customers.edit', $c->Customer_ID) }}"><i class="bi bi-pencil"></i></a>
                            <form class="d-inline" method="POST" action="{{ route('customers.destroy', $c->Customer_ID) }}" data-confirm="Are you sure to delete this?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">No record found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $customers->links() }}
    </div>
</div>
@endsection
