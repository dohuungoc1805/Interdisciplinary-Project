<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Models\PurchaseReceipt;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function setStock(ProductVariant $variant, int $targetStock, string $referenceType = 'manual_adjustment', ?int $referenceId = null): void
    {
        DB::transaction(function () use ($variant, $targetStock, $referenceType, $referenceId) {
            $lockedVariant = ProductVariant::query()->lockForUpdate()->findOrFail($variant->id);
            $currentStock = (int) $lockedVariant->stock;
            $delta = $targetStock - $currentStock;
            if ($delta === 0) {
                return;
            }

            $lockedVariant->update(['stock' => $targetStock]);
            InventoryMovement::query()->create([
                'product_variant_id' => $lockedVariant->id,
                'type' => $delta > 0 ? 'in' : 'out',
                'quantity' => abs($delta),
                'unit_cost' => $lockedVariant->avg_cost,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'moved_at' => now(),
            ]);
        });
    }

    public function recordOut(ProductVariant $variant, int $quantity, string $referenceType, int $referenceId): void
    {
        InventoryMovement::query()->create([
            'product_variant_id' => $variant->id,
            'type' => 'out',
            'quantity' => $quantity,
            'unit_cost' => $variant->avg_cost,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'moved_at' => now(),
        ]);
    }

    public function recordReturn(ProductVariant $variant, int $quantity, string $referenceType, int $referenceId): void
    {
        InventoryMovement::query()->create([
            'product_variant_id' => $variant->id,
            'type' => 'return',
            'quantity' => $quantity,
            'unit_cost' => $variant->avg_cost,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'moved_at' => now(),
        ]);
    }

    public function receive(array $data): PurchaseReceipt
    {
        return DB::transaction(function () use ($data) {
            $receipt = PurchaseReceipt::query()->create([
                'code' => $data['code'],
                'supplier_name' => $data['supplier_name'] ?? null,
                'received_at' => $data['received_at'],
                'note' => $data['note'] ?? null,
                'total_cost' => 0,
            ]);

            $totalCost = 0;
            foreach ($data['items'] as $item) {
                $variant = ProductVariant::query()->lockForUpdate()->findOrFail($item['product_variant_id']);
                $quantity = (int) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];
                $lineTotal = $quantity * $unitCost;

                $receipt->items()->create([
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'line_total' => $lineTotal,
                ]);

                $currentStock = (int) $variant->stock;
                $currentAvgCost = (float) $variant->avg_cost;
                $newStock = $currentStock + $quantity;
                $newAvgCost = $newStock > 0
                    ? (($currentStock * $currentAvgCost) + ($quantity * $unitCost)) / $newStock
                    : $unitCost;

                $variant->update([
                    'stock' => $newStock,
                    'avg_cost' => $newAvgCost,
                    'last_cost' => $unitCost,
                ]);

                InventoryMovement::query()->create([
                    'product_variant_id' => $variant->id,
                    'type' => 'in',
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'reference_type' => 'purchase_receipt',
                    'reference_id' => $receipt->id,
                    'moved_at' => $receipt->received_at,
                ]);

                $totalCost += $lineTotal;
            }

            $receipt->update(['total_cost' => $totalCost]);

            return $receipt;
        });
    }
}
