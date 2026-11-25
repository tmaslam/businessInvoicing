@extends('layouts.app')
@section('title', $customer->exists ? 'Edit Customer' : 'Add Customer')
@section('content')
<div class="card" style="max-width: 760px">
    <div class="card-body">
        <form method="POST" action="{{ $customer->exists ? route('customers.update', $customer->Customer_ID) : route('customers.store') }}">
            @csrf
            @if($customer->exists) @method('PUT') @endif
            <div class="row g-3">
                @if($customer->exists)
                    <div class="col-md-6">
                        <label class="form-label">Customer Code</label>
                        <input class="form-control" value="{{ $customer->Customer_Unique_ID }}" disabled>
                    </div>
                @endif
                <div class="col-md-6">
                    <label class="form-label">Company Name *</label>
                    <input name="CompanyName" class="form-control" value="{{ old('CompanyName', $customer->CompanyName) }}" required maxlength="150">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Person *</label>
                    <input name="ContactPerson" class="form-control" value="{{ old('ContactPerson', $customer->ContactPerson) }}" required maxlength="150">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input name="Email" type="email" class="form-control" value="{{ old('Email', $customer->Email) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <textarea name="Address" rows="3" class="form-control" maxlength="255">{{ old('Address', trim((string) $customer->Address)) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Zip Code</label>
                    <input name="Zip_Code" class="form-control" value="{{ old('Zip_Code', $customer->Zip_Code) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">City</label>
                    <input name="City" class="form-control" value="{{ old('City', $customer->City) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">State</label>
                    <input name="State" class="form-control" value="{{ old('State', $customer->State) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Country</label>
                    <select name="Country" class="form-select">
                        <option value="">Please Select</option>
                        @foreach($countries as $country)
                            <option value="{{ $country }}" @selected(old('Country', $customer->Country) === $country)>{{ $country }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input name="Phone" class="form-control" value="{{ old('Phone', $customer->Phone) }}" placeholder="###-###-####">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Min No of Stitches</label>
                    <input name="Min_Order_Value" type="number" min="0" class="form-control" value="{{ old('Min_Order_Value', $customer->Min_Order_Value) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Max No of Stitches</label>
                    <input name="Max_Order_Value" type="number" min="0" class="form-control" value="{{ old('Max_Order_Value', $customer->Max_Order_Value) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Normal Fee (per 1000 stitches) *</label>
                    <input name="Normal_Fee" type="number" step="0.01" min="0.01" class="form-control" value="{{ old('Normal_Fee', $customer->Normal_Fee) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Urgent Fee *</label>
                    <input name="Urgent_Fee" type="number" step="0.01" min="0.01" class="form-control" value="{{ old('Urgent_Fee', $customer->Urgent_Fee) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Currency</label>
                    <select name="Currency" class="form-select">
                        @foreach(config('binvoice.currencies') as $id => $name)
                            <option value="{{ $id }}" @selected((int) old('Currency', $customer->Currency) === $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Website</label>
                    <select name="Website_ID" class="form-select">
                        @foreach(config('binvoice.websites') as $id => $site)
                            <option value="{{ $id }}" @selected((int) old('Website_ID', $customer->Website_ID) === $id)>{{ $site['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="Is_Active" class="form-select">
                        <option value="1" @selected((int) old('Is_Active', $customer->Is_Active) === 1)>Active</option>
                        <option value="2" @selected((int) old('Is_Active', $customer->Is_Active) === 2)>InActive</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('customers.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
