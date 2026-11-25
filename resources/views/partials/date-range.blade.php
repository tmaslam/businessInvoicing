{{-- Date range filter bar with quick presets. Expects $range; optional $keep (extra query params). --}}
@php
    $keep = $keep ?? [];
    $presets = [
        'This month' => [now()->startOfMonth(), now()],
        'Last month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
        'Last 30 days' => [now()->subDays(29), now()],
        'Last 3 months' => [now()->subMonthsNoOverflow(3)->startOfMonth(), now()],
        'This year' => [now()->startOfYear(), now()],
    ];
    $from = $range->from->toDateString();
    $to = $range->to->toDateString();
@endphp
<form method="GET" class="filter-bar">
    @foreach($keep as $k => $v)
        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
    @endforeach
    <div>
        <label class="form-label">From</label>
        <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
    </div>
    <div>
        <label class="form-label">To</label>
        <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
    </div>
    {{ $extra ?? '' }}
    <div><button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Apply</button></div>
    <div class="d-flex flex-wrap gap-1 ms-lg-auto">
        @foreach($presets as $label => [$a, $b])
            @php($active = $a->toDateString() === $from && $b->toDateString() === $to)
            <a href="{{ request()->fullUrlWithQuery(['from' => $a->toDateString(), 'to' => $b->toDateString(), 'page' => null]) }}"
               class="btn btn-sm {{ $active ? 'btn-primary' : 'btn-light' }}">{{ $label }}</a>
        @endforeach
    </div>
</form>
