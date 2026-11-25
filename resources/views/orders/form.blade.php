@extends('layouts.app')
@section('title', $order->exists ? 'Edit order #'.$order->Order_ID : 'New order')
@section('subtitle', $order->exists ? 'Saving re-opens the order for the designer.' : 'Add a design order for a customer. The designer sets the stitches when completing it.')
@section('actions')
    <a href="{{ route('orders.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Orders</a>
@endsection
@push('head')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush
@section('content')
<div class="card card-narrow">
    <form method="POST" action="{{ $order->exists ? route('orders.update', $order->Order_ID) : route('orders.store') }}">
        @csrf
        @if($order->exists) @method('PUT') @endif
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Customer <span class="req">*</span></label>
                    <select name="Customer_ID" id="customer" class="form-select" required>
                        <option value="">Search customer…</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->Customer_ID }}" @selected((int) old('Customer_ID', $order->Customer_ID) === (int) $c->Customer_ID)>{{ $c->CompanyName }} ({{ $c->Customer_Unique_ID }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Design name <span class="req">*</span></label>
                    <input name="Design_Name" id="design" class="form-control @error('Design_Name') is-invalid @enderror" value="{{ old('Design_Name', $order->Design_Name) }}" required maxlength="200" placeholder="e.g. Company logo – left chest">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Order date <span class="req">*</span></label>
                    <input type="date" name="order_date" class="form-control" value="{{ old('order_date', ($order->Effective_Date ?? now())->toDateString()) }}" required>
                </div>
                @if($order->exists)
                    <div class="col-12">
                        <label class="form-label">Edit code</label>
                        <select name="Order_Status_ID" class="form-select">
                            <option value="0">No edit code — re-open as New</option>
                            @foreach($codes as $code)
                                <option value="{{ $code->Code_ID }}" @selected((int) old('Order_Status_ID', $order->Order_Status_ID) === (int) $code->Code_ID)>{{ $code->Narrative }} · {{ $code->Code_Type }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">With an edit code the order becomes <strong>In Progress</strong> and the code is saved to the order history (counted as a mistake for designer codes).</div>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-info mb-0 d-flex gap-2 small"><i class="bi bi-info-circle"></i>
                            <div>Current status: <x-status :status="$order->Order_Status" class="ms-1" />. Saving changes the status as described above.</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="form-footer">
            <a href="{{ route('orders.index') }}" class="btn btn-light">Cancel</a>
            @unless($order->exists)
                <span class="me-auto small text-muted align-self-center d-none d-md-inline">After saving you can add the next design for the same customer.</span>
            @endunless
            <button class="btn btn-primary"><i class="bi bi-check2"></i> {{ $order->exists ? 'Save changes' : 'Add order' }}</button>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    const ts = new TomSelect('#customer', { maxOptions: 200, placeholder: 'Search customer…' });
    @if(! $order->exists)
        (ts.getValue() ? document.getElementById('design') : ts).focus();
    @endif
</script>
@endpush
