{{-- Date From / Date To filter used by report actions on the page. --}}
<form method="GET" class="row g-2 align-items-end mb-3" id="rangeForm">
    @foreach(($keep ?? []) as $k => $v)
        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
    @endforeach
    <div class="col-auto">
        <label class="form-label small mb-0">Date From</label>
        <input type="date" name="from" class="form-control form-control-sm" value="{{ $range->from->toDateString() }}">
    </div>
    <div class="col-auto">
        <label class="form-label small mb-0">Date To</label>
        <input type="date" name="to" class="form-control form-control-sm" value="{{ $range->to->toDateString() }}">
    </div>
    <div class="col-auto">
        <button class="btn btn-sm btn-outline-primary"><i class="bi bi-funnel"></i> Apply</button>
    </div>
    {{ $slot ?? '' }}
</form>
