<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $user = User::active()
            ->where('User_Name', $data['username'])
            ->where('active_user', '1')
            ->first();

        if (! $user || ! $user->verifyAndUpgradePassword($data['password'])) {
            return back()->withInput($request->only('username'))
                ->withErrors(['username' => 'Invalid username or password.']);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showForgot(): View
    {
        return view('auth.forgot');
    }

    /**
     * Passwords are now hashed, so the old "email me my password" flow is replaced
     * by issuing a new random password to the email on file for that username.
     */
    public function sendNewPassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:100'],
        ]);

        $user = User::active()
            ->where('User_Name', $data['username'])
            ->where('Email', $data['email'])
            ->first();

        if ($user) {
            $newPassword = Str::password(12, symbols: false);
            $user->setPassword($newPassword);

            try {
                Mail::raw(
                    "Your Business Invoicing password has been reset.\n\nUsername: {$user->User_Name}\nNew password: {$newPassword}\n\nPlease change it after logging in.",
                    fn ($m) => $m->to($user->Email)->subject('Business Invoicing password reset')
                );
            } catch (\Throwable $e) {
                Log::error('Password reset mail failed', ['user' => $user->User_Id, 'error' => $e->getMessage()]);
            }
        }

        // Same message either way so usernames/emails cannot be enumerated.
        return back()->with('status', 'If the username and email match an account, a new password has been emailed.');
    }

    public function showChangePassword(): View
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = $request->user();
        if (! $user->verifyAndUpgradePassword($data['current_password'])) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->setPassword($data['password']);

        return redirect()->route('dashboard')->with('success', 'Password has been changed.');
    }
}
