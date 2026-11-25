@extends('layouts.app')
@section('title', 'Error Codes')
@section('actions')
    <a href="{{ route('codes.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Code</a>
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead><tr><th>Code ID</th><th>Code Name</th><th>Code Type</th><th>Date Added</th><th>Last Update</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @forelse($codes as $code)
                    <tr>
                        <td>{{ $code->Code_ID }}</td>
                        <td>{{ $code->Narrative }}</td>
                        <td>{{ $code->Code_Type }}</td>
                        <td>{{ $code->Effective_Date?->format('d-M-Y g:i A') ?? 'N/A' }}</td>
                        <td>{{ $code->Time_Stamp?->format('d-M-Y g:i A') ?? 'N/A' }}</td>
                        <td class="text-end actions text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('codes.edit', $code->Code_ID) }}"><i class="bi bi-pencil"></i></a>
                            <form class="d-inline" method="POST" action="{{ route('codes.destroy', $code->Code_ID) }}" data-confirm="Are you sure to delete this?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No record found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $codes->links() }}
    </div>
</div>
@endsection
