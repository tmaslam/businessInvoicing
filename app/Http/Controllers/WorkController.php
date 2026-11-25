<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use App\Models\Order;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Port of ajax-get-DOrder-contents.php (menu "Designer Order" / "Complete Order").
 */
class WorkController extends Controller
{
    /** Form where a designer/CSR completes an open (NEW / In Progress) order. */
    public function create(): View
    {
        return view('work.create');
    }

    /** JSON search of open orders by design name (replaces the old jQuery autocomplete). */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $orders = $this->openOrders()
            ->when($q !== '', fn ($query) => $query->where('Orders.Design_Name', 'like', '%'.addcslashes($q, '%_\\').'%'))
            ->orderByDesc('Orders.Effective_Date')
            ->limit(50)
            ->get();

        return response()->json($orders->map(fn (Order $o) => [
            'id' => $o->Order_ID,
            'text' => $o->Design_Name.' ('.$o->Effective_Date?->format('d/M/y').')',
            'status' => $o->Order_Status,
            'edit_code' => $o->Edit_Code,
            'customer' => $o->Customer_Name,
        ]));
    }

    public function store(Request $request, PricingService $pricing): RedirectResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'integer'],
            'stitches' => ['required', 'integer', 'min:1', 'max:100000000'],
        ], ['order_id.required' => 'Please select Design.', 'stitches.required' => 'Please enter No of Stiches.', 'stitches.min' => 'Please enter No of Stiches.']);

        $message = DB::transaction(function () use ($data, $pricing, $request) {
            $order = $this->openOrders()->where('Orders.Order_ID', $data['order_id'])->lockForUpdate()->first();
            if (! $order) {
                return null;
            }

            $order->load('customer.priceAttributes');
            $price = $order->customer ? $pricing->calculate($order->customer, (int) $data['stitches']) : 0;

            Order::whereKey($order->Order_ID)->update([
                'No_Of_Stiches' => $data['stitches'],
                'Order_Price' => $price,
                'Order_Status' => config('binvoice.order_status.completed'),
                'Completed_By' => $request->user()->User_Id,
                'Completed_Date' => now(),
                'Session_ID' => ActionLog::record('OrderCompleted'),
            ]);

            return 'You have completed the design "'.$order->Design_Name.'".';
        });

        if ($message === null) {
            return back()->withErrors(['order_id' => 'This order is no longer open. Please select another design.']);
        }

        return redirect()->route('work.create')->with('success', $message);
    }

    /** Completed orders: admin/QA see everyone's, designers/CSR see their own. */
    public function index(Request $request): View
    {
        $user = $request->user();
        $seeAll = $user->hasRole('admin', 'qa');

        $orders = Order::query()
            ->join('Customers', 'Customers.Customer_ID', '=', 'Orders.Customer_ID')
            ->whereNull('Orders.End_Date')
            ->where('Orders.Order_Status', config('binvoice.order_status.completed'))
            ->when(! $seeAll, fn ($q) => $q->where('Orders.Completed_By', $user->User_Id))
            ->when($request->filled('design'), fn ($q) => $q->where('Orders.Design_Name', 'like', '%'.addcslashes((string) $request->query('design'), '%_\\').'%'))
            ->when($seeAll && $request->filled('designer'), fn ($q) => $q->where('Orders.Completed_By', (int) $request->query('designer')))
            ->select('Orders.*', 'Customers.Customer_Unique_ID')
            ->orderByDesc('Orders.Completed_Date')
            ->paginate(config('binvoice.per_page.completed'))
            ->withQueryString();

        $todayCount = Order::query()
            ->whereNull('End_Date')
            ->where('Order_Status', config('binvoice.order_status.completed'))
            ->when(! $seeAll, fn ($q) => $q->where('Completed_By', $user->User_Id))
            ->whereBetween('Completed_Date', [today(), today()->endOfDay()])
            ->count();

        return view('work.index', [
            'orders' => $orders,
            'todayCount' => $todayCount,
            'canEditStitches' => $user->hasRole('admin', 'qa'),
            'showCustomer' => $user->isAdmin(),
            'prefixes' => config('binvoice.designer_prefixes'),
        ]);
    }

    /**
     * Admin/QA inline stitch correction. Setting 0 re-opens the order as NEW
     * (legacy updateStich behaviour). Price is kept as-is, like the old app.
     */
    public function updateStitches(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        $data = $request->validate(['stitches' => ['required', 'integer', 'min:0', 'max:100000000']]);

        if ((int) $data['stitches'] === 0) {
            $order->forceFill([
                'No_Of_Stiches' => 0,
                'Order_Status' => config('binvoice.order_status.new'),
                'Completed_By' => 0,
                'Completed_Date' => null,
            ])->save();
        } else {
            $order->forceFill(['No_Of_Stiches' => $data['stitches']])->save();
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'Stitches have been successfully updated.']);
        }

        return back()->with('success', 'Stitches have been successfully updated.');
    }

    private function openOrders()
    {
        $s = config('binvoice.order_status');

        return Order::query()
            ->leftJoin('EditCode', 'Orders.Order_Status_ID', '=', 'EditCode.Code_ID')
            ->leftJoin('Customers', 'Customers.Customer_ID', '=', 'Orders.Customer_ID')
            ->whereNull('Orders.End_Date')
            ->whereIn('Orders.Order_Status', [$s['new'], $s['edit']])
            ->select('Orders.*', 'EditCode.Narrative as Edit_Code', 'Customers.CompanyName as Customer_Name');
    }
}
