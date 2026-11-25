<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use App\Models\Customer;
use App\Models\Order;
use App\Services\ExcelReports;
use App\Support\Countries;
use App\Support\DateRange;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $range = DateRange::fromRequest($request);
        $customers = Customer::active()
            ->orderByDesc('Customer_ID')
            ->paginate(config('binvoice.per_page.customers'))
            ->withQueryString();

        return view('customers.index', compact('customers', 'range'));
    }

    public function create(): View
    {
        return view('customers.form', ['customer' => new Customer(['Min_Order_Value' => 4000, 'Is_Active' => 1, 'Currency' => 1, 'Website_ID' => 1]), 'countries' => Countries::all()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $data['Session_ID'] = ActionLog::record('CustomerInsert');
            $customer = Customer::create($data);
            $customer->update(['Customer_Unique_ID' => $customer->generateUniqueCode()]);
        });

        return redirect()->route('customers.index')->with('success', 'Customer has been successfully added.');
    }

    public function edit(Customer $customer): View
    {
        return view('customers.form', ['customer' => $customer, 'countries' => Countries::all()]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $this->validated($request);
        $data['Session_ID'] = ActionLog::record('CustomerInsert');
        $customer->update($data);

        return redirect()->route('customers.index')->with('success', 'Customer has been successfully updated.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->forceFill(['End_Date' => now()])->save();

        return redirect()->route('customers.index')->with('success', 'Customer has been successfully deleted.');
    }

    /** Completed orders for a customer in the selected range (legacy "view detail"). */
    public function completedOrders(Request $request, Customer $customer): View
    {
        $range = DateRange::fromRequest($request);
        $orders = Order::active()->status('completed')
            ->where('Customer_ID', $customer->Customer_ID)
            ->whereBetween('Completed_Date', $range->bounds())
            ->orderBy('Effective_Date')
            ->get();

        return view('customers.orders', compact('customer', 'orders', 'range'));
    }

    public function invoice(Request $request, Customer $customer, ExcelReports $excel): StreamedResponse
    {
        $range = DateRange::fromRequest($request);
        $orders = Order::active()->status('completed')
            ->where('Customer_ID', $customer->Customer_ID)
            ->whereBetween('Effective_Date', $range->bounds())
            ->orderBy('Effective_Date')
            ->get();

        return $excel->customerInvoice($customer, $orders);
    }

    public function lockOrders(Request $request, Customer $customer): RedirectResponse
    {
        return $this->bulkStatus($request, $customer, 'lock', 'You have successfully locked this customer orders.');
    }

    public function payOrders(Request $request, Customer $customer): RedirectResponse
    {
        return $this->bulkStatus($request, $customer, 'archive', 'You have successfully paid this customer orders.');
    }

    private function bulkStatus(Request $request, Customer $customer, string $status, string $message): RedirectResponse
    {
        $request->validate(['from' => ['required', 'date_format:Y-m-d'], 'to' => ['required', 'date_format:Y-m-d']]);
        $range = DateRange::fromRequest(new Request($request->only('from', 'to')));

        $count = Order::where('Customer_ID', $customer->Customer_ID)
            ->whereBetween('Completed_Date', $range->bounds())
            ->update(['Order_Status' => config("binvoice.order_status.$status")]);

        return redirect()->route('customers.index', $range->query())
            ->with('success', "$message ($count orders)");
    }

    /** Monthly revenue of completed orders; customer=0 means all customers. */
    public function revenueChart(Request $request): View|JsonResponse
    {
        $range = DateRange::fromRequest($request);
        $customer = Customer::find((int) $request->query('customer'));
        $months = $range->months(12);

        $data = null;
        if ($months !== null) {
            $rows = Order::active()->status('completed')
                ->when($customer, fn ($q) => $q->where('Customer_ID', $customer->Customer_ID))
                ->whereBetween('Effective_Date', $range->bounds())
                ->selectRaw("DATE_FORMAT(Effective_Date, '%Y-%m') AS ym, SUM(Order_Price) AS revenue")
                ->groupBy('ym')
                ->pluck('revenue', 'ym');

            $data = [
                'labels' => array_values($months),
                'revenue' => array_map(fn ($ym) => round((float) ($rows[$ym] ?? 0), 2), array_keys($months)),
            ];
        }

        return view('customers.chart', compact('customer', 'range', 'data'));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'CompanyName' => ['required', 'string', 'max:150'],
            'ContactPerson' => ['required', 'string', 'max:150'],
            'Email' => ['required', 'email', 'max:150'],
            'Address' => ['nullable', 'string', 'max:255'],
            'Zip_Code' => ['nullable', 'string', 'max:20'],
            'City' => ['nullable', 'string', 'max:150'],
            'State' => ['nullable', 'string', 'max:60'],
            'Country' => ['nullable', 'string', 'max:150'],
            'Phone' => ['nullable', 'string', 'max:150'],
            'Min_Order_Value' => ['nullable', 'integer', 'min:0'],
            'Max_Order_Value' => ['nullable', 'integer', 'min:0'],
            'Normal_Fee' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'Urgent_Fee' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'Currency' => ['required', Rule::in(array_keys(config('binvoice.currencies')))],
            'Website_ID' => ['required', Rule::in(array_keys(config('binvoice.websites')))],
            'Is_Active' => ['required', Rule::in([1, 2])],
        ], [], [
            'CompanyName' => 'company name', 'ContactPerson' => 'contact person',
            'Min_Order_Value' => 'min no of stitches', 'Max_Order_Value' => 'max no of stitches',
            'Normal_Fee' => 'normal fee', 'Urgent_Fee' => 'urgent fee',
        ]);

        foreach (['Address', 'Zip_Code', 'City', 'State', 'Country', 'Phone'] as $field) {
            $data[$field] ??= '';
        }

        return $data;
    }
}
