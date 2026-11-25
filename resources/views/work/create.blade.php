@extends('layouts.app')
@section('title', 'Complete Order')
@push('head')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush
@section('content')
<div class="card" style="max-width: 640px">
    <div class="card-body">
        <form method="POST" action="{{ route('work.store') }}" id="completeForm">
            @csrf
            <div class="mb-3">
                <label class="form-label">Design (search by name) *</label>
                <select name="order_id" id="design" required placeholder="Type design name..."></select>
                <div class="form-text">Only NEW and In Progress orders are listed.</div>
            </div>
            <div class="mb-3 d-none" id="statusBox">
                <span class="me-3">Status: <strong id="status"></strong></span>
                <span class="me-3 d-none" id="codeWrap">Edit Code: <strong id="code" class="text-danger"></strong></span>
                <span>Customer: <strong id="customer"></strong></span>
            </div>
            <div class="mb-3">
                <label class="form-label">No of Stitches *</label>
                <input type="number" min="1" name="stitches" id="stitches" class="form-control" value="{{ old('stitches') }}" required>
            </div>
            <button class="btn btn-primary">Complete</button>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    const ts = new TomSelect('#design', {
        valueField: 'id', labelField: 'text', searchField: ['text'],
        preload: 'focus', loadThrottle: 250, maxOptions: 50,
        load: function (query, callback) {
            fetch(@json(route('work.search')) + '?q=' + encodeURIComponent(query), {headers: {'Accept': 'application/json'}})
                .then(r => r.json()).then(callback).catch(() => callback());
        },
        render: { no_results: () => '<div class="no-results">No open order found</div>' },
        onChange: function (value) {
            const o = this.options[value];
            document.getElementById('statusBox').classList.toggle('d-none', !o);
            if (!o) return;
            document.getElementById('status').textContent = o.status;
            document.getElementById('customer').textContent = o.customer || '';
            document.getElementById('codeWrap').classList.toggle('d-none', !o.edit_code);
            document.getElementById('code').textContent = o.edit_code || '';
            document.getElementById('stitches').focus();
        }
    });
    ts.focus();
</script>
@endpush
