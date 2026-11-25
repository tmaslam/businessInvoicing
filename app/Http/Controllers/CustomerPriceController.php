<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PriceAttribute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Per-customer stitch price ranges (legacy "Add Customer Price Range" dialog).
 */
class CustomerPriceController extends Controller
{
    public function index(Customer $customer): View
    {
        return view('customers.prices', [
            'customer' => $customer,
            'prices' => $customer->priceAttributes()->get(),
        ]);
    }

    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate([
            'No_of_Stiches_Start' => ['required', 'integer', 'min:0'],
            'No_of_Stiches_End' => ['required', 'integer', 'gte:No_of_Stiches_Start'],
            'Price_per_Thousand_Stiches' => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'Is_Fixed' => ['required', 'in:0,1'],
        ], [], [
            'No_of_Stiches_Start' => 'stitches start',
            'No_of_Stiches_End' => 'stitches end',
            'Price_per_Thousand_Stiches' => 'price',
        ]);

        $customer->priceAttributes()->create($data);

        return redirect()->route('customers.prices.index', $customer)->with('success', 'Price range added.');
    }

    public function destroy(Customer $customer, PriceAttribute $price): RedirectResponse
    {
        abort_unless((int) $price->Customer_ID === (int) $customer->Customer_ID, 404);
        $price->delete();

        return redirect()->route('customers.prices.index', $customer)->with('success', 'Price range deleted.');
    }
}
