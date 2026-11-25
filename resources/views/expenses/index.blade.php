@extends('layouts.app')
@section('title', 'Expense Listing')
@section('actions')
    <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Expense</a>
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead><tr><th>Expense Name</th><th class="text-end">Amount</th><th>Expense Month</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse($expenses as $e)
                    <tr>
                        <td>{{ $e->Narrative }}</td>
                        <td class="text-end">{{ number_format($e->Amount, 2) }}</td>
                        <td>{{ $e->Expense_Month?->format('F Y') }}</td>
                        <td class="text-end actions text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('expenses.edit', $e->ID) }}"><i class="bi bi-pencil"></i></a>
                            <form class="d-inline" method="POST" action="{{ route('expenses.destroy', $e->ID) }}" data-confirm="Are you sure to delete this?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">No record found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $expenses->links() }}
    </div>
</div>
@endsection
