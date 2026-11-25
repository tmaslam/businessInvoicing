@extends('layouts.app')
@section('title', 'Dashboard')
@section('subtitle')
    Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ $user->FName ?: $user->User_Name }} — here's what's happening {{ now()->format('l, j F Y') }}.
@endsection
@section('actions')
    @if($user->hasRole('admin', 'csr', 'qa'))
        <a href="{{ route('orders.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New order</a>
    @endif
    @if($user->hasRole('designer', 'csr'))
        <a href="{{ route('work.create') }}" class="btn {{ $user->hasRole('designer') ? 'btn-primary' : 'btn-light' }}"><i class="bi bi-check2-square"></i> Complete order</a>
    @endif
@endsection
@php($delta = [\App\Http\Controllers\DashboardController::class, 'delta'])
@section('content')

{{-- KPI row --}}
<div class="row g-3 mb-4">
    @if($user->hasRole('admin'))
        <div class="col-sm-6 col-xl-3"><x-stat label="Revenue this month" :value="'$'.number_format($revenueMonth, 2)" icon="bi-currency-dollar" tone="success" :meta="$delta($revenueMonth, $revenueLastMonth) ?? 'Month to date'" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat label="Completed this month" :value="number_format($completedMonth)" icon="bi-check2-circle" tone="primary" :meta="number_format($completedToday).' completed today'" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat label="Open orders" :value="number_format($openCount)" icon="bi-hourglass-split" tone="warning" meta="New + In Progress" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat label="Orders added today" :value="number_format($addedToday)" icon="bi-box-seam" tone="info" :meta="'<a href=\''.route('orders.index').'\'>View orders →</a>'" /></div>
    @elseif($user->hasRole('csr'))
        <div class="col-sm-6 col-xl-3"><x-stat label="Orders I added today" :value="number_format($addedToday)" icon="bi-plus-square" tone="primary" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat label="Orders I added this month" :value="number_format($addedMonth)" icon="bi-calendar3" tone="info" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat label="Open orders" :value="number_format($openCount)" icon="bi-hourglass-split" tone="warning" meta="Waiting for designers" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat label="Completed today (all)" :value="number_format($completedToday)" icon="bi-check2-circle" tone="success" :meta="number_format($myCompletedMonth).' completed by me this month'" /></div>
    @elseif($user->hasRole('qa'))
        <div class="col-sm-6 col-xl-3"><x-stat label="Awaiting QA this month" :value="number_format($awaitingQa)" icon="bi-search" tone="warning" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat label="Verified by me this month" :value="number_format($verifiedMonth)" icon="bi-patch-check" tone="success" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat label="Completed today" :value="number_format($completedToday)" icon="bi-check2-circle" tone="primary" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat label="Open orders" :value="number_format($openCount)" icon="bi-hourglass-split" tone="info" /></div>
    @else
        <div class="col-sm-6 col-xl-3"><x-stat label="Completed today" :value="number_format($myToday)" icon="bi-lightning-charge" tone="primary" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat label="Completed this month" :value="number_format($myMonth)" icon="bi-check2-circle" tone="success" :meta="$delta($myMonth, $myLastMonth) ?? 'Month to date'" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat label="Stitches this month" :value="number_format($myStitches)" icon="bi-bezier2" tone="info" /></div>
        <div class="col-sm-6 col-xl-3"><x-stat label="Open orders available" :value="number_format($openCount)" icon="bi-inbox" tone="warning" :meta="'<a href=\''.route('work.create').'\'>Pick one →</a>'" /></div>
    @endif
</div>

<div class="row g-3 mb-4">
    {{-- Trend chart --}}
    <div class="{{ isset($topCustomers) || isset($topDesigners) ? 'col-xl-8' : 'col-12' }}">
        <div class="card h-100">
            <div class="card-header">
                <div>
                    <h2 class="card-title">{{ $trend['revenue'] ? 'Revenue & orders' : 'Completed orders' }}</h2>
                    <div class="small text-muted">Last 12 months, by completion date</div>
                </div>
            </div>
            <div class="card-body"><div class="chart-box"><canvas id="trendChart"></canvas></div></div>
        </div>
    </div>

    @if(isset($topCustomers))
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header"><h2 class="card-title">Top customers</h2><span class="small text-muted">This month</span></div>
                <div class="table-wrap">
                    <table class="table">
                        <tbody>
                        @forelse($topCustomers as $i => $c)
                            <tr>
                                <td style="width:36px"><span class="avatar avatar-sm" style="background:hsl({{ 230 + $i * 25 }} 70% 60%)">{{ $i + 1 }}</span></td>
                                <td><span class="cell-strong">{{ $c->name }}</span><span class="cell-sub">{{ $c->code }} · {{ number_format($c->orders) }} orders</span></td>
                                <td class="text-end tabular fw-semibold">${{ number_format($c->revenue, 2) }}</td>
                            </tr>
                        @empty
                            <x-empty colspan="3" icon="bi-bar-chart" title="No completed orders yet">Revenue will appear here as orders are completed.</x-empty>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif(isset($topDesigners))
        <div class="col-xl-4">
            @include('partials.top-designers')
        </div>
    @endif
</div>

<div class="row g-3">
    <div class="{{ isset($topCustomers) && isset($topDesigners) ? 'col-xl-8' : 'col-12' }}">
        <div class="card h-100">
            <div class="card-header">
                <h2 class="card-title">
                    @if($user->hasRole('qa')) Waiting for QA
                    @elseif($user->hasRole('designer')) My recent work
                    @elseif($user->hasRole('csr')) Orders I added recently
                    @else Latest orders @endif
                </h2>
                <a href="{{ $user->hasRole('admin', 'csr', 'qa') && ! $user->hasRole('designer') ? route('orders.index') : route('work.index') }}" class="small fw-medium">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="table-wrap">
                <table class="table table-hover">
                    <thead><tr><th>Order</th><th>Design</th><th>Stitches</th><th>Status</th><th class="text-end">Date</th></tr></thead>
                    <tbody>
                    @forelse($recent as $o)
                        <tr>
                            <td class="cell-mono">
                                @if($user->hasRole('admin', 'csr', 'qa'))<a href="{{ route('orders.show', $o->Order_ID) }}">#{{ $o->Order_ID }}</a>@else #{{ $o->Order_ID }} @endif
                            </td>
                            <td><span class="cell-strong">{{ $o->Design_Name }}</span>@if($o->relationLoaded('customer'))<span class="cell-sub">{{ $o->customer?->CompanyName }}</span>@endif</td>
                            <td class="tabular">{{ number_format((int) $o->No_Of_Stiches) }}</td>
                            <td><x-status :status="$o->Order_Status" /></td>
                            <td class="text-end text-muted small text-nowrap">{{ ($o->Completed_Date ?? $o->Effective_Date)?->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <x-empty colspan="5" icon="bi-inbox" title="Nothing to show">New activity will appear here.</x-empty>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @if(isset($topCustomers) && isset($topDesigners))
        <div class="col-xl-4">@include('partials.top-designers')</div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/charts.js') }}?v={{ filemtime(public_path('js/charts.js')) }}"></script>
<script>
    biChart('trendChart', (c) => {
        const trend = @json($trend);
        const datasets = [];
        if (trend.revenue) {
            datasets.push({ type: 'bar', label: 'Revenue', data: trend.revenue, backgroundColor: c.primarySoftSolid, hoverBackgroundColor: c.primary, borderRadius: 6, yAxisID: 'y', order: 2 });
            datasets.push({ type: 'line', label: 'Orders', data: trend.orders, borderColor: c.success, backgroundColor: c.success, yAxisID: 'y1', tension: .35, pointRadius: 3, order: 1 });
        } else {
            datasets.push({ type: 'bar', label: 'Orders', data: trend.orders, backgroundColor: c.primarySoftSolid, hoverBackgroundColor: c.primary, borderRadius: 6, yAxisID: 'y' });
        }
        return {
            data: { labels: trend.labels, datasets },
            options: {
                scales: {
                    y: { beginAtZero: true, ticks: { callback: v => trend.revenue ? '$' + Number(v).toLocaleString() : v } },
                    y1: { display: !!trend.revenue, beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } }
                },
                plugins: { tooltip: { callbacks: { label: (ctx) => ctx.dataset.label + ': ' + (ctx.dataset.label === 'Revenue' ? '$' + ctx.parsed.y.toLocaleString(undefined, {minimumFractionDigits: 2}) : ctx.parsed.y.toLocaleString()) } } }
            }
        };
    });
</script>
@endpush
