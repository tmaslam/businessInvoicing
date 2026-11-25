@extends('layouts.app')
@section('title', $user->full_name.' — Progress Chart')
@section('actions')
    <a href="{{ route('users.index', $range->query()) }}" class="btn btn-outline-secondary btn-sm">Back</a>
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        @include('partials.date-range')
        @if($data === null)
            <div class="alert alert-warning">Report duration can not be greater than 12 months.</div>
        @elseif(array_sum($data['designs']) === 0)
            <div class="alert alert-info">No record found.</div>
        @else
            <div style="height:420px"><canvas id="chart"></canvas></div>
        @endif
    </div>
</div>
@endsection
@if($data && array_sum($data['designs']) > 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const datasets = [
        { type: 'bar', label: 'Total Designs', data: @json($data['designs']), backgroundColor: '#60a5fa', yAxisID: 'y' },
        { type: 'bar', label: 'Total Mistakes', data: @json($data['mistakes']), backgroundColor: '#f59e0b', yAxisID: 'y' },
    ];
    const revenue = @json($data['revenue']);
    if (revenue) {
        datasets.push({ type: 'line', label: 'Total Revenue', data: revenue, borderColor: '#16a34a', backgroundColor: '#16a34a', yAxisID: 'y1', tension: .25 });
    }
    new Chart(document.getElementById('chart'), {
        data: { labels: @json($data['labels']), datasets },
        options: {
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Designs' } },
                y1: { display: !!revenue, beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Revenue' } }
            }
        }
    });
</script>
@endpush
@endif
