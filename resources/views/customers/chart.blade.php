@extends('layouts.app')
@section('title', 'Revenue report')
@section('subtitle', ($customer ? $customer->CompanyName.' · '.$customer->ContactPerson : 'All customers').' — monthly revenue of completed orders')
@section('actions')
    <a href="{{ route('customers.index', $range->query()) }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Customers</a>
@endsection
@section('content')
@php($has = $data && array_sum($data['revenue']) > 0)
@if($has)
    @php($nonZero = array_filter($data['revenue']))
    <div class="row g-3 mb-3">
        <div class="col-sm-4"><x-stat label="Total revenue" :value="'$'.number_format(array_sum($data['revenue']), 2)" icon="bi-currency-dollar" tone="success" /></div>
        <div class="col-sm-4"><x-stat label="Monthly average" :value="'$'.number_format(array_sum($data['revenue']) / max(1, count($nonZero)), 2)" icon="bi-graph-up" /></div>
        <div class="col-sm-4"><x-stat label="Best month" :value="$data['labels'][array_search(max($data['revenue']), $data['revenue'])]" icon="bi-trophy" tone="warning" :meta="'$'.number_format(max($data['revenue']), 2)" /></div>
    </div>
@endif
<div class="card">
    @include('partials.date-range', ['keep' => ['customer' => $customer?->Customer_ID ?? 0]])
    <div class="card-body">
        @if($data === null)
            <x-empty icon="bi-calendar-range" title="Period too long">Please choose a period of 12 months or less.</x-empty>
        @elseif(! $has)
            <x-empty icon="bi-bar-chart" title="No revenue in this period">Try a different date range.</x-empty>
        @else
            <div class="chart-box lg"><canvas id="chart"></canvas></div>
        @endif
    </div>
</div>
@endsection
@if($has)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/charts.js') }}?v={{ filemtime(public_path('js/charts.js')) }}"></script>
<script>
    biChart('chart', (c) => ({
        data: { labels: @json($data['labels']), datasets: [{ label: 'Revenue', data: @json($data['revenue']), backgroundColor: c.primarySoftSolid, hoverBackgroundColor: c.primary, borderRadius: 8, maxBarThickness: 48 }] },
        options: {
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: x => '$' + x.parsed.y.toLocaleString(undefined, {minimumFractionDigits: 2}) } } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => '$' + Number(v).toLocaleString() } } }
        }
    }));
</script>
@endpush
@endif
