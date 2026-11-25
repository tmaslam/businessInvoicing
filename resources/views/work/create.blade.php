@extends('layouts.app')
@section('title', 'Complete order')
@section('subtitle', 'Find the design, enter the stitch count, and press Enter. The price is calculated automatically.')
@section('actions')
    <a href="{{ route('work.index') }}" class="btn btn-light"><i class="bi bi-clipboard-check"></i> My completed orders</a>
@endsection
@push('head')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush
@section('content')
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <form method="POST" action="{{ route('work.store') }}" id="completeForm">
                @csrf
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label">1 · Design <span class="req">*</span></label>
                        <select name="order_id" id="design" required placeholder="Type to search open designs…"></select>
                        <div class="form-text">Only <strong>New</strong> and <strong>In Progress</strong> orders are listed. Newest first.</div>
                    </div>

                    <div id="selected" class="d-none mb-4 p-3 rounded-3" style="background: var(--bi-surface-2); border: 1px solid var(--bi-border)">
                        <div class="d-flex flex-wrap gap-3 align-items-center">
                            <div class="me-auto">
                                <div class="small text-muted">Selected design</div>
                                <div class="fw-semibold" id="selName"></div>
                                <div class="small text-muted" id="selCustomer"></div>
                            </div>
                            <span id="selStatus"></span>
                        </div>
                        <div id="selCode" class="alert alert-warning small mt-3 mb-0 d-none"><i class="bi bi-exclamation-triangle"></i> Edit requested: <strong></strong></div>
                    </div>

                    <div>
                        <label class="form-label">2 · Number of stitches <span class="req">*</span></label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text" style="background: var(--bi-surface-2); border-color: var(--bi-border)"><i class="bi bi-bezier2"></i></span>
                            <input type="number" min="1" name="stitches" id="stitches" class="form-control @error('stitches') is-invalid @enderror" value="{{ old('stitches') }}" required placeholder="e.g. 8500" inputmode="numeric">
                        </div>
                    </div>
                </div>
                <div class="form-footer">
                    <span class="me-auto small text-muted align-self-center"><kbd>Enter</kbd> to submit</span>
                    <button class="btn btn-primary btn-lg px-4" id="submitBtn"><i class="bi bi-check2-circle"></i> Complete design</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-body">
                <div class="fw-semibold mb-3"><i class="bi bi-lightbulb text-warning"></i> Quick tips</div>
                <ul class="small text-muted ps-3 mb-0" style="line-height:1.9">
                    <li>Start typing any part of the design name — the list updates instantly.</li>
                    <li>Orders marked <span class="pill tone-warning">In Progress</span> were sent back for an edit; check the edit request before finishing.</li>
                    <li>After completing, the form resets so you can finish the next design right away.</li>
                    <li>Made a mistake? Ask QA or an admin to correct the stitch count.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));
    const pill = (status) => status === 'In Progress' ? '<span class="pill tone-warning">In Progress</span>' : '<span class="pill tone-primary">New</span>';
    const ts = new TomSelect('#design', {
        valueField: 'id', labelField: 'text', searchField: ['text', 'customer'],
        preload: 'focus', loadThrottle: 250, maxOptions: 50,
        load: function (query, callback) {
            fetch(@json(route('work.search')) + '?q=' + encodeURIComponent(query), {headers: {'Accept': 'application/json'}})
                .then(r => r.json()).then(callback).catch(() => callback());
        },
        render: {
            option: (o) => '<div class="d-flex align-items-center gap-2"><div class="me-auto">' + esc(o.text) + '<span class="ts-opt-sub">' + esc(o.customer) + (o.edit_code ? ' · edit: ' + esc(o.edit_code) : '') + '</span></div>' + pill(o.status) + '</div>',
            item: (o) => '<div>' + esc(o.text) + '</div>',
            no_results: () => '<div class="no-results p-2 text-muted">No open order matches</div>',
        },
        onChange: function (value) {
            const o = this.options[value], box = document.getElementById('selected');
            box.classList.toggle('d-none', !o);
            if (!o) return;
            document.getElementById('selName').textContent = o.text;
            document.getElementById('selCustomer').textContent = o.customer || '';
            document.getElementById('selStatus').innerHTML = pill(o.status);
            const code = document.getElementById('selCode');
            code.classList.toggle('d-none', !o.edit_code);
            code.querySelector('strong').textContent = o.edit_code || '';
            document.getElementById('stitches').focus();
        }
    });
    ts.focus();
</script>
@endpush
