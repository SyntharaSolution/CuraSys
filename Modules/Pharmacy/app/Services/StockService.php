<?php

namespace Modules\Pharmacy\Services;

use App\Models\StockTransaction;
use App\Models\MedicineBatch;
use App\Models\HeldBill;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class StockService
{
    /**
     * Get the physical stock quantity for a given batch.
     * Calculated by summing all transactions for the batch.
     */
    public function getPhysicalStock(int $batchId): int
    {
        $result = StockTransaction::where('batch_id', $batchId)
            ->select(DB::raw("SUM(quantity) as total"))
            ->first();

        return $result->total ? (int) $result->total : 0;
    }

    /**
     * Get the available stock quantity for a given batch.
     * Physical stock minus soft-reserved stock in unexpired held bills.
     */
    public function getAvailableStock(int $batchId): int
    {
        $physical = $this->getPhysicalStock($batchId);
        $reserved = $this->getSoftReservedStock($batchId);
        
        $available = $physical - $reserved;
        return $available > 0 ? $available : 0;
    }

    /**
     * Get the soft-reserved stock quantity for a given batch from held bills.
     */
    public function getSoftReservedStock(int $batchId): int
    {
        $unexpiredHeld = HeldBill::where('expires_at', '>', now())->get();
        $reserved = 0;

        foreach ($unexpiredHeld as $bill) {
            $cart = $bill->cart_json;
            if (is_array($cart)) {
                foreach ($cart as $item) {
                    // Cart JSON structure matches the frontend keys
                    // e.g. item.batch_id references batch UUID or ID
                    $itemBatch = MedicineBatch::where('uuid', $item['batch_id'] ?? '')
                        ->orWhere('id', $item['batch_id'] ?? 0)
                        ->first();

                    if ($itemBatch && $itemBatch->id === $batchId) {
                        $reserved += (int) ($item['quantity'] ?? 0);
                    }
                }
            }
        }

        return $reserved;
    }

    /**
     * Get active batches for a medicine ordered by FEFO (First-Expiry-First-Out).
     */
    public function getFefoBatches(int $medicineId): Collection
    {
        return MedicineBatch::where('medicine_id', $medicineId)
            ->where('status', 'active')
            ->whereDate('expiry_date', '>', now())
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    /**
     * Resolve batch allocation cascade for a requested quantity of a medicine.
     * Returns list of resolved batches and allocated quantities.
     */
    public function resolveBatchesForQuantity(int $medicineId, int $requestedQty): array
    {
        $batches = $this->getFefoBatches($medicineId);
        $allocation = [];
        $remaining = $requestedQty;

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $available = $this->getAvailableStock($batch->id);
            if ($available <= 0) {
                continue;
            }

            $allocated = min($remaining, $available);
            $allocation[] = [
                'batch_id' => $batch->id,
                'batch_uuid' => $batch->uuid,
                'batch_number' => $batch->batch_number,
                'quantity' => $allocated,
                'unit_price' => (float) $batch->selling_price,
            ];

            $remaining -= $allocated;
        }

        return [
            'allocation' => $allocation,
            'is_fulfilled' => $remaining === 0,
            'remaining_quantity' => $remaining,
        ];
    }

    /**
     * Log a stock deduction transaction.
     */
    public function deductStock(int $batchId, int $quantity, string $transactionType, string $referenceId, ?int $branchId = null, ?int $medicineId = null): StockTransaction
    {
        if (is_null($branchId) || is_null($medicineId)) {
            $batch = MedicineBatch::findOrFail($batchId);
            $branchId = $branchId ?? $batch->branch_id ?? 1;
            $medicineId = $medicineId ?? $batch->medicine_id;
        }

        return StockTransaction::create([
            'branch_id' => $branchId,
            'medicine_id' => $medicineId,
            'batch_id' => $batchId,
            'transaction_type' => $transactionType,
            'quantity' => -abs($quantity), // Negative for deduction
            'reference_id' => $referenceId,
            'created_by' => auth()->id()
        ]);
    }

    /**
     * Log a stock addition transaction.
     */
    public function addStock(int $batchId, int $quantity, string $transactionType, string $referenceId, ?int $branchId = null, ?int $medicineId = null): StockTransaction
    {
        if (is_null($branchId) || is_null($medicineId)) {
            $batch = MedicineBatch::findOrFail($batchId);
            $branchId = $branchId ?? $batch->branch_id ?? 1;
            $medicineId = $medicineId ?? $batch->medicine_id;
        }

        return StockTransaction::create([
            'branch_id' => $branchId,
            'medicine_id' => $medicineId,
            'batch_id' => $batchId,
            'transaction_type' => $transactionType,
            'quantity' => abs($quantity), // Positive for addition
            'reference_id' => $referenceId,
            'created_by' => auth()->id()
        ]);
    }
}
