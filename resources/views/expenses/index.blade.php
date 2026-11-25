@extends('layouts.app')
@section('title', 'Expenses')
@section('subtitle', 'Monthly business expenses, deducted on the Profit Distribution page.')
@section('actions')
    <a href="{{ route('profit') }}" class="btn btn-light"><i class="bi bi-pie-chart"></i> Profit distribution</a>
    <a href="{{ route('expenses.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add expense</a>
@endsection
@section('content')
<div class="card">
    <div class="table-wrap">
        <table class="table table-hover">
            <thead><tr><th>Month</th><th>Expense</th><th class="text-end">Amount</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($expenses as $e)
                <tr>
                    <td><span class="pill no-dot tone-neutral">{{ $e->Expense_Month?->format('M Y') }}</span></td>
                    <td class="cell-strong">{{ $e->Narrative }}</td>
                    <td class="text-end tabular fw-semibold">{{ number_format($e->Amount, 2) }}</td>
                    <td class="text-end">
                        <div class="row-actions">
                            <a class="act act-primary" title="Edit" href="{{ route('expenses.edit', $e->ID) }}"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('expenses.destroy', $e->ID) }}" data-confirm="“{{ $e->Narrative }}” ({{ number_format($e->Amount, 2) }}) will be deleted." data-title="Delete expense?" data-ok="Delete">
                                @csrf @method('DELETE')
                                <button class="act act-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <x-empty colspan="4" icon="bi-wallet2" title="No expenses recorded"><a href="{{ route('expenses.create') }}">Add an expense</a></x-empty>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-pager :items="$expenses" />
</div>
@endsection
