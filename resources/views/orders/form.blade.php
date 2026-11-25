@extends('layouts.app')
@section('title', $order->exists ? 'Edit Order #'.$order->Order_ID : 'Add Order')
@section('content')
<div class="card" style="max-width: 640px">
    <div class="card-body">
        <form method="POST" action="{{ $order->exists ? route('orders.update', $order->Order_ID) : route('orders.store') }}">
            @csrf
            @if($order->exists) @method('PUT') @endif
            <div class="mb-3">
                <label class="form-label">Customer *</label>
                <select name="Customer_ID" class="form-select" required autofocus>
                    <option value="">Please Select</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->Customer_ID }}" @selected((int) old('Customer_ID', $order->Customer_ID) === (int) $c->Customer_ID)>{{ $c->CompanyName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Design Name *</label>
                <input name="Design_Name" class="form-control" value="{{ old('Design_Name', $order->Design_Name) }}" required maxlength="200" @if(! $order->exists && $order->Customer_ID) autofocus @endif>
            </div>
            @if($order->exists)
                <div class="mb-3">
                    <label class="form-label">Edit Code</label>
                    <select name="Order_Status_ID" class="form-select">
                        <option value="0">None (re-open as NEW)</option>
                        @foreach($codes as $code)
                            <option value="{{ $code->Code_ID }}" @selected((int) old('Order_Status_ID', $order->Order_Status_ID) === (int) $code->Code_ID)>{{ $code->Narrative }} ({{ $code->Code_Type }})</option>
                        @endforeach
                    </select>
                    <div class="form-text">Saving re-opens the order for the designer. With an edit code it becomes "In Progress" and the code is recorded in the order history.</div>
                </div>
            @endif
            <div class="mb-3">
                <label class="form-label">Order Date *</label>
                <input type="date" name="order_date" class="form-control" value="{{ old('order_date', ($order->Effective_Date ?? now())->toDateString()) }}" required>
            </div>
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('orders.index') }}" class="btn btn-link">Cancel</a>
        </form>
    </div>
</div>
@endsection
