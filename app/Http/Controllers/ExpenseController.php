<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Port of ajax-get-expense-contents.php. */
class ExpenseController extends Controller
{
    public function index(): View
    {
        return view('expenses.index', [
            'expenses' => Expense::active()->orderByDesc('Expense_Month')->orderByDesc('ID')
                ->paginate(config('binvoice.per_page.expenses')),
        ]);
    }

    public function create(): View
    {
        return view('expenses.form', ['expense' => new Expense(['Expense_Month' => now()->startOfMonth()])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Expense::create($this->validated($request) + ['Session_ID' => ActionLog::record('ExpenseInsert')]);

        return redirect()->route('expenses.index')->with('success', 'Expense has been successfully added.');
    }

    public function edit(Expense $expense): View
    {
        return view('expenses.form', compact('expense'));
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $expense->update($this->validated($request) + ['Session_ID' => ActionLog::record('ExpenseInsert')]);

        return redirect()->route('expenses.index')->with('success', 'Expense has been successfully updated.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->forceFill(['End_Date' => now()])->save();

        return redirect()->route('expenses.index')->with('success', 'Expense has been successfully deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'Narrative' => ['required', 'string', 'max:50'],
            'Amount' => ['required', 'numeric', 'min:0'],
            'month' => ['required', 'date_format:Y-m'],
        ], [], ['Narrative' => 'expense name']);

        return [
            'Narrative' => $data['Narrative'],
            'Amount' => $data['Amount'],
            'Expense_Month' => $data['month'].'-01 00:00:00',
        ];
    }
}
