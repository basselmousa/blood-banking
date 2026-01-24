<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Display a listing of tenants.
     */
    public function index()
    {
        $tenants = Tenant::paginate(15);
        return view('tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new tenant.
     */
    public function create()
    {
        return view('tenants.create');
    }

    /**
     * Store a newly created tenant in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|unique:tenants',
            'plan' => 'required|in:starter,professional,enterprise',
        ]);

        $tenant = $this->tenantService->createTenant($validated);

        return redirect()->route('tenants.show', $tenant)
            ->with('success', 'Tenant created successfully!');
    }

    /**
     * Display the specified tenant.
     */
    public function show(Tenant $tenant)
    {
        $usage = $this->tenantService->getUsageStatistics($tenant);
        return view('tenants.show', compact('tenant', 'usage'));
    }

    /**
     * Show the form for editing the tenant.
     */
    public function edit(Tenant $tenant)
    {
        return view('tenants.edit', compact('tenant'));
    }

    /**
     * Update the specified tenant in storage.
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|unique:tenants,domain,' . $tenant->id,
        ]);

        $tenant->update($validated);

        return redirect()->route('tenants.show', $tenant)
            ->with('success', 'Tenant updated successfully!');
    }

    /**
     * Activate a tenant.
     */
    public function activate(Tenant $tenant)
    {
        $tenant->update(['is_active' => true]);

        return redirect()->route('tenants.show', $tenant)
            ->with('success', 'Tenant activated successfully!');
    }

    /**
     * Deactivate a tenant.
     */
    public function deactivate(Tenant $tenant)
    {
        $tenant->update(['is_active' => false]);

        return redirect()->route('tenants.show', $tenant)
            ->with('success', 'Tenant deactivated successfully!');
    }

    /**
     * Delete the specified tenant.
     */
    public function destroy(Tenant $tenant)
    {
        $tenant->delete();

        return redirect()->route('tenants.index')
            ->with('success', 'Tenant deleted successfully!');
    }
}
