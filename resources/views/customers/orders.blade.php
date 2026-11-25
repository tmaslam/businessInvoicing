@extends('layouts.app')
@section('title', $customer->ContactPerson.' — Completed Orders')
@section('actions')
    <a href="{{ route('customers.invoice', ['customer' => $customer->Customer_ID] + $range->query()) }}" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-excel"></i> Invoice</a>
    <a href="{{ route('customers.index', $range->query()) }}" class="btn btn-outline-secondary btn-sm">Back</a>
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        @include('partials.date-range')
        <div class="table-responsive">
            <table class="table table-striped table-sm text-center">
                <thead><tr><th>Order Id</th><th>Design Name</th><th>No Of Stitches</th><th>Price</th><th>Date Added</th><th>Completed</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($orders as $o)
                    <tr>
                        <td>{{ $o->Order_ID }}</td>
                        <td>{{ $o->Design_Name }}</td>
                        <td>{{ $o->No_Of_Stiches ?: '0' }}</td>
                        <td>{{ number_format((float) $o->Order_Price, 2) }}</td>
                        <td>{{ $o->Effective_Date?->format('d-M-y g:i A') }}</td>
                        <td>{{ $o->Completed_Date?->format('d-M-y g:i A') }}</td>
                        <td>{{ $o->Order_Status ?: 'New' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-danger fw-bold">No record found.</td></tr>
                @endforelse
                </tbody>
                @if($orders->isNotEmpty())
                    <tfoot><tr class="fw-bold"><td colspan="3" class="text-end">Total ({{ $orders->count() }} orders)</td><td>{{ number_format($orders->sum(fn ($o) => (float) $o->Order_Price), 2) }}</td><td colspan="3"></td></tr></tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
