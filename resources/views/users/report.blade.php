@extends('layouts.app')
@section('title', 'Progress report')
@section('subtitle', $user->full_name.' · '.$user->role_name)
@section('actions')
    <a href="{{ route('users.index', $range->query()) }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Users</a>
    <a href="{{ route('users.chart', ['user' => $user->User_Id] + $range->query()) }}" class="btn btn-light"><i class="bi bi-bar-chart"></i> Chart</a>
    <a href="{{ route('users.report.excel', ['user' => $user->User_Id] + $range->query()) }}" class="btn btn-primary"><i class="bi bi-file-earmark-spreadsheet"></i> Export Excel</a>
@endsection
@section('content')
@php($days = max(1, $range->from->diffInDays($range->to->startOfDay()) + 1))
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-lg-3"><x-stat label="Designs" :value="number_format($orders->count())" icon="bi-check2-circle" /></div>
    <div class="col-sm-6 col-lg-3"><x-stat label="Stitches" :value="number_format($orders->sum(fn ($o) => (int) $o->No_Of_Stiches))" icon="bi-bezier2" tone="info" /></div>
    <div class="col-sm-6 col-lg-3"><x-stat label="Per day" :value="number_format($orders->count() / $days, 1)" icon="bi-speedometer2" tone="warning" :meta="$days.' days in period'" /></div>
    @if($showPrices)
        <div class="col-sm-6 col-lg-3"><x-stat label="Value" :value="'$'.number_format($orders->sum(fn ($o) => (float) $o->Order_Price), 2)" icon="bi-currency-dollar" tone="success" /></div>
    @endif
</div>
<div class="card">
    @include('partials.date-range')
    <div class="table-wrap">
        <table class="table table-hover">
            <thead><tr><th>Completed</th><th>Order</th><th>Design</th><th class="text-end">Stitches</th>@if($showPrices)<th class="text-end">Price</th>@endif</tr></thead>
            <tbody>
            @forelse($orders as $o)
                <tr>
                    <td class="small text-muted text-nowrap">{{ $o->Completed_Date?->format('d M Y') }}</td>
                    <td class="cell-mono">#{{ $o->Order_ID }}</td>
                    <td class="cell-strong">{{ $o->Design_Name }}</td>
                    <td class="text-end tabular">{{ number_format((int) $o->No_Of_Stiches) }}</td>
                    @if($showPrices)<td class="text-end tabular">${{ number_format((float) $o->Order_Price, 2) }}</td>@endif
                </tr>
            @empty
                <x-empty colspan="5" icon="bi-calendar-x" title="No completed designs in this period">Try a wider date range.</x-empty>
            @endforelse
            </tbody>
            @if($showPrices && $orders->isNotEmpty())
                <tfoot><tr><td colspan="4" class="text-end">Grand total</td><td class="text-end tabular">${{ number_format($orders->sum(fn ($o) => (float) $o->Order_Price), 2) }}</td></tr></tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
