@extends('layouts.app')
@section('title', 'Price Ranges — '.$customer->CompanyName)
@section('actions')
    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
@endsection
@section('content')
<div class="card" style="max-width: 900px">
    <div class="card-body">
        <p class="small text-muted">
            Normal fee: <strong>{{ $customer->Normal_Fee }}</strong> per 1000 stitches ·
            Min stitches: <strong>{{ $customer->Min_Order_Value ?: '—' }}</strong> ·
            Max stitches: <strong>{{ $customer->Max_Order_Value ?: '—' }}</strong>.
            A matching range below overrides the normal fee.
        </p>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead><tr><th>Stitches Start</th><th>Stitches End</th><th>Price Type</th><th>Price</th><th></th></tr></thead>
                <tbody>
                @forelse($prices as $p)
                    <tr>
                        <td>{{ $p->No_of_Stiches_Start }}</td>
                        <td>{{ $p->No_of_Stiches_End }}</td>
                        <td>{{ $p->Is_Fixed ? 'Fixed Price' : 'Price per Thousand' }}</td>
                        <td>{{ $p->Price_per_Thousand_Stiches }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('customers.prices.destroy', [$customer->Customer_ID, $p->ID]) }}" data-confirm="Are you sure to delete this?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted">No price ranges yet.</td></tr>
                @endforelse
                </tbody>
                <tfoot>
                <tr>
                    <td><input form="priceForm" name="No_of_Stiches_Start" type="number" min="0" class="form-control form-control-sm" value="{{ old('No_of_Stiches_Start') }}" required></td>
                    <td><input form="priceForm" name="No_of_Stiches_End" type="number" min="0" class="form-control form-control-sm" value="{{ old('No_of_Stiches_End') }}" required></td>
                    <td>
                        <select form="priceForm" name="Is_Fixed" class="form-select form-select-sm">
                            <option value="0">Price per Thousand</option>
                            <option value="1" @selected(old('Is_Fixed') === '1')>Fixed Price</option>
                        </select>
                    </td>
                    <td><input form="priceForm" name="Price_per_Thousand_Stiches" type="number" step="0.01" min="0" class="form-control form-control-sm" value="{{ old('Price_per_Thousand_Stiches') }}" required></td>
                    <td class="text-end"><button form="priceForm" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Add</button></td>
                </tr>
                </tfoot>
            </table>
        </div>
        <form id="priceForm" method="POST" action="{{ route('customers.prices.store', $customer->Customer_ID) }}">@csrf</form>
    </div>
</div>
@endsection
