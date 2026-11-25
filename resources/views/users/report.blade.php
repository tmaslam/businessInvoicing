@extends('layouts.app')
@section('title', $user->full_name.' — Progress Report')
@section('actions')
    <a href="{{ route('users.report.excel', ['user' => $user->User_Id] + $range->query()) }}" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-excel"></i> Excel</a>
    <a href="{{ route('users.chart', ['user' => $user->User_Id] + $range->query()) }}" class="btn btn-outline-info btn-sm"><i class="bi bi-bar-chart"></i> Chart</a>
    <a href="{{ route('users.index', $range->query()) }}" class="btn btn-outline-secondary btn-sm">Back</a>
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        @include('partials.date-range')
        <p class="mb-2"><strong>Total: {{ $orders->count() }}</strong> completed designs ({{ $user->role_name }})</p>
        <div class="table-responsive">
            <table class="table table-striped table-sm text-center">
                <thead><tr><th>Date</th><th>Order Id</th><th>Design Name</th><th>No Of Stitches</th>@if($showPrices)<th>Price</th>@endif</tr></thead>
                <tbody>
                @forelse($orders as $o)
                    <tr>
                        <td>{{ $o->Completed_Date?->format('d-m-Y') }}</td>
                        <td>{{ $o->Order_ID }}</td>
                        <td>{{ $o->Design_Name }}</td>
                        <td>{{ (int) $o->No_Of_Stiches }}</td>
                        @if($showPrices)<td>{{ number_format((float) $o->Order_Price, 2) }}</td>@endif
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-danger fw-bold">No record found.</td></tr>
                @endforelse
                </tbody>
                @if($showPrices && $orders->isNotEmpty())
                    <tfoot><tr class="fw-bold"><td colspan="4" class="text-end">Grand Total</td><td>{{ number_format($orders->sum(fn ($o) => (float) $o->Order_Price), 2) }}</td></tr></tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
