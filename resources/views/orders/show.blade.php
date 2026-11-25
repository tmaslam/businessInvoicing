@extends('layouts.app')
@section('title', 'Order #'.$order->Order_ID)
@section('actions')
    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
@endsection
@section('content')
<div class="card" style="max-width: 900px">
    <div class="card-body">
        <table class="table table-bordered mb-3">
            <tr><th>Order ID</th><td>{{ $order->Order_ID }}</td><th>Design Name</th><td>{{ $order->Design_Name }}</td></tr>
            <tr><th>No of Stitches</th><td>{{ $order->No_Of_Stiches ?: '0' }}</td><th>Order Price</th><td>{{ auth()->user()->isAdmin() ? number_format((float) $order->Order_Price, 2) : '—' }}</td></tr>
            <tr><th>Assigned By</th><td>{{ $order->assigner?->full_name ?? 'N/A' }}</td><th>Completed By</th><td>{{ $order->completer?->full_name ?? 'N/A' }}</td></tr>
            <tr><th>Company</th><td>{{ $order->customer?->CompanyName }}</td><th>Contact Person</th><td>{{ $order->customer?->ContactPerson }}</td></tr>
            <tr><th>Status</th><td>{{ $order->Order_Status }}</td><th>Date Added</th><td>{{ $order->Effective_Date?->format('d-M-y g:i A') ?? 'N/A' }}</td></tr>
            <tr><th>Completed Date</th><td>{{ $order->Completed_Date?->format('d-M-y g:i A') ?? 'N/A' }}</td><th>Completion Duration</th><td>{{ $order->completionDuration() }}</td></tr>
            <tr><th>Edit Code</th><td>{{ $order->editCode?->Narrative ?? '—' }}</td><th>QA</th><td>{{ $order->qaUser?->full_name ?? 'Not verified' }}</td></tr>
        </table>
        @if(auth()->user()->hasRole('qa'))
            <form method="POST" action="{{ route('orders.verify', $order->Order_ID) }}">
                @csrf
                <button class="btn btn-success" @disabled($order->isVerified())><i class="bi bi-patch-check"></i> {{ $order->isVerified() ? 'Verified' : 'Verify' }}</button>
            </form>
        @endif
    </div>
</div>
@endsection
