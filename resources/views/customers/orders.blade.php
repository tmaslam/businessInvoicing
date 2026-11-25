@extends('layouts.app')
@section('title', 'Completed orders')
@section('subtitle', $customer->CompanyName.' · '.$customer->ContactPerson)
@section('actions')
    <a href="{{ route('customers.index', $range->query()) }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Customers</a>
    <a href="{{ route('customers.invoice', ['customer' => $customer->Customer_ID] + $range->query()) }}" class="btn btn-primary"><i class="bi bi-file-earmark-spreadsheet"></i> Download invoice</a>
@endsection
@section('content')
@php($total = $orders->sum(fn ($o) => (float) $o->Order_Price))
<div class="row g-3 mb-3">
    <div class="col-sm-4"><x-stat label="Orders" :value="number_format($orders->count())" icon="bi-box-seam" /></div>
    <div class="col-sm-4"><x-stat label="Stitches" :value="number_format($orders->sum(fn ($o) => (int) $o->No_Of_Stiches))" icon="bi-bezier2" tone="info" /></div>
    <div class="col-sm-4"><x-stat label="Amount" :value="'$'.number_format($total, 2)" icon="bi-currency-dollar" tone="success" /></div>
</div>
<div class="card">
    @include('partials.date-range')
    <div class="table-wrap">
        <table class="table table-hover">
            <thead><tr><th>Order</th><th>Design</th><th class="text-end">Stitches</th><th class="text-end">Price</th><th>Added</th><th>Completed</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($orders as $o)
                <tr>
                    <td class="cell-mono"><a href="{{ route('orders.show', $o->Order_ID) }}">#{{ $o->Order_ID }}</a></td>
                    <td class="cell-strong">{{ $o->Design_Name }}</td>
                    <td class="text-end tabular">{{ number_format((int) $o->No_Of_Stiches) }}</td>
                    <td class="text-end tabular">${{ number_format((float) $o->Order_Price, 2) }}</td>
                    <td class="small text-muted text-nowrap">{{ $o->Effective_Date?->format('d M Y, g:i A') }}</td>
                    <td class="small text-muted text-nowrap">{{ $o->Completed_Date?->format('d M Y, g:i A') }}</td>
                    <td><x-status :status="$o->Order_Status" /></td>
                </tr>
            @empty
                <x-empty colspan="7" icon="bi-calendar-x" title="No completed orders in this period">Try a wider date range.</x-empty>
            @endforelse
            </tbody>
            @if($orders->isNotEmpty())
                <tfoot><tr><td colspan="3" class="text-end">Total</td><td class="text-end tabular">${{ number_format($total, 2) }}</td><td colspan="3"></td></tr></tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
