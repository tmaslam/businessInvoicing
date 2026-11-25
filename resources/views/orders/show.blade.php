@extends('layouts.app')
@section('title', 'Order #'.$order->Order_ID)
@section('subtitle', $order->Design_Name)
@section('actions')
    <a href="{{ route('orders.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Orders</a>
    <a href="{{ route('orders.edit', $order->Order_ID) }}" class="btn btn-light"><i class="bi bi-pencil"></i> Edit</a>
    @if(auth()->user()->hasRole('qa'))
        @if($order->isVerified())
            <span class="btn btn-soft-success disabled"><i class="bi bi-patch-check-fill"></i> Verified</span>
        @else
            <form method="POST" action="{{ route('orders.verify', $order->Order_ID) }}">@csrf
                <button class="btn btn-primary"><i class="bi bi-patch-check"></i> Mark as verified</button>
            </form>
        @endif
    @endif
@endsection
@section('content')
@php($isAdmin = auth()->user()->isAdmin())
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header">
                <h2 class="card-title">Order details</h2>
                <x-status :status="$order->Order_Status" />
            </div>
            <div class="card-body">
                <dl class="dl-grid">
                    <div><dt>Design name</dt><dd>{{ $order->Design_Name }}</dd></div>
                    <div><dt>Stitches</dt><dd class="tabular">{{ number_format((int) $order->No_Of_Stiches) }}</dd></div>
                    @if($isAdmin)<div><dt>Price</dt><dd class="tabular">${{ number_format((float) $order->Order_Price, 2) }}</dd></div>@endif
                    <div><dt>Edit code</dt><dd>{{ $order->editCode?->Narrative ?? '—' }}</dd></div>
                    <div><dt>Company</dt><dd>{{ $order->customer?->CompanyName ?? '—' }}</dd></div>
                    <div><dt>Contact person</dt><dd>{{ $order->customer?->ContactPerson ?? '—' }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h2 class="card-title">Timeline</h2></div>
            <div class="card-body">
                @php($steps = [
                    ['Added', $order->Effective_Date, $order->assigner?->full_name, 'bi-plus-lg', 'primary'],
                    ['Completed', $order->Completed_Date, $order->completer?->full_name, 'bi-check2', 'success'],
                    ['QA verified', null, $order->qaUser?->full_name, 'bi-patch-check', 'info'],
                ])
                @foreach($steps as [$label, $date, $who, $icon, $tone])
                    @php($done = $date || $who)
                    <div class="d-flex gap-3 {{ $loop->last ? '' : 'mb-3' }}" style="opacity: {{ $done ? 1 : .45 }}">
                        <span class="stat-icon position-static tone-{{ $done ? $tone : 'neutral' }}" style="width:34px;height:34px;flex-shrink:0"><i class="bi {{ $icon }}"></i></span>
                        <div>
                            <div class="fw-semibold">{{ $label }}</div>
                            <div class="small text-muted">
                                @if($done)
                                    {{ $who ?? '—' }}@if($date) · {{ $date->format('d M Y, g:i A') }}@endif
                                @else
                                    Pending
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
                <hr>
                <div class="d-flex justify-content-between small"><span class="text-muted">Completion time</span><span class="fw-semibold">{{ $order->completionDuration() }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
