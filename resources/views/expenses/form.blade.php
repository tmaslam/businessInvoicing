@extends('layouts.app')
@section('title', $expense->exists ? 'Edit Expense' : 'Add Expense')
@section('content')
<div class="card" style="max-width: 520px">
    <div class="card-body">
        <form method="POST" action="{{ $expense->exists ? route('expenses.update', $expense->ID) : route('expenses.store') }}">
            @csrf
            @if($expense->exists) @method('PUT') @endif
            <div class="mb-3">
                <label class="form-label">Expense Month *</label>
                <input type="month" name="month" class="form-control" value="{{ old('month', $expense->Expense_Month?->format('Y-m') ?? now()->format('Y-m')) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Expense Name *</label>
                <input name="Narrative" class="form-control" value="{{ old('Narrative', $expense->Narrative) }}" required maxlength="50">
            </div>
            <div class="mb-3">
                <label class="form-label">Amount *</label>
                <input type="number" step="0.01" min="0" name="Amount" class="form-control" value="{{ old('Amount', $expense->Amount) }}" required>
            </div>
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('expenses.index') }}" class="btn btn-link">Cancel</a>
        </form>
    </div>
</div>
@endsection
