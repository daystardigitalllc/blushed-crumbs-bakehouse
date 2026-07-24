<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;

class BrandController extends Controller
{
    /**
     * Serve the brand marketing/landing page.
     * This is hit when a user visits doughmain.pro (the main SaaS domain).
     */
    public function landing(Request $request)
    {
        $brand = Brand::where('slug', 'doughmain')->first();

        return view('brand.landing', [
            'brand' => $brand,
            'pricing' => $brand?->pricing_plans ?? [
                'free' => ['name' => 'Free Baker', 'price' => 0],
                'pro' => ['name' => 'Pro Baker', 'price' => 29],
            ],
        ]);
    }

    /**
     * Platform Super Admin Dashboard (/admin).
     * Manage all bakery subscriptions, tenants, users, and platform settings.
     */
    public function superAdminDashboard(Request $request)
    {
        $tenants = \App\Models\Tenant::latest()->get();
        $users = \App\Models\User::with('tenant')->latest()->get();
        $tickets = \App\Models\SupportTicket::with('tenant')->latest()->get();
        $ordersCount = \App\Models\Order::count();

        // Calculate platform MRR
        $mrr = $tenants->reduce(function ($total, $t) {
            if ($t->plan_tier === 'pro') return $total + 50;
            if ($t->plan_tier === 'standard') return $total + 29;
            return $total;
        }, 0);

        return view('admin.brand_admin', [
            'tenants' => $tenants,
            'users' => $users,
            'tickets' => $tickets,
            'mrr' => $mrr,
            'ordersCount' => $ordersCount,
        ]);
    }

    /**
     * Toggle tenant active status.
     */
    public function toggleTenantStatus(Request $request, \App\Models\Tenant $tenant)
    {
        $tenant->is_active = !$tenant->is_active;
        $tenant->save();

        return redirect()->back()->with('success', "Bakery {$tenant->name} status updated!");
    }

    /**
     * Update user role.
     */
    public function updateUserRole(Request $request, \App\Models\User $user)
    {
        $validated = $request->validate([
            'role' => 'required|string|in:owner,admin,staff,superadmin',
        ]);

        $user->role = $validated['role'];
        $user->save();

        return redirect()->back()->with('success', "Role for {$user->name} updated to {$user->role}!");
    }

    /**
     * Delete user account.
     */
    public function deleteUser(\App\Models\User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own active superadmin account.');
        }

        $user->delete();
        return redirect()->back()->with('success', 'User account deleted.');
    }

    /**
     * Update support ticket status.
     */
    public function updateTicketStatus(Request $request, \App\Models\SupportTicket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:open,in_progress,resolved',
        ]);

        $ticket->status = $validated['status'];
        $ticket->save();

        return redirect()->back()->with('success', 'Support ticket status updated!');
    }

    /**
     * Platform Privacy Policy.
     */
    public function privacy(Request $request)
    {
        return view('legal.privacy');
    }

    /**
     * Platform Terms & Conditions.
     */
    public function terms(Request $request)
    {
        return view('legal.terms');
    }
}
