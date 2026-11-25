@extends('layouts.app')
@section('title', $customer->exists ? 'Edit customer' : 'Add customer')
@section('subtitle', $customer->exists ? $customer->CompanyName.' · '.$customer->Customer_Unique_ID : 'The customer code is generated automatically from the website.')
@section('actions')
    <a href="{{ route('customers.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
@endsection
@section('content')
<div class="card card-narrow">
    <form method="POST" action="{{ $customer->exists ? route('customers.update', $customer->Customer_ID) : route('customers.store') }}">
        @csrf
        @if($customer->exists) @method('PUT') @endif
        <div class="card-body">
            <div class="form-section">
                <div class="form-section-title">Company</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Company name <span class="req">*</span></label>
                        <input name="CompanyName" class="form-control @error('CompanyName') is-invalid @enderror" value="{{ old('CompanyName', $customer->CompanyName) }}" required maxlength="150" autofocus>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact person <span class="req">*</span></label>
                        <input name="ContactPerson" class="form-control @error('ContactPerson') is-invalid @enderror" value="{{ old('ContactPerson', $customer->ContactPerson) }}" required maxlength="150">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email <span class="req">*</span></label>
                        <div class="input-icon"><i class="bi bi-envelope"></i>
                            <input name="Email" type="email" class="form-control @error('Email') is-invalid @enderror" value="{{ old('Email', $customer->Email) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <div class="input-icon"><i class="bi bi-telephone"></i>
                            <input name="Phone" class="form-control" value="{{ old('Phone', $customer->Phone) }}" placeholder="###-###-####">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Website</label>
                        <select name="Website_ID" class="form-select">
                            @foreach(config('binvoice.websites') as $id => $site)
                                <option value="{{ $id }}" @selected((int) old('Website_ID', $customer->Website_ID) === $id)>{{ $site['name'] }} ({{ $site['prefix'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="Is_Active" class="form-select">
                            <option value="1" @selected((int) old('Is_Active', $customer->Is_Active) === 1)>Active</option>
                            <option value="2" @selected((int) old('Is_Active', $customer->Is_Active) === 2)>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">Address</div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Street address</label>
                        <textarea name="Address" rows="2" class="form-control" maxlength="255">{{ old('Address', trim((string) $customer->Address)) }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">City</label>
                        <input name="City" class="form-control" value="{{ old('City', $customer->City) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">State</label>
                        <input name="State" class="form-control" value="{{ old('State', $customer->State) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Zip code</label>
                        <input name="Zip_Code" class="form-control" value="{{ old('Zip_Code', $customer->Zip_Code) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Country</label>
                        <select name="Country" class="form-select">
                            <option value="">Select country</option>
                            @foreach($countries as $country)
                                <option value="{{ $country }}" @selected(old('Country', $customer->Country) === $country)>{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">Pricing</div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Normal fee <span class="req">*</span></label>
                        <input name="Normal_Fee" type="number" step="0.01" min="0.01" class="form-control @error('Normal_Fee') is-invalid @enderror" value="{{ old('Normal_Fee', $customer->Normal_Fee) }}" required>
                        <div class="form-text">Per 1,000 stitches</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Urgent fee <span class="req">*</span></label>
                        <input name="Urgent_Fee" type="number" step="0.01" min="0.01" class="form-control @error('Urgent_Fee') is-invalid @enderror" value="{{ old('Urgent_Fee', $customer->Urgent_Fee) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Currency</label>
                        <select name="Currency" class="form-select">
                            @foreach(config('binvoice.currencies') as $id => $name)
                                <option value="{{ $id }}" @selected((int) old('Currency', $customer->Currency) === $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Minimum stitches</label>
                        <input name="Min_Order_Value" type="number" min="0" class="form-control" value="{{ old('Min_Order_Value', $customer->Min_Order_Value) }}">
                        <div class="form-text">Smaller designs are charged as this many stitches.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Maximum stitches</label>
                        <input name="Max_Order_Value" type="number" min="0" class="form-control" value="{{ old('Max_Order_Value', $customer->Max_Order_Value) }}">
                        <div class="form-text">Larger designs are capped at this many stitches.</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-footer">
            <a href="{{ route('customers.index') }}" class="btn btn-light">Cancel</a>
            <button class="btn btn-primary"><i class="bi bi-check2"></i> {{ $customer->exists ? 'Save changes' : 'Create customer' }}</button>
        </div>
    </form>
</div>
@endsection
