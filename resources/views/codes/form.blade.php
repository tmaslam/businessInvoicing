@extends('layouts.app')
@section('title', $code->exists ? 'Edit error code' : 'Add error code')
@section('actions')
    <a href="{{ route('codes.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Error codes</a>
@endsection
@section('content')
<div class="card card-xs">
    <form method="POST" action="{{ $code->exists ? route('codes.update', $code->Code_ID) : route('codes.store') }}">
        @csrf
        @if($code->exists) @method('PUT') @endif
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Code name <span class="req">*</span></label>
                <input name="Narrative" class="form-control @error('Narrative') is-invalid @enderror" value="{{ old('Narrative', $code->Narrative) }}" required maxlength="50" autofocus placeholder="e.g. Wrong thread colour">
            </div>
            <div>
                <label class="form-label">Type <span class="req">*</span></label>
                <select name="Code_Type" class="form-select">
                    @foreach(config('binvoice.edit_code_types') as $type)
                        <option value="{{ $type }}" @selected(old('Code_Type', $code->Code_Type) === $type)>{{ $type }}</option>
                    @endforeach
                </select>
                <div class="form-text">Use <strong>Designer</strong> for designer mistakes — these are counted in progress reports.</div>
            </div>
        </div>
        <div class="form-footer">
            <a href="{{ route('codes.index') }}" class="btn btn-light">Cancel</a>
            <button class="btn btn-primary"><i class="bi bi-check2"></i> Save</button>
        </div>
    </form>
</div>
@endsection
