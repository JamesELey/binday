<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['No account found with this email address.'],
            ]);
        }

        if (!$user->active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Please contact an administrator.'],
            ]);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        // Redirect based on user role
        return $this->redirectAfterLogin($user);
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role' => 'nullable|in:customer,worker', // Admin accounts created manually
        ]);

        // Default to customer role if not specified
        $validated['role'] = $validated['role'] ?? 'customer';
        $validated['active'] = true;

        $user = User::create($validated);

        Auth::login($user);

        return $this->redirectAfterLogin($user);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Redirect user after login based on their role
     */
    private function redirectAfterLogin(User $user)
    {
        $intended = session()->pull('url.intended', '/');

        switch ($user->role) {
            case 'admin':
                return redirect()->intended('/admin/dashboard')->with('success', "Welcome back, {$user->name}! (Admin)");
            
            case 'worker':
                return redirect()->intended('/worker/dashboard')->with('success', "Welcome back, {$user->name}! (Worker)");
            
            case 'customer':
                return redirect()->intended('/customer/dashboard')->with('success', "Welcome back, {$user->name}!");
            
            default:
                return redirect()->intended($intended)->with('success', "Welcome back, {$user->name}!");
        }
    }

    /**
     * Show user dashboard based on role
     */
    public function dashboard()
    {
        $user = auth()->user();

        switch ($user->role) {
            case 'admin':
                return view('dashboard.admin', compact('user'));
            
            case 'worker':
                return view('dashboard.worker', compact('user'));
            
            case 'customer':
                return view('dashboard.customer', compact('user'));
            
            default:
                return redirect('/');
        }
    }
}