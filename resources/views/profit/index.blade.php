@extends('layouts.app')
@section('title', 'Profit distribution')
@section('subtitle', 'Business year 1 Jan – 31 Dec '.$year.'. Profit counts orders marked as paid.')
@section('actions')
    <form method="GET" class="d-flex align-items-center gap-2">
        <label class="small text-muted text-nowrap" for="year">Business year</label>
        <select name="year" id="year" class="form-select" onchange="this.form.submit()" style="width:120px">
            @foreach($years as $y)
                <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
            @endforeach
        </select>
    </form>
@endsection
@section('content')
@php($show = fn ($v) => '$'.number_format($totalProfit == 0 ? 0 : $v, 2))
<div class="row g-3 mb-4">
    <div class="col-md-4"><x-stat label="Total profit (paid orders)" :value="'$'.number_format($totalProfit, 2)" icon="bi-cash-stack" tone="primary" /></div>
    <div class="col-md-4"><x-stat label="Total expenses" :value="'$'.number_format($totalExpense, 2)" icon="bi-wallet2" tone="danger" :meta="'<a href=\''.route('expenses.index').'\'>Manage expenses →</a>'" /></div>
    <div class="col-md-4"><x-stat label="Earned profit" :value="$show($earned)" icon="bi-graph-up-arrow" :tone="$earned >= 0 ? 'success' : 'danger'" meta="Profit − expenses" /></div>
</div>

<div class="card">
    <div class="card-header"><h2 class="card-title">Distribution</h2></div>
    <div class="card-body">
        @if($totalProfit == 0)
            <x-empty icon="bi-pie-chart" title="No paid orders in {{ $year }}">Mark a customer's orders as paid from the Customers page to see the distribution.</x-empty>
        @else
            <div class="row g-4 align-items-center">
                <div class="col-md-5"><div class="chart-box" style="height:260px"><canvas id="split"></canvas></div></div>
                <div class="col-md-7">
                    @foreach([['Owner', $ownerPercent, $owner, 'var(--bi-primary)'], ['Employees', 100 - $ownerPercent, $employee, 'var(--bi-success)']] as [$label, $pct, $amount, $color])
                        <div class="d-flex align-items-center justify-content-between py-3 {{ $loop->first ? 'border-bottom' : '' }}">
                            <div><span class="legend-dot" style="background: {{ $color }}"></span><span class="fw-semibold">{{ $label }}</span> <span class="text-muted small">· {{ $pct }}%</span></div>
                            <div class="fs-5 fw-bold tabular">${{ number_format($amount, 2) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
@if($totalProfit != 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/charts.js') }}?v={{ filemtime(public_path('js/charts.js')) }}"></script>
<script>
    biChart('split', (c) => ({
        type: 'doughnut',
        data: { labels: ['Owner', 'Employees'], datasets: [{ data: [{{ max(0, round($owner, 2)) }}, {{ max(0, round($employee, 2)) }}], backgroundColor: [c.primary, c.success], borderColor: c.surface, borderWidth: 3 }] },
        options: { cutout: '68%', scales: { x: { display: false }, y: { display: false } }, plugins: { legend: { display: false } } }
    }));
</script>
@endpush
@endif
