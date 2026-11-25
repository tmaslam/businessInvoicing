@extends('layouts.app')
@section('title', 'Progress chart')
@section('subtitle', $user->full_name.' · designs, mistakes'.(auth()->user()->isAdmin() ? ' and revenue' : '').' per month')
@section('actions')
    <a href="{{ route('users.index', $range->query()) }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Users</a>
    <a href="{{ route('users.report', ['user' => $user->User_Id] + $range->query()) }}" class="btn btn-light"><i class="bi bi-list-check"></i> Report</a>
@endsection
@section('content')
@php($has = $data && array_sum($data['designs']) > 0)
@if($has)
    @php($designs = array_sum($data['designs']))
    @php($mistakes = array_sum($data['mistakes']))
    <div class="row g-3 mb-3">
        <div class="col-sm-4"><x-stat label="Designs" :value="number_format($designs)" icon="bi-check2-circle" /></div>
        <div class="col-sm-4"><x-stat label="Mistakes" :value="number_format($mistakes)" icon="bi-bug" tone="danger" :meta="number_format($designs ? $mistakes / $designs * 100 : 0, 1).'% error rate'" /></div>
        @if($data['revenue'])
            <div class="col-sm-4"><x-stat label="Revenue" :value="'$'.number_format(array_sum($data['revenue']), 2)" icon="bi-currency-dollar" tone="success" /></div>
        @endif
    </div>
@endif
<div class="card">
    @include('partials.date-range')
    <div class="card-body">
        @if($data === null)
            <x-empty icon="bi-calendar-range" title="Period too long">Please choose a period of 12 months or less.</x-empty>
        @elseif(! $has)
            <x-empty icon="bi-bar-chart" title="No completed designs in this period">Try a different date range.</x-empty>
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
    biChart('chart', (c) => {
        const revenue = @json($data['revenue']);
        const datasets = [
            { type: 'bar', label: 'Designs', data: @json($data['designs']), backgroundColor: c.primarySoftSolid, borderRadius: 6, yAxisID: 'y', order: 2 },
            { type: 'bar', label: 'Mistakes', data: @json($data['mistakes']), backgroundColor: c.danger, borderRadius: 6, yAxisID: 'y', order: 3 },
        ];
        if (revenue) datasets.push({ type: 'line', label: 'Revenue', data: revenue, borderColor: c.success, backgroundColor: c.success, yAxisID: 'y1', tension: .35, order: 1 });
        return {
            data: { labels: @json($data['labels']), datasets },
            options: { scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Designs' } },
                y1: { display: !!revenue, beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { callback: v => '$' + Number(v).toLocaleString() } }
            } }
        };
    });
</script>
@endpush
@endif
