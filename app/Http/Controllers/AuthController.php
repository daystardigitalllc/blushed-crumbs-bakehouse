<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Brand;
use App\Models\Tenant;
use App\Models\User;

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

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return $this->redirectUserAfterAuth();
        }

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
            'name' => $validated['bakery_name'],
            'slug' => $slug,
            'subdomain' => $subdomain,
            'owner_name' => $validated['owner_name'],
            'email' => $validated['email'],
            'plan_tier' => 'standard',
            'theme_id' => 'rustic_kitchen',
            'is_active' => true,
            'onboarding_completed' => false,
            'max_reviews_display' => 3,
        ]);

        // Create the owner user
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['owner_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'owner',
        ]);

        // Auto-login
        Auth::login($user);

        return $this->redirectUserAfterAuth();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
