<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Port of ajax-get-profit-contents.php.
 * Total profit  = paid (Archive) orders completed in the business year
 * Earned profit = total profit - expenses of that year
 * Owner share   = 30% of earned profit, employees share the remaining 70%.
 */
class ProfitController extends Controller
{
    public function __invoke(Request $request): View
    {
        $year = (int) $request->query('year', now()->year);
        $year = max(2010, min($year, now()->year + 1));

        $from = Carbon::create($year)->startOfYear();
        $to = $from->copy()->endOfYear();

        $totalProfit = (float) Order::query()
            ->where('Order_Status', config('binvoice.order_status.archive'))
            ->whereBetween('Completed_Date', [$from, $to])
            ->sum('Order_Price');

        $totalExpense = (float) Expense::active()
            ->whereBetween('Expense_Month', [$from, $to])
            ->sum('Amount');

        $earned = $totalProfit - $totalExpense;
        $ownerPercent = (float) config('binvoice.owner_profit_percent');
        $owner = $earned * $ownerPercent / 100;

        return view('profit.index', [
            'year' => $year,
            'totalProfit' => $totalProfit,
            'totalExpense' => $totalExpense,
            'earned' => $earned,
            'owner' => $owner,
            'employee' => $earned - $owner,
            'ownerPercent' => $ownerPercent,
            'years' => range(now()->year + 1, 2010),
        ]);
    }
}
