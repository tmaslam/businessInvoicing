@extends('layouts.app')
@section('title', $code->exists ? 'Edit Code' : 'Add Code')
@section('content')
<div class="card" style="max-width: 520px">
    <div class="card-body">
        <form method="POST" action="{{ $code->exists ? route('codes.update', $code->Code_ID) : route('codes.store') }}">
            @csrf
            @if($code->exists) @method('PUT') @endif
            <div class="mb-3">
                <label class="form-label">Code Name *</label>
                <input name="Narrative" class="form-control" value="{{ old('Narrative', $code->Narrative) }}" required maxlength="50" autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Code Type *</label>
                <select name="Code_Type" class="form-select">
                    @foreach(config('binvoice.edit_code_types') as $type)
                        <option value="{{ $type }}" @selected(old('Code_Type', $code->Code_Type) === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('codes.index') }}" class="btn btn-link">Cancel</a>
        </form>
    </div>
</div>
@endsection
