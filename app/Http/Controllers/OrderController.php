<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use App\Models\Customer;
use App\Models\EditCode;
use App\Models\Order;
use App\Models\OrderHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Port of ajax-get-contents.php (menu "Order").
 */
class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'customer' => ['nullable', 'integer'],
            'order_id' => ['nullable', 'integer'],
            'design' => ['nullable', 'string', 'max:200'],
            'status' => ['nullable', Rule::in(array_values(config('binvoice.order_status')))],
        ]);

        $statuses = config('binvoice.order_status');
        $query = Order::query()
            ->join('Customers', 'Customers.Customer_ID', '=', 'Orders.Customer_ID')
            ->whereNull('Orders.End_Date')
            ->select('Orders.*', 'Customers.CompanyName as Customer_Name', 'Customers.Customer_Unique_ID');

        if (! array_filter($filters)) {
            // Default view: active statuses from the 1st of last month.
            $query->whereIn('Orders.Order_Status', [$statuses['new'], $statuses['completed'], $statuses['edit'], $statuses['lock']])
                ->where('Orders.Effective_Date', '>=', now()->subMonthNoOverflow()->startOfMonth());
        } else {
            $query->when($filters['customer'] ?? null, fn ($q, $v) => $q->where('Orders.Customer_ID', $v))
                ->when($filters['order_id'] ?? null, fn ($q, $v) => $q->where('Orders.Order_ID', $v))
                ->when($filters['design'] ?? null, fn ($q, $v) => $q->where('Orders.Design_Name', 'like', '%'.addcslashes($v, '%_\\').'%'))
                ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('Orders.Order_Status', $v));
        }

        $orders = $query->orderByDesc('Orders.Effective_Date')
            ->paginate(config('binvoice.per_page.orders'))
            ->withQueryString();

        $statusOptions = array_values($statuses);
        if (! $request->user()->isAdmin()) {
            $statusOptions = array_values(array_diff($statusOptions, [$statuses['lock']]));
        }

        return view('orders.index', [
            'orders' => $orders,
            'filters' => $filters,
            'customers' => $this->customers(),
            'statusOptions' => $statusOptions,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['customer', 'assigner', 'completer', 'qaUser', 'editCode']);

        return view('orders.show', compact('order'));
    }

    public function create(Request $request): View
    {
        return view('orders.form', [
            'order' => new Order(['Customer_ID' => $request->integer('customer') ?: null, 'Effective_Date' => now()]),
            'customers' => $this->customers(),
            'codes' => collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, false);

        $exists = Order::active()
            ->where('Design_Name', $data['Design_Name'])
            ->where('Customer_ID', $data['Customer_ID'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['Design_Name' => 'Order already exists.']);
        }

        Order::create([
            'Customer_ID' => $data['Customer_ID'],
            'Design_Name' => $data['Design_Name'],
            'Order_Status' => config('binvoice.order_status.new'),
            'Order_Status_ID' => 0,
            'Session_ID' => ActionLog::record('OrderInsert'),
            'Assign_By' => $request->user()->User_Id,
            'QA_By' => 0,
            'Effective_Date' => $this->orderDate($data['order_date']),
        ]);

        // Legacy reopened the "Add Order" form for the same customer after saving.
        return redirect()->route('orders.create', ['customer' => $data['Customer_ID']])
            ->with('success', 'Order has been successfully added.');
    }

    public function edit(Order $order): View
    {
        abort_if($order->End_Date !== null, 404);

        return view('orders.form', [
            'order' => $order,
            'customers' => $this->customers(),
            'codes' => EditCode::active()->orderBy('Narrative')->get(),
        ]);
    }

    /**
     * Editing an order with an edit code re-opens it ("In Progress") for the designer
     * and records the edit code in OrderHistory, as in the legacy app.
     */
    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $this->validated($request, true);
        $code = (int) ($data['Order_Status_ID'] ?? 0);

        DB::transaction(function () use ($order, $data, $code, $request) {
            OrderHistory::create(['Order_ID' => $order->Order_ID, 'Edit_Order_Staus_ID' => $code]);

            $order->update([
                'Customer_ID' => $data['Customer_ID'],
                'Design_Name' => $data['Design_Name'],
                'Order_Status' => config('binvoice.order_status.'.($code ? 'edit' : 'new')),
                'Order_Status_ID' => $code,
                'Session_ID' => ActionLog::record('OrderInsert'),
                'Assign_By' => $request->user()->User_Id,
                'Effective_Date' => $this->orderDate($data['order_date']),
            ]);
        });

        return redirect()->route('orders.index')->with('success', 'Order has been successfully updated.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->forceFill(['End_Date' => now()])->save();

        return redirect()->route('orders.index')->with('success', 'Order has been successfully deleted.');
    }

    /** QA marks a design as checked. */
    public function verify(Request $request, Order $order): RedirectResponse
    {
        $order->forceFill(['QA_By' => $request->user()->User_Id])->save();

        return redirect()->route('orders.index')->with('success', 'You have completed the design QA.');
    }

    private function validated(Request $request, bool $editing): array
    {
        return $request->validate([
            'Customer_ID' => ['required', 'integer', Rule::exists('Customers', 'Customer_ID')->whereNull('End_Date')],
            'Design_Name' => ['required', 'string', 'max:200'],
            'Order_Status_ID' => [$editing ? 'nullable' : 'prohibited', 'integer',
                fn ($attr, $value, $fail) => ($value && ! EditCode::active()->whereKey($value)->exists()) ? $fail('Invalid edit code.') : null],
            'order_date' => ['required', 'date_format:Y-m-d'],
        ], ['Customer_ID.required' => 'Please select Customer.', 'Design_Name.required' => 'Please enter Design Name.']);
    }

    /** Selected date + current time, like the legacy form. */
    private function orderDate(string $date): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.now()->format('H:i:s'));
    }

    private function customers()
    {
        return Customer::active()->orderBy('CompanyName')->get(['Customer_ID', 'CompanyName', 'Customer_Unique_ID']);
    }
}
