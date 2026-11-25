@extends('layouts.app')
@section('title', $expense->exists ? 'Edit expense' : 'Add expense')
@section('actions')
    <a href="{{ route('expenses.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Expenses</a>
@endsection
@section('content')
<div class="card card-xs">
    <form method="POST" action="{{ $expense->exists ? route('expenses.update', $expense->ID) : route('expenses.store') }}">
        @csrf
        @if($expense->exists) @method('PUT') @endif
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Expense name <span class="req">*</span></label>
                <input name="Narrative" class="form-control @error('Narrative') is-invalid @enderror" value="{{ old('Narrative', $expense->Narrative) }}" required maxlength="50" autofocus placeholder="e.g. Office rent">
            </div>
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label">Month <span class="req">*</span></label>
                    <input type="month" name="month" class="form-control" value="{{ old('month', $expense->Expense_Month?->format('Y-m') ?? now()->format('Y-m')) }}" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Amount <span class="req">*</span></label>
                    <input type="number" step="0.01" min="0" name="Amount" class="form-control @error('Amount') is-invalid @enderror" value="{{ old('Amount', $expense->Amount) }}" required placeholder="0.00">
                </div>
            </div>
        </div>
        <div class="form-footer">
            <a href="{{ route('expenses.index') }}" class="btn btn-light">Cancel</a>
            <button class="btn btn-primary"><i class="bi bi-check2"></i> Save</button>
        </div>
    </form>
</div>
@endsection
