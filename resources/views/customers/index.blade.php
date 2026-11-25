@extends('layouts.app')
@section('title', 'Customers')
@section('subtitle', 'Manage customers, pricing, invoices and payments.')
@section('actions')
    <a href="{{ route('customers.chart', ['customer' => 0] + $range->query()) }}" class="btn btn-light"><i class="bi bi-bar-chart-line"></i> Overall revenue</a>
    <a href="{{ route('customers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add customer</a>
@endsection
@section('content')
<div class="card">
    @include('partials.date-range')
    <div class="filter-note"><i class="bi bi-info-circle"></i> Invoice, orders, chart, lock and pay actions use the period <strong class="mx-1">{{ $range->from->format('d M Y') }} – {{ $range->to->format('d M Y') }}</strong></div>
    <div class="table-wrap">
        <table class="table table-hover">
            <thead><tr><th>Code</th><th>Company</th><th>Email</th><th>Website</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($customers as $c)
                @php($p = ['customer' => $c->Customer_ID] + $range->query())
                <tr>
                    <td><span class="pill no-dot tone-neutral cell-mono">{{ $c->Customer_Unique_ID }}</span></td>
                    <td><span class="cell-strong">{{ $c->CompanyName }}</span><span class="cell-sub">{{ $c->ContactPerson }}</span></td>
                    <td class="text-muted">{{ $c->Email }}</td>
                    <td class="small">{{ config('binvoice.websites')[(int) $c->Website_ID]['name'] ?? '—' }}</td>
                    <td>
                        @if((int) $c->Is_Active === 2)<span class="pill tone-neutral">Inactive</span>@else<span class="pill tone-success">Active</span>@endif
                    </td>
                    <td class="text-end">
                        <div class="row-actions">
                            <a class="act act-primary" title="Completed orders" href="{{ route('customers.orders', $p) }}"><i class="bi bi-list-check"></i></a>
                            <a class="act act-success" title="Download invoice (Excel)" href="{{ route('customers.invoice', $p) }}"><i class="bi bi-file-earmark-spreadsheet"></i></a>
                            <a class="act act-info" title="Revenue chart" href="{{ route('customers.chart', $p) }}"><i class="bi bi-bar-chart"></i></a>
                            <a class="act act-primary" title="Edit" href="{{ route('customers.edit', $c->Customer_ID) }}"><i class="bi bi-pencil"></i></a>
                            <div class="dropdown">
                                <button class="act" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-label="More actions"><i class="bi bi-three-dots-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('customers.prices.index', $c->Customer_ID) }}"><i class="bi bi-tags"></i> Price ranges</a></li>
                                    <li>
                                        <form method="POST" action="{{ route('customers.lock', $c->Customer_ID) }}" data-confirm="All orders of {{ $c->CompanyName }} completed between {{ $range->from->format('d M Y') }} and {{ $range->to->format('d M Y') }} will be locked." data-title="Lock orders?" data-tone="warning" data-icon="bi-lock" data-ok="Lock orders">
                                            @csrf <input type="hidden" name="from" value="{{ $range->from->toDateString() }}"><input type="hidden" name="to" value="{{ $range->to->toDateString() }}">
                                            <button class="dropdown-item"><i class="bi bi-lock"></i> Lock orders in period</button>
                                        </form>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('customers.pay', $c->Customer_ID) }}" data-confirm="All orders of {{ $c->CompanyName }} completed between {{ $range->from->format('d M Y') }} and {{ $range->to->format('d M Y') }} will be marked as paid." data-title="Mark as paid?" data-tone="success" data-icon="bi-cash-coin" data-ok="Mark as paid">
                                            @csrf <input type="hidden" name="from" value="{{ $range->from->toDateString() }}"><input type="hidden" name="to" value="{{ $range->to->toDateString() }}">
                                            <button class="dropdown-item"><i class="bi bi-cash-coin"></i> Mark period as paid</button>
                                        </form>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('customers.destroy', $c->Customer_ID) }}" data-confirm="{{ $c->CompanyName }} will be removed from all lists. Existing orders are kept." data-title="Delete customer?" data-ok="Delete">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger"><i class="bi bi-trash"></i> Delete</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <x-empty colspan="6" icon="bi-buildings" title="No customers yet"><a href="{{ route('customers.create') }}">Add your first customer</a></x-empty>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-pager :items="$customers" />
</div>
@endsection
