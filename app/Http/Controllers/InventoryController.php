<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Tenant;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display inventory.
     */
    public function index(Request $request)
    {
        $tenant = auth()->user()->tenant;
        
        $query = Inventory::where('tenant_id', $tenant->id);

        if ($request->has('blood_type')) {
            $query->where('blood_type', $request->blood_type);
        }

        if ($request->has('component')) {
            $query->where('component_type', $request->component);
        }

        $inventory = $query->paginate(20);
        $summary = $this->inventoryService->getSummary($tenant->id);
        $alerts = $this->inventoryService->getAlerts($tenant->id);

        return view('inventory.index', compact('inventory', 'summary', 'alerts'));
    }

    /**
     * Show inventory details.
     */
    public function show(Inventory $inventory)
    {
        if ($inventory->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $transactions = $inventory->transactions()->latest()->paginate(20);

        return view('inventory.show', compact('inventory', 'transactions'));
    }

    /**
     * Update inventory.
     */
    public function update(Request $request, Inventory $inventory)
    {
        if ($inventory->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'expiration_date' => 'required|date',
            'storage_location' => 'required|string',
            'critical_level' => 'required|integer|min:0',
            'maximum_level' => 'required|integer|min:1',
        ]);

        $inventory->update($validated);

        return response()->json([
            'message' => 'Inventory updated successfully',
            'inventory' => $inventory,
        ]);
    }

    /**
     * Add to inventory.
     */
    public function add(Request $request, Inventory $inventory)
    {
        if ($inventory->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
        ]);

        $inventory->addQuantity($validated['quantity'], $validated['reason']);

        return response()->json(['message' => 'Inventory added successfully']);
    }

    /**
     * Remove from inventory.
     */
    public function remove(Request $request, Inventory $inventory)
    {
        if ($inventory->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
        ]);

        $inventory->removeQuantity($validated['quantity'], $validated['reason']);

        return response()->json(['message' => 'Inventory removed successfully']);
    }

    /**
     * Get inventory summary.
     */
    public function summary(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $summary = $this->inventoryService->getSummary($tenant->id);

        return response()->json($summary);
    }

    /**
     * Get inventory alerts.
     */
    public function alerts(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $alerts = $this->inventoryService->getAlerts($tenant->id);

        return response()->json(['alerts' => $alerts]);
    }

    /**
     * Get low stock items.
     */
    public function lowStock(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $items = $this->inventoryService->getLowStock($tenant->id);

        return response()->json($items);
    }

    /**
     * Get expiring soon items.
     */
    public function expiringSoon(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $days = $request->query('days', 7);
        $items = $this->inventoryService->getExpiringSoon($tenant->id, $days);

        return response()->json($items);
    }

    /**
     * Waste inventory.
     */
    public function waste(Request $request, Inventory $inventory)
    {
        if ($inventory->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
        ]);

        $this->inventoryService->waste(
            auth()->user()->tenant_id,
            $inventory->id,
            $validated['quantity'],
            $validated['reason']
        );

        return response()->json(['message' => 'Inventory wasted']);
    }

    /**
     * Export inventory report.
     */
    public function export(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $inventory = Inventory::where('tenant_id', $tenant->id)->get();

        $csv = "Blood Type,Component,Quantity,Expiration Date,Storage Location\n";
        foreach ($inventory as $item) {
            $csv .= "{$item->blood_type},{$item->component_type},{$item->quantity},{$item->expiration_date},{$item->storage_location}\n";
        }

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'inventory-' . now()->format('Y-m-d') . '.csv');
    }
}
