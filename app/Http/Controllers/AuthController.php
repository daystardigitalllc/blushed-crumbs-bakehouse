<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\Brand;
use App\Models\Tenant;
use App\Models\User;
use App\Models\AuditLog;

class AuthController extends Controller
{
    private function redirectUserAfterAuth()
    {
        $user = Auth::user();
        if (!$user) return redirect('/login');

        if ($user->isSuperAdmin()) {
            return redirect('/admin');
        }

        if ($user->tenant && !$user->tenant->onboarding_completed) {
            return redirect('/onboarding');
        }

        return redirect('/dashboard');
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectUserAfterAuth();
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Security: Rate limiting / brute-force protection (max 5 attempts per minute per IP + email)
        $throttleKey = Str::lower($credentials['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            AuditLog::logEvent('auth.login.throttled', null, null, [
                'email' => $credentials['email'],
                'seconds' => $seconds,
            ], 'warning');

            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $user = Auth::user();
            AuditLog::logEvent('auth.login.success', $user->tenant_id, $user->id, [
                'email' => $user->email,
            ]);

            return $this->redirectUserAfterAuth();
        }

        RateLimiter::hit($throttleKey, 60);

        AuditLog::logEvent('auth.login.failed', null, null, [
            'email' => $credentials['email'],
        ], 'warning');

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'owner_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'bakery_name' => 'required|string|max:255',
        ]);

        // Generate a unique slug/subdomain from the bakery name
        $slug = Str::slug($validated['bakery_name'], '');
        $subdomain = Str::slug($validated['bakery_name'], '');

        // Ensure uniqueness
        $baseSlug = $slug;
        $counter = 1;
        while (Tenant::where('slug', $slug)->orWhere('subdomain', $subdomain)->exists()) {
            $slug = $baseSlug . $counter;
            $subdomain = $baseSlug . $counter;
            $counter++;
        }

        // Find the doughmain brand (default for now)
        $brand = Brand::where('slug', 'doughmain')->first();

        // Create the tenant
        $tenant = Tenant::create([
            'brand_id' => $brand?->id,
            'name' => strip_tags($validated['bakery_name']),
            'slug' => $slug,
            'subdomain' => $subdomain,
            'owner_name' => strip_tags($validated['owner_name']),
            'email' => filter_var($validated['email'], FILTER_SANITIZE_EMAIL),
            'plan_tier' => 'standard',
            'theme_id' => 'rustic_kitchen',
            'is_active' => true,
            'onboarding_completed' => false,
            'max_reviews_display' => 3,
        ]);

        // Create the owner user
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => strip_tags($validated['owner_name']),
            'email' => filter_var($validated['email'], FILTER_SANITIZE_EMAIL),
            'password' => Hash::make($validated['password']),
            'role' => 'owner',
        ]);

        AuditLog::logEvent('auth.register', $tenant->id, $user->id, [
            'bakery_name' => $tenant->name,
            'subdomain' => $tenant->subdomain,
        ]);

        // Auto-login
        Auth::login($user);

        return $this->redirectUserAfterAuth();
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::logEvent('auth.logout', $user->tenant_id, $user->id);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    // ─── Forgot Password & Reset ───

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = \Illuminate\Support\Facades\Password::broker()->sendResetLink(
            $request->only('email')
        );
        return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
                    ? back()->with(['status' => __($status)])
                    : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request, $token)
    {
        return view('auth.reset-password', ['request' => $request, 'token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = \Illuminate\Support\Facades\Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
                $user->save();
                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withErrors(['email' => [__($status)]]);
    }
}
