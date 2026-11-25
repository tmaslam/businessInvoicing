<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Role-aware overview page. Money figures are only shown to admins.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $s = config('binvoice.order_status');
        $today = [today(), today()->endOfDay()];
        $month = [now()->startOfMonth(), now()->endOfMonth()];
        // Same period last month (1st .. same day/time), so month-to-date comparisons are fair.
        $lastMonth = [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()];
        $billable = [$s['completed'], $s['lock'], $s['archive']];

        $base = fn () => Order::query()->whereNull('Orders.End_Date');
        $openCount = $base()->whereIn('Order_Status', [$s['new'], $s['edit']])->count();

        $data = ['user' => $user, 'openCount' => $openCount];

        if ($user->hasRole('admin')) {
            $revenue = fn (array $range) => (float) $base()->whereIn('Order_Status', $billable)->whereBetween('Completed_Date', $range)->sum('Order_Price');
            $data += [
                'revenueMonth' => $revenue($month),
                'revenueLastMonth' => $revenue($lastMonth),
                'completedToday' => $base()->whereIn('Order_Status', $billable)->whereBetween('Completed_Date', $today)->count(),
                'completedMonth' => $base()->whereIn('Order_Status', $billable)->whereBetween('Completed_Date', $month)->count(),
                'addedToday' => $base()->whereBetween('Effective_Date', $today)->count(),
                'trend' => $this->trend($billable, null),
                'topCustomers' => $base()
                    ->join('Customers', 'Customers.Customer_ID', '=', 'Orders.Customer_ID')
                    ->whereIn('Orders.Order_Status', $billable)
                    ->whereBetween('Orders.Completed_Date', $month)
                    ->groupBy('Orders.Customer_ID', 'Customers.CompanyName', 'Customers.Customer_Unique_ID')
                    ->selectRaw('Customers.CompanyName as name, Customers.Customer_Unique_ID as code, COUNT(*) as orders, SUM(Orders.Order_Price) as revenue')
                    ->orderByDesc('revenue')->limit(5)->get(),
                'topDesigners' => $this->topDesigners($billable, $month),
                'recent' => $this->recentOrders(),
            ];
        } elseif ($user->hasRole('csr')) {
            $data += [
                'addedToday' => $base()->where('Assign_By', $user->User_Id)->whereBetween('Effective_Date', $today)->count(),
                'addedMonth' => $base()->where('Assign_By', $user->User_Id)->whereBetween('Effective_Date', $month)->count(),
                'completedToday' => $base()->whereIn('Order_Status', $billable)->whereBetween('Completed_Date', $today)->count(),
                'myCompletedMonth' => $base()->where('Completed_By', $user->User_Id)->whereBetween('Completed_Date', $month)->count(),
                'trend' => $this->trend($billable, null, false),
                'recent' => $this->recentOrders($user->User_Id),
            ];
        } elseif ($user->hasRole('qa')) {
            $data += [
                'completedToday' => $base()->whereIn('Order_Status', $billable)->whereBetween('Completed_Date', $today)->count(),
                'awaitingQa' => $base()->where('Order_Status', $s['completed'])->where('QA_By', 0)->whereBetween('Completed_Date', $month)->count(),
                'verifiedMonth' => $base()->where('QA_By', $user->User_Id)->whereBetween('Completed_Date', $month)->count(),
                'trend' => $this->trend($billable, null, false),
                'topDesigners' => $this->topDesigners($billable, $month),
                'recent' => $base()->where('Order_Status', $s['completed'])->where('QA_By', 0)
                    ->with('customer:Customer_ID,CompanyName')->orderByDesc('Completed_Date')->limit(8)->get(),
            ];
        } else {
            $mine = fn () => $base()->where('Completed_By', $user->User_Id)->whereIn('Order_Status', $billable);
            $data += [
                'myToday' => $mine()->whereBetween('Completed_Date', $today)->count(),
                'myMonth' => $mine()->whereBetween('Completed_Date', $month)->count(),
                'myLastMonth' => $mine()->whereBetween('Completed_Date', $lastMonth)->count(),
                'myStitches' => (int) $mine()->whereBetween('Completed_Date', $month)->sum('No_Of_Stiches'),
                'trend' => $this->trend($billable, $user->User_Id, false),
                'recent' => $mine()->orderByDesc('Completed_Date')->limit(8)->get(),
            ];
        }

        return view('dashboard', $data);
    }

    /** Last 12 months of completed orders (count, and revenue when $money). */
    private function trend(array $statuses, ?int $designerId, bool $money = true): array
    {
        $start = now()->subMonthsNoOverflow(11)->startOfMonth();
        $rows = Order::query()
            ->whereNull('End_Date')
            ->whereIn('Order_Status', $statuses)
            ->when($designerId, fn ($q) => $q->where('Completed_By', $designerId))
            ->where('Completed_Date', '>=', $start)
            ->selectRaw("DATE_FORMAT(Completed_Date, '%Y-%m') AS ym, COUNT(*) AS orders, SUM(Order_Price) AS revenue")
            ->groupBy('ym')->get()->keyBy('ym');

        $labels = $orders = $revenue = [];
        for ($m = $start->copy(); $m->lte(now()); $m->addMonth()) {
            $key = $m->format('Y-m');
            $labels[] = $m->format('M y');
            $orders[] = (int) ($rows[$key]->orders ?? 0);
            $revenue[] = round((float) ($rows[$key]->revenue ?? 0), 2);
        }

        return ['labels' => $labels, 'orders' => $orders, 'revenue' => $money ? $revenue : null];
    }

    private function topDesigners(array $statuses, array $range)
    {
        $rows = Order::query()
            ->whereNull('End_Date')->whereIn('Order_Status', $statuses)
            ->whereBetween('Completed_Date', $range)
            ->where('Completed_By', '>', 0)
            ->groupBy('Completed_By')
            ->selectRaw('Completed_By, COUNT(*) AS orders, SUM(No_Of_Stiches) AS stitches')
            ->orderByDesc('orders')->limit(5)->get();

        $names = User::whereIn('User_Id', $rows->pluck('Completed_By'))->get()->keyBy('User_Id');

        return $rows->map(fn ($r) => (object) [
            'name' => $names[$r->Completed_By]?->full_name ?? ('#'.$r->Completed_By),
            'orders' => (int) $r->orders,
            'stitches' => (int) $r->stitches,
        ]);
    }

    private function recentOrders(?int $assignedBy = null)
    {
        return Order::query()->whereNull('End_Date')
            ->when($assignedBy, fn ($q) => $q->where('Assign_By', $assignedBy))
            ->with('customer:Customer_ID,CompanyName')
            ->orderByDesc('Order_ID')->limit(8)->get();
    }

    public static function delta(float $now, float $before): ?string
    {
        if ($before <= 0) {
            return null;
        }
        $pct = ($now - $before) / $before * 100;

        return sprintf('<span class="%s"><i class="bi bi-arrow-%s"></i> %s%%</span> vs last month',
            $pct >= 0 ? 'text-success' : 'text-danger', $pct >= 0 ? 'up' : 'down', number_format(abs($pct), 1));
    }
}
