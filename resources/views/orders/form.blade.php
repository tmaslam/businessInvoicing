@extends('layouts.app')
@section('title', $order->exists ? 'Edit order #'.$order->Order_ID : 'New order')
@section('subtitle', $order->exists ? 'Saving re-opens the order for the designer.' : 'Pick the customer, type the design name and press Enter. The form stays open for the next design.')
@section('actions')
    <a href="{{ route('orders.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> All orders</a>
@endsection
@push('head')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush
@section('content')
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <form method="POST" action="{{ $order->exists ? route('orders.update', $order->Order_ID) : route('orders.store') }}" id="orderForm">
                @csrf
                @if($order->exists) @method('PUT') @endif
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="customer">Customer <span class="req">*</span></label>
                            <select name="Customer_ID" id="customer" class="form-select" required>
                                <option value="">Search customer…</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->Customer_ID }}" @selected((int) old('Customer_ID', $order->Customer_ID) === (int) $c->Customer_ID)>{{ $c->CompanyName }} ({{ $c->Customer_Unique_ID }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="design">Design name <span class="req">*</span></label>
                            <input name="Design_Name" id="design" class="form-control @error('Design_Name') is-invalid @enderror" value="{{ old('Design_Name', $order->Design_Name) }}" required maxlength="200" placeholder="e.g. Company logo – left chest" autocomplete="off">
                            <div class="alert alert-warning small mt-2 mb-0 py-2 gap-2 dup-warning" id="dupWarning" role="alert">
                                <i class="bi bi-exclamation-triangle"></i><span>This customer already has an order with this design name.</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="order_date">Order date <span class="req">*</span></label>
                            <input type="date" id="order_date" name="order_date" class="form-control" value="{{ old('order_date', ($order->Effective_Date ?? now())->toDateString()) }}" required>
                        </div>
                        @if($order->exists)
                            <div class="col-12">
                                <label class="form-label" for="code">Edit code</label>
                                <select name="Order_Status_ID" id="code" class="form-select">
                                    <option value="0">No edit code — re-open as New</option>
                                    @foreach($codes as $code)
                                        <option value="{{ $code->Code_ID }}" @selected((int) old('Order_Status_ID', $order->Order_Status_ID) === (int) $code->Code_ID)>{{ $code->Narrative }} · {{ $code->Code_Type }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">With an edit code the order becomes <strong>In Progress</strong> and the code is saved in the order history (designer codes count as mistakes).</div>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-info mb-0 d-flex align-items-center gap-2 small"><i class="bi bi-info-circle"></i>
                                    Current status: <x-status :status="$order->Order_Status" />
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="form-footer">
                    <a href="{{ route('orders.index') }}" class="btn btn-light">Cancel</a>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> {{ $order->exists ? 'Save changes' : 'Add order' }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <h2 class="card-title">Recent orders of this customer</h2>
                <span class="small text-muted" id="recentCount"></span>
            </div>
            <div class="recent-list" id="recentList">
                <div class="empty py-4"><i class="bi bi-person-check"></i><strong>Select a customer</strong><div class="small">Their latest orders will appear here so you can avoid duplicates.</div></div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
(function () {
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));
    const tones = {'NEW': ['primary', 'New'], 'In Progress': ['warning', 'In Progress'], 'Completed': ['success', 'Completed'], 'Lock': ['neutral', 'Locked'], 'Archive': ['info', 'Paid']};
    const list = document.getElementById('recentList'), count = document.getElementById('recentCount');
    const design = document.getElementById('design'), warn = document.getElementById('dupWarning');
    const currentId = {{ (int) $order->Order_ID }};
    let recent = [];

    function checkDuplicate() {
        const name = design.value.trim().toLowerCase();
        const dup = name !== '' && recent.some(o => o.id !== currentId && String(o.design).trim().toLowerCase() === name);
        warn.classList.toggle('show', dup);
    }

    function loadRecent(customerId) {
        if (!customerId) { recent = []; count.textContent = ''; checkDuplicate(); return; }
        list.innerHTML = '<div class="empty py-4"><span class="spinner-border spinner-border-sm"></span></div>';
        fetch(@json(route('orders.recent')) + '?customer=' + encodeURIComponent(customerId), {headers: {'Accept': 'application/json'}})
            .then(r => r.json())
            .then(function (rows) {
                recent = rows;
                count.textContent = rows.length ? 'Last ' + rows.length : '';
                if (!rows.length) {
                    list.innerHTML = '<div class="empty py-4"><i class="bi bi-inbox"></i><strong>No orders yet</strong><div class="small">This will be the first order for this customer.</div></div>';
                } else {
                    list.innerHTML = '<table class="table table-hover mb-0"><tbody>' + rows.map(function (o) {
                        const t = tones[o.status] || ['neutral', o.status];
                        return '<tr><td><a href="' + esc(o.url) + '" class="cell-strong">' + esc(o.design) + '</a><span class="cell-sub">#' + o.id + ' · ' + esc(o.date) + '</span></td>' +
                               '<td class="text-end"><span class="pill tone-' + t[0] + '">' + esc(t[1]) + '</span></td></tr>';
                    }).join('') + '</tbody></table>';
                }
                checkDuplicate();
            })
            .catch(() => { list.innerHTML = '<div class="empty py-4 small">Could not load recent orders.</div>'; });
    }

    const ts = new TomSelect('#customer', {
        maxOptions: 300, placeholder: 'Search customer…',
        onChange: function (v) { loadRecent(v); design.focus(); }
    });
    design.addEventListener('input', checkDuplicate);

    if (ts.getValue()) loadRecent(ts.getValue());
    @unless($order->exists)
        (ts.getValue() ? design : ts).focus();
    @endunless
})();
</script>
@endpush
