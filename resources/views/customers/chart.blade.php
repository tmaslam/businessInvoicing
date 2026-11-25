@extends('layouts.app')
@section('title', ($customer ? $customer->ContactPerson : 'Overall').' Monthly Revenue')
@section('actions')
    <a href="{{ route('customers.index', $range->query()) }}" class="btn btn-outline-secondary btn-sm">Back</a>
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        @include('partials.date-range', ['keep' => ['customer' => $customer?->Customer_ID ?? 0]])
        @if($data === null)
            <div class="alert alert-warning">Report duration can not be greater than 12 months.</div>
        @elseif(array_sum($data['revenue']) == 0)
            <div class="alert alert-info">No record found.</div>
        @else
            <div style="height:420px"><canvas id="chart"></canvas></div>
        @endif
    </div>
</div>
@endsection
@if($data && array_sum($data['revenue']) > 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    new Chart(document.getElementById('chart'), {
        type: 'bar',
        data: {
            labels: @json($data['labels']),
            datasets: [{ label: 'Revenue', data: @json($data['revenue']), backgroundColor: '#3b82f6' }]
        },
        options: {
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, title: { display: true, text: 'Revenue' }, ticks: { callback: v => '$' + v.toLocaleString() } },
                      x: { title: { display: true, text: 'Month' } } },
            plugins: { tooltip: { callbacks: { label: c => '$' + c.parsed.y.toLocaleString(undefined, {minimumFractionDigits: 2}) } } }
        }
    });
</script>
@endpush
@endif
