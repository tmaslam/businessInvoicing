@extends('layouts.app')
@section('title', 'Profit Distribution')
@section('content')
<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end mb-4">
            <div class="col-auto">
                <label class="form-label small mb-0">Business Year</label>
                <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($years as $y)
                        <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </form>
        @php($fmt = fn ($v) => number_format($totalProfit == 0 ? 0 : $v, 2))
        <div class="row g-3">
            @foreach([
                ['Total Profit (paid orders)', number_format($totalProfit, 2), 'primary'],
                ['Total Expense', number_format($totalExpense, 2), 'danger'],
                ['Earned Profit', $fmt($earned), 'success'],
                ['Owner Profit ('.$ownerPercent.'%)', $fmt($owner), 'dark'],
                ['Employee Profit ('.(100 - $ownerPercent).'%)', $fmt($employee), 'info'],
            ] as [$label, $value, $color])
                <div class="col-sm-6 col-lg">
                    <div class="border-start border-4 border-{{ $color }} bg-light rounded p-3 h-100">
                        <div class="small text-muted">{{ $label }}</div>
                        <div class="fs-4 fw-semibold">{{ $value }}</div>
                    </div>
                </div>
            @endforeach
        </div>
        <p class="small text-muted mt-3 mb-0">Totals cover 1 Jan – 31 Dec {{ $year }}: orders marked as paid (Archive) by completion date, and expenses by expense month.</p>
    </div>
</div>
@endsection
