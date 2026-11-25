@extends('layouts.app')
@section('title', 'Completed Orders')
@section('content')
<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-auto">
                <label class="form-label small mb-0">Design name</label>
                <input name="design" class="form-control form-control-sm" value="{{ request('design') }}">
            </div>
            @if(auth()->user()->hasRole('admin', 'qa'))
                <div class="col-auto">
                    <label class="form-label small mb-0">Designer ID</label>
                    <input name="designer" type="number" class="form-control form-control-sm" value="{{ request('designer') }}">
                </div>
            @endif
            <div class="col-auto"><button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Search</button></div>
            <div class="col-auto ms-auto"><span class="badge text-bg-success fs-6">Completed today: {{ $todayCount }}</span></div>
        </form>
        @if($canEditStitches)
            <p class="small text-muted">Click a stitch count to edit it, then press <kbd>Enter</kbd>. Setting it to 0 re-opens the order as NEW.</p>
        @endif
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead><tr><th>{{ $showCustomer ? 'Customer ID' : 'Designer ID' }}</th><th>Design Name</th><th>No Of Stitches</th><th>Completed</th></tr></thead>
                <tbody>
                @forelse($orders as $o)
                    @php($today = $o->Completed_Date?->isToday())
                    <tr class="{{ $today ? 'row-today' : '' }}">
                        <td>
                            @if($showCustomer)
                                {{ $o->Customer_Unique_ID }}
                            @else
                                {{ isset($prefixes[$o->Completed_By]) ? $prefixes[$o->Completed_By].'-' : '' }}{{ $o->Completed_By }}
                            @endif
                        </td>
                        <td>{{ $o->Design_Name }}</td>
                        @if($canEditStitches)
                            <td contenteditable="true" class="stitch" data-url="{{ route('work.stitches', $o->Order_ID) }}" data-original="{{ (int) $o->No_Of_Stiches }}">{{ (int) $o->No_Of_Stiches }}</td>
                        @else
                            <td>{{ (int) $o->No_Of_Stiches }}</td>
                        @endif
                        <td class="text-nowrap">{{ $today ? 'Today '.$o->Completed_Date->format('H:i:s') : $o->Completed_Date?->format('Y-m-d H:i:s') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">No record found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $orders->links() }}
    </div>
</div>
@endsection
@if($canEditStitches)
@push('scripts')
<script>
    const token = document.querySelector('meta[name="csrf-token"]').content;
    document.querySelectorAll('td.stitch').forEach(function (cell) {
        cell.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            const value = cell.textContent.trim();
            if (!/^\d+$/.test(value)) { alert('Please enter a whole number.'); return; }
            if (value === '0' && !confirm('Setting stitches to 0 will re-open this order as NEW. Continue?')) return;
            fetch(cell.dataset.url, {
                method: 'PATCH',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token},
                body: JSON.stringify({stitches: parseInt(value, 10)})
            }).then(r => r.ok ? r.json() : Promise.reject(r)).then(function () {
                if (value === '0') { location.reload(); return; }
                cell.dataset.original = value;
                cell.blur();
                cell.classList.add('table-success');
                setTimeout(() => cell.classList.remove('table-success'), 1200);
            }).catch(function () {
                alert('Could not update stitches.');
                cell.textContent = cell.dataset.original;
            });
        });
    });
</script>
@endpush
@endif
