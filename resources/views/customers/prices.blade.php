@extends('layouts.app')
@section('title', 'Price ranges')
@section('subtitle', $customer->CompanyName.' · '.$customer->Customer_Unique_ID)
@section('actions')
    <a href="{{ route('customers.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Customers</a>
    <a href="{{ route('customers.edit', $customer->Customer_ID) }}" class="btn btn-light"><i class="bi bi-pencil"></i> Edit customer</a>
@endsection
@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h2 class="card-title">Ranges</h2><span class="small text-muted">{{ $prices->count() }} defined</span></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Stitches from</th><th>Stitches to</th><th>Type</th><th class="text-end">Price</th><th></th></tr></thead>
                    <tbody>
                    @forelse($prices as $p)
                        <tr>
                            <td class="tabular">{{ number_format($p->No_of_Stiches_Start) }}</td>
                            <td class="tabular">{{ number_format($p->No_of_Stiches_End) }}</td>
                            <td>@if($p->Is_Fixed)<span class="pill tone-warning">Fixed price</span>@else<span class="pill tone-primary">Per 1,000</span>@endif</td>
                            <td class="text-end tabular fw-semibold">{{ number_format((float) $p->Price_per_Thousand_Stiches, 2) }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('customers.prices.destroy', [$customer->Customer_ID, $p->ID]) }}" data-confirm="This price range will be removed." data-title="Delete range?" data-ok="Delete">
                                    @csrf @method('DELETE')
                                    <button class="act act-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <x-empty colspan="5" icon="bi-tags" title="No price ranges">The normal fee is used for all designs.</x-empty>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h2 class="card-title">Add range</h2></div>
            <form method="POST" action="{{ route('customers.prices.store', $customer->Customer_ID) }}">
                @csrf
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">From <span class="req">*</span></label>
                            <input name="No_of_Stiches_Start" type="number" min="0" class="form-control" value="{{ old('No_of_Stiches_Start') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">To <span class="req">*</span></label>
                            <input name="No_of_Stiches_End" type="number" min="0" class="form-control" value="{{ old('No_of_Stiches_End') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price type</label>
                        <select name="Is_Fixed" class="form-select">
                            <option value="0">Price per 1,000 stitches</option>
                            <option value="1" @selected(old('Is_Fixed') === '1')>Fixed price</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Price <span class="req">*</span></label>
                        <input name="Price_per_Thousand_Stiches" type="number" step="0.01" min="0" class="form-control" value="{{ old('Price_per_Thousand_Stiches') }}" required>
                    </div>
                </div>
                <div class="form-footer"><button class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Add range</button></div>
            </form>
        </div>
        <div class="card">
            <div class="card-body small">
                <div class="fw-semibold mb-2"><i class="bi bi-calculator text-primary"></i> How the price is calculated</div>
                <ol class="ps-3 mb-0 text-muted">
                    <li>Above max ({{ $customer->Max_Order_Value ? number_format($customer->Max_Order_Value) : '—' }}) → charged as max stitches.</li>
                    <li>Below min ({{ $customer->Min_Order_Value ? number_format($customer->Min_Order_Value) : '—' }}) → charged as min stitches.</li>
                    <li>Inside a range → that range's price.</li>
                    <li>Otherwise → normal fee ({{ $customer->Normal_Fee }} per 1,000).</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection
