@extends('layouts.app')
@section('title', 'Error codes')
@section('subtitle', 'Reasons used when an order is sent back for an edit. Designer codes count as mistakes in reports.')
@section('actions')
    <a href="{{ route('codes.create') }}" class="btn btn-primary" data-shortcut="n" title="Shortcut: N"><i class="bi bi-plus-lg"></i> Add code</a>
@endsection
@section('content')
@php($tones = ['Designer' => 'danger', 'Customer' => 'info', 'Other' => 'neutral'])
<div class="card">
    <div class="table-wrap">
        <table class="table table-hover">
            <thead><tr><th>ID</th><th>Name</th><th>Type</th><th>Added</th><th>Last updated</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($codes as $code)
                <tr>
                    <td class="cell-mono">{{ $code->Code_ID }}</td>
                    <td class="cell-strong">{{ $code->Narrative }}</td>
                    <td><span class="pill tone-{{ $tones[$code->Code_Type] ?? 'neutral' }}">{{ $code->Code_Type }}</span></td>
                    <td class="small text-muted">{{ $code->Effective_Date?->format('d M Y') ?? '—' }}</td>
                    <td class="small text-muted">{{ $code->Time_Stamp?->diffForHumans() ?? '—' }}</td>
                    <td class="text-end">
                        <div class="row-actions">
                            <a class="act act-primary" title="Edit" href="{{ route('codes.edit', $code->Code_ID) }}"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('codes.destroy', $code->Code_ID) }}" data-confirm="“{{ $code->Narrative }}” will no longer be available for new edits." data-title="Delete code?" data-ok="Delete">
                                @csrf @method('DELETE')
                                <button class="act act-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <x-empty colspan="6" icon="bi-bug" title="No error codes"><a href="{{ route('codes.create') }}">Add the first code</a></x-empty>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-pager :items="$codes" />
</div>
@endsection
