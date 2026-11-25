@extends('layouts.app')
@section('title', 'Completed orders')
@section('subtitle', $canEditStitches ? 'All completed designs. Click a stitch count to correct it.' : 'Designs you have completed.')
@section('actions')
    @if(auth()->user()->hasRole('designer', 'csr'))
        <a href="{{ route('work.create') }}" class="btn btn-primary" data-shortcut="n" title="Shortcut: N"><i class="bi bi-check2-square"></i> Complete order</a>
    @endif
@endsection
@section('content')
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-lg-3"><x-stat label="Completed today" :value="number_format($todayCount)" icon="bi-lightning-charge" tone="success" /></div>
    <div class="col-sm-6 col-lg-3"><x-stat label="Total completed" :value="number_format($orders->total())" icon="bi-clipboard-check" /></div>
</div>
<div class="card">
    <form method="GET" class="filter-bar">
        <div class="grow">
            <label class="form-label">Design name</label>
            <div class="input-icon"><i class="bi bi-search"></i>
                <input name="design" class="form-control form-control-sm" value="{{ request('design') }}" placeholder="Search designs…">
            </div>
        </div>
        @if(auth()->user()->hasRole('admin', 'qa'))
            <div style="width:150px">
                <label class="form-label">Designer ID</label>
                <input name="designer" type="number" class="form-control form-control-sm" value="{{ request('designer') }}">
            </div>
        @endif
        <div class="d-flex gap-1">
            <button class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filter</button>
            @if(request()->hasAny(['design', 'designer']))<a href="{{ route('work.index') }}" class="btn btn-sm btn-light">Clear</a>@endif
        </div>
    </form>
    @if($canEditStitches)
        <div class="filter-note"><i class="bi bi-pencil-square"></i> Click a stitch count, type the new value and press <kbd class="mx-1">Enter</kbd>. Setting it to 0 re-opens the order as New.</div>
    @endif
    <div class="table-wrap">
        <table class="table table-hover">
            <thead><tr><th>{{ $showCustomer ? 'Customer' : 'Designer' }}</th><th>Design</th><th class="text-end" style="width:160px">Stitches</th><th class="text-end">Completed</th></tr></thead>
            <tbody>
            @forelse($orders as $o)
                @php($today = $o->Completed_Date?->isToday())
                <tr class="{{ $today ? 'row-today' : '' }}">
                    <td class="cell-mono">
                        @if($showCustomer)
                            {{ $o->Customer_Unique_ID }}
                        @else
                            {{ isset($prefixes[$o->Completed_By]) ? $prefixes[$o->Completed_By].'-' : '' }}{{ $o->Completed_By }}
                        @endif
                    </td>
                    <td class="cell-strong">{{ $o->Design_Name }}</td>
                    @if($canEditStitches)
                        <td contenteditable="true" class="stitch text-end tabular" data-url="{{ route('work.stitches', $o->Order_ID) }}" data-original="{{ (int) $o->No_Of_Stiches }}">{{ (int) $o->No_Of_Stiches }}</td>
                    @else
                        <td class="text-end tabular">{{ number_format((int) $o->No_Of_Stiches) }}</td>
                    @endif
                    <td class="text-end text-nowrap small">
                        @if($today)<span class="pill tone-success">Today {{ $o->Completed_Date->format('g:i A') }}</span>
                        @else<span class="text-muted">{{ $o->Completed_Date?->format('d M Y, g:i A') }}</span>@endif
                    </td>
                </tr>
            @empty
                <x-empty colspan="4" icon="bi-clipboard" title="No completed orders yet">Completed designs will be listed here.</x-empty>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-pager :items="$orders" />
</div>
@endsection
@if($canEditStitches)
@push('scripts')
<script>
    const token = document.querySelector('meta[name="csrf-token"]').content;
    document.querySelectorAll('td.stitch').forEach(function (cell) {
        cell.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { cell.textContent = cell.dataset.original; cell.blur(); return; }
            if (e.key !== 'Enter') return;
            e.preventDefault();
            const value = cell.textContent.trim();
            if (!/^\d+$/.test(value)) { toast('Please enter a whole number.', 'danger'); return; }
            if (value === cell.dataset.original) { cell.blur(); return; }
            if (value === '0' && !confirm('Setting stitches to 0 will re-open this order as New. Continue?')) return;
            fetch(cell.dataset.url, {
                method: 'PATCH',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token},
                body: JSON.stringify({stitches: parseInt(value, 10)})
            }).then(r => r.ok ? r.json() : Promise.reject(r)).then(function (res) {
                if (value === '0') { location.reload(); return; }
                cell.dataset.original = value;
                cell.blur();
                cell.classList.add('saved');
                setTimeout(() => cell.classList.remove('saved'), 1400);
                toast(res.message || 'Saved');
            }).catch(function () {
                toast('Could not update stitches.', 'danger');
                cell.textContent = cell.dataset.original;
            });
        });
        cell.addEventListener('blur', () => { if (!/^\d+$/.test(cell.textContent.trim())) cell.textContent = cell.dataset.original; });
    });
</script>
@endpush
@endif
