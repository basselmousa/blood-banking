<?php

namespace App\Services;

use App\Models\Inventory;

class InventoryService
{
    /**
     * Get or create inventory record.
     */
    public function getOrCreateInventory($tenantId, $bloodType, $componentType = 'whole_blood')
    {
        return Inventory::firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'blood_type' => $bloodType,
                'component_type' => $componentType,
            ],
            [
                'quantity' => 0,
                'critical_level' => 5,
                'maximum_level' => 50,
            ]
        );
    }

    /**
     * Get inventory by blood type.
     */
    public function getByBloodType($tenantId, $bloodType)
    {
        return Inventory::where('tenant_id', $tenantId)
            ->where('blood_type', $bloodType)
            ->get();
    }

    /**
     * Get low stock items.
     */
    public function getLowStock($tenantId)
    {
        return Inventory::where('tenant_id', $tenantId)
            ->whereRaw('quantity <= critical_level')
            ->get();
    }

    /**
     * Get expiring soon items.
     */
    public function getExpiringSoon($tenantId, $days = 7)
    {
        return Inventory::where('tenant_id', $tenantId)
            ->whereBetween('expiration_date', [now(), now()->addDays($days)])
            ->get();
    }

    /**
     * Get expired items.
     */
    public function getExpired($tenantId)
    {
        return Inventory::where('tenant_id', $tenantId)
            ->where('expiration_date', '<', now())
            ->get();
    }

    /**
     * Get inventory summary.
     */
    public function getSummary($tenantId)
    {
        return [
            'total_units' => Inventory::where('tenant_id', $tenantId)->sum('quantity'),
            'low_stock_items' => count($this->getLowStock($tenantId)),
            'expiring_items' => count($this->getExpiringSoon($tenantId)),
            'expired_items' => count($this->getExpired($tenantId)),
            'blood_types' => $this->getBloodTypeBreakdown($tenantId),
        ];
    }

    /**
     * Get blood type breakdown.
     */
    public function getBloodTypeBreakdown($tenantId)
    {
        return Inventory::where('tenant_id', $tenantId)
            ->selectRaw('blood_type, SUM(quantity) as total, COUNT(*) as components')
            ->groupBy('blood_type')
            ->get()
            ->keyBy('blood_type');
    }

    /**
     * Check if sufficient stock for transfusion.
     */
    public function hasSufficientStock($tenantId, $bloodType, $units = 1)
    {
        $inventory = Inventory::where('tenant_id', $tenantId)
            ->where('blood_type', $bloodType)
            ->where('component_type', 'red_cells')
            ->first();

        return $inventory && $inventory->quantity >= $units;
    }

    /**
     * Get inventory alerts.
     */
    public function getAlerts($tenantId)
    {
        $alerts = [];

        // Low stock alerts
        foreach ($this->getLowStock($tenantId) as $item) {
            $alerts[] = [
                'type' => 'low_stock',
                'severity' => 'warning',
                'message' => "Low stock: {$item->blood_type} {$item->component_type}",
                'item_id' => $item->id,
            ];
        }

        // Expiring soon alerts
        foreach ($this->getExpiringSoon($tenantId) as $item) {
            $alerts[] = [
                'type' => 'expiring',
                'severity' => 'warning',
                'message' => "{$item->blood_type} expires on {$item->expiration_date->format('Y-m-d')}",
                'item_id' => $item->id,
            ];
        }

        // Expired alerts
        foreach ($this->getExpired($tenantId) as $item) {
            $alerts[] = [
                'type' => 'expired',
                'severity' => 'critical',
                'message' => "{$item->blood_type} expired on {$item->expiration_date->format('Y-m-d')}",
                'item_id' => $item->id,
            ];
        }

        return $alerts;
    }

    /**
     * Waste inventory.
     */
    public function waste($tenantId, $inventoryId, $quantity, $reason)
    {
        $inventory = Inventory::find($inventoryId);
        $inventory->removeQuantity($quantity, $reason);
    }

    /**
     * Adjust inventory.
     */
    public function adjust($tenantId, $inventoryId, $quantity, $reason)
    {
        $inventory = Inventory::find($inventoryId);
        if ($quantity > 0) {
            $inventory->addQuantity($quantity, $reason);
        } else {
            $inventory->removeQuantity(abs($quantity), $reason);
        }
    }
}
