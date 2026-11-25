<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use App\Models\Order;
use App\Models\User;
use App\Services\ExcelReports;
use App\Support\DateRange;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Port of ajax-get-user-contents.php. Admin manages Designers, CSRs and QAs;
 * QA users manage Designers only (and can block rather than delete).
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $roles = $this->manageableRoles($request->user());
        $roleFilter = (int) $request->query('role');

        $users = User::active()
            ->whereIn('Role_ID', $roleFilter && in_array($roleFilter, $roles, true) ? [$roleFilter] : $roles)
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $term = '%'.addcslashes((string) $request->query('q'), '%_\\').'%';
                $q->where(fn ($w) => $w->where('User_Name', 'like', $term)->orWhere('FName', 'like', $term)
                    ->orWhere('LName', 'like', $term)->orWhere('User_Id', (int) $request->query('q')));
            })
            ->orderByDesc('User_Id')
            ->paginate(config('binvoice.per_page.users'))
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'range' => DateRange::fromRequest($request),
            'roles' => $roles,
        ]);
    }

    public function create(Request $request): View
    {
        return view('users.form', [
            'user' => new User(['Role_ID' => 2, 'Joining_Date' => now()->toDateString()]),
            'roles' => $this->manageableRoles($request->user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);

        DB::transaction(function () use ($data) {
            $user = new User($data);
            $user->Password = Hash::make($data['password']);
            $user->active_user = '1';
            $user->Address = '';
            $user->Session_ID = ActionLog::record('UserInsert');
            $user->save();
        });

        return redirect()->route('users.index')->with('success', 'User has been successfully added.');
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorizeManage($request->user(), $user);

        return view('users.form', ['user' => $user, 'roles' => $this->manageableRoles($request->user())]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManage($request->user(), $user);
        $data = $this->validated($request, $user);

        unset($data['User_Name']); // username cannot be changed (legacy behaviour)
        $user->fill($data);
        if (! empty($data['password'])) {
            $user->Password = Hash::make($data['password']);
        }
        $user->save();

        return redirect()->route('users.index')->with('success', 'User has been successfully updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $this->authorizeManage($request->user(), $user);
        $user->forceFill(['End_Date' => now()])->save();

        return redirect()->route('users.index')->with('success', 'User has been successfully deleted.');
    }

    public function toggleBlock(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManage($request->user(), $user);
        $blocked = $user->isBlocked();
        $user->forceFill(['active_user' => $blocked ? '1' : '0'])->save();

        return back()->with('success', 'User has been successfully '.($blocked ? 'reactivated' : 'blocked').'.');
    }

    /** Orders handled by the user in a date range (legacy "view detail" / progress report). */
    public function report(Request $request, User $user): View
    {
        $this->authorizeView($request->user(), $user);
        $range = DateRange::fromRequest($request);
        $orders = $this->userOrders($user, $range)->get();

        return view('users.report', [
            'user' => $user,
            'orders' => $orders,
            'range' => $range,
            'showPrices' => $request->user()->isAdmin(),
        ]);
    }

    public function reportExcel(Request $request, User $user, ExcelReports $excel): StreamedResponse
    {
        $this->authorizeView($request->user(), $user);
        $range = DateRange::fromRequest($request);

        return $excel->userReport($user, $this->userOrders($user, $range)->get(), $range, $request->user()->isAdmin());
    }

    /** Monthly designs / mistakes / revenue chart. */
    public function chart(Request $request, User $user): View
    {
        $this->authorizeView($request->user(), $user);
        $range = DateRange::fromRequest($request);
        $months = $range->months(12);
        $data = null;

        if ($months !== null) {
            $column = $this->ownerColumn($user);
            [$from, $to] = $range->bounds();

            $rows = Order::query()
                ->whereNull('End_Date')
                ->where('Order_Status', config('binvoice.order_status.completed'))
                ->where($column, $user->User_Id)
                ->whereBetween('Effective_Date', [$from, $to])
                ->selectRaw("DATE_FORMAT(Effective_Date, '%Y-%m') AS ym, COUNT(*) AS designs, SUM(Order_Price) AS revenue")
                ->groupBy('ym')
                ->get()->keyBy('ym');

            $mistakes = DB::table('Orders')
                ->join('OrderHistory', 'OrderHistory.Order_ID', '=', 'Orders.Order_ID')
                ->join('EditCode', 'EditCode.Code_ID', '=', 'OrderHistory.Edit_Order_Staus_ID')
                ->whereNull('Orders.End_Date')
                ->where('Orders.Order_Status', config('binvoice.order_status.completed'))
                ->where("Orders.$column", $user->User_Id)
                ->where('EditCode.Code_Type', 'Designer')
                ->whereBetween('Orders.Effective_Date', [$from, $to])
                ->selectRaw("DATE_FORMAT(Orders.Effective_Date, '%Y-%m') AS ym, COUNT(*) AS mistakes")
                ->groupBy('ym')
                ->pluck('mistakes', 'ym');

            $keys = array_keys($months);
            $data = [
                'labels' => array_values($months),
                'designs' => array_map(fn ($k) => (int) ($rows[$k]->designs ?? 0), $keys),
                'mistakes' => array_map(fn ($k) => (int) ($mistakes[$k] ?? 0), $keys),
                'revenue' => $request->user()->isAdmin()
                    ? array_map(fn ($k) => round((float) ($rows[$k]->revenue ?? 0), 2), $keys)
                    : null,
            ];
        }

        return view('users.chart', compact('user', 'range', 'data'));
    }

    private function userOrders(User $user, DateRange $range)
    {
        return Order::query()
            ->whereNull('End_Date')
            ->where('Order_Status', config('binvoice.order_status.completed'))
            ->where($this->ownerColumn($user), $user->User_Id)
            ->whereBetween('Completed_Date', $range->bounds())
            ->orderBy('Effective_Date');
    }

    /** CSR -> orders they assigned, QA -> orders they checked, Designer -> orders they completed. */
    private function ownerColumn(User $user): string
    {
        return match ($user->Role_ID) {
            3 => 'Assign_By',
            4 => 'QA_By',
            default => 'Completed_By',
        };
    }

    /** @return int[] */
    private function manageableRoles(User $actor): array
    {
        return $actor->isAdmin() ? [2, 3, 4] : [2];
    }

    private function authorizeManage(User $actor, User $target): void
    {
        abort_unless(in_array($target->Role_ID, $this->manageableRoles($actor), true) && $target->End_Date === null, 403);
    }

    /** Reports: same role scope as management, but also allowed for removed users (history). */
    private function authorizeView(User $actor, User $target): void
    {
        abort_unless(in_array($target->Role_ID, $this->manageableRoles($actor), true), 403);
    }

    private function validated(Request $request, ?User $user): array
    {
        $data = $request->validate([
            'Role_ID' => ['required', 'integer', Rule::in($this->manageableRoles($request->user()))],
            'User_Name' => $user ? ['nullable'] : ['required', 'string', 'max:50', 'alpha_dash',
                Rule::unique('User', 'User_Name')->whereNull('End_Date')],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(6)],
            'FName' => ['required', 'string', 'max:60'],
            'LName' => ['required', 'string', 'max:60'],
            'Email' => ['required', 'email', 'max:100'],
            'Contact_No' => ['nullable', 'string', 'max:11'],
            'Profit_Distribution' => ['required', 'numeric', 'min:0'],
            'Joining_Date' => ['required', 'date_format:Y-m-d'],
        ], ['User_Name.unique' => 'This user name has been already taken.'], [
            'FName' => 'first name', 'LName' => 'last name', 'Contact_No' => 'telephone',
            'Profit_Distribution' => 'profit distribution', 'Role_ID' => 'user type',
        ]);
        $data['Contact_No'] ??= '';

        return $data;
    }
}
