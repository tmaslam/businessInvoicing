<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Sends each role to its start page, like legacy core/welcome.php.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        return match (true) {
            $user->hasRole('admin') => redirect()->route('customers.index'),
            $user->hasRole('csr') => redirect()->route('orders.index'),
            $user->hasRole('qa') => redirect()->route('orders.index'),
            default => redirect()->route('work.create'),
        };
    }
}
