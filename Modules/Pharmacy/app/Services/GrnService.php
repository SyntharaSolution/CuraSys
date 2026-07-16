<?php

namespace Modules\Pharmacy\Services;

use App\Models\GoodsReceivedNote;
use App\Models\GrnItem;
use App\Models\SupplierDebitNote;
use App\Models\MedicineBatch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GrnService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Submit a draft GRN and perform initial validation (tolerance, expiry).
     */
    public function processGrn(array $data, ?int $userId = null): GoodsReceivedNote
    {
        $userId = $userId ?? auth()->id() ?? 1;

        return DB::transaction(function () use ($data, $userId) {
            $po = null;
            if (!empty($data['purchase_order_id'])) {
                $po = Purchase::findOrFail($data['purchase_order_id']);
            }

            // Calculate Subtotal, Tax, and Grand Total
            $subtotal = collect($data['items'])->sum(fn($item) => $item['received_qty'] * $item['unit_cost']);
            $taxTotal = collect($data['items'])->sum(fn($item) => $item['tax'] ?? 0);
            $freight = (float) ($data['freight_charges'] ?? 0);
            $otherCharges = (float) ($data['other_charges'] ?? 0);
            $grandTotal = $subtotal + $taxTotal + $freight + $otherCharges;

            // 1. Landed Cost Allocation
            // Proportional allocation by value: landed_cost = unit_cost + (freight_allocated_pro_rata)
            $totalCharges = $freight + $otherCharges;

            // Create GRN Header
            $grn = GoodsReceivedNote::create([
                'grn_no' => 'GRN-' . strtoupper(Str::random(8)),
                'branch_id' => $data['branch_id'] ?? 1,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'purchase_order_id' => $po?->id,
                'supplier_id' => $data['supplier_id'],
                'supplier_invoice_no' => $data['supplier_invoice_no'] ?? null,
                'supplier_invoice_date' => $data['supplier_invoice_date'] ?? null,
                'delivery_note_no' => $data['delivery_note_no'] ?? null,
                'received_by' => $userId,
                'freight_charges' => $freight,
                'other_charges' => $otherCharges,
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,
                'variance_flag' => false,
                'status' => 'draft',
            ]);

            $hasVariance = false;

            foreach ($data['items'] as $item) {
                $orderedQty = (int) ($item['ordered_qty'] ?? 0);
                $receivedQty = (int) $item['received_qty'];
                $unitCost = (float) $item['unit_cost'];

                // 3-way match price check
                $poItem = null;
                if (!empty($item['purchase_order_item_id'])) {
                    $poItem = PurchaseItem::find($item['purchase_order_item_id']);
                }

                $poPrice = $poItem ? (float) $poItem->unit_cost : $unitCost;

                // Check Expiry Gate (default min shelf-life: 3 months)
                $expiryDate = Carbon::parse($item['expiry_date']);
                $minShelfLife = now()->addMonths(3);
                
                if ($expiryDate->lessThan($minShelfLife) && empty($data['override_expiry'])) {
                    throw new \Exception("Batch {$item['batch_no']} expires on {$item['expiry_date']}, which violates minimum 3-month shelf-life gate.");
                }

                // Check Tolerance Variance
                // Qty tolerance: ±5%
                // Cost tolerance: ±2%
                $qtyDiff = abs($receivedQty - $orderedQty);
                $qtyVariancePercent = $orderedQty > 0 ? ($qtyDiff / $orderedQty) * 100 : 0;

                $priceDiff = abs($unitCost - $poPrice);
                $priceVariancePercent = $poPrice > 0 ? ($priceDiff / $poPrice) * 100 : 0;

                if ($qtyVariancePercent > 5.0 || $priceVariancePercent > 2.0) {
                    $hasVariance = true;
                }

                // Calculate allocated landed cost
                $allocatedCharges = 0;
                if ($subtotal > 0 && $totalCharges > 0) {
                    $itemShare = ($receivedQty * $unitCost) / $subtotal;
                    $allocatedCharges = ($itemShare * $totalCharges) / ($receivedQty ?: 1);
                }
                $landedUnitCost = $unitCost + $allocatedCharges;

                $grnItem = GrnItem::create([
                    'grn_id' => $grn->id,
                    'purchase_order_item_id' => $poItem?->id,
                    'medicine_id' => $item['medicine_id'],
                    'ordered_qty' => $orderedQty,
                    'received_qty' => $receivedQty,
                    'free_qty' => $item['free_qty'] ?? 0,
                    'batch_no' => $item['batch_no'],
                    'mfg_date' => $item['mfg_date'] ?? null,
                    'expiry_date' => $item['expiry_date'],
                    'unit_cost' => $unitCost,
                    'selling_price' => $item['selling_price'] ?? ($unitCost * 1.20),
                    'landed_unit_cost' => $landedUnitCost,
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'line_total' => ($receivedQty * $unitCost) + ($item['tax'] ?? 0) - ($item['discount'] ?? 0),
                    'qc_status' => $item['qc_status'] ?? 'Pass',
                    'rejection_reason' => $item['rejection_reason'] ?? null,
                    'storage_location' => $item['storage_location'] ?? null,
                ]);

                // 2. QC Rejections handling
                if ($grnItem->qc_status === 'Fail') {
                    $this->issueSupplierDebitNote($grn, $grnItem, $userId);
                }
            }

            if ($hasVariance) {
                $grn->update([
                    'variance_flag' => true,
                    'status' => 'pending_approval',
                ]);
            } else {
                // Auto-approve if within tolerance
                $this->approveGrn($grn, $userId);
            }

            return $grn->load('items');
        });
    }

    /**
     * Approve GRN, update inventory and ledger.
     */
    public function approveGrn(GoodsReceivedNote $grn, int $userId): void
    {
        DB::transaction(function () use ($grn, $userId) {
            $grn->update([
                'status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

            foreach ($grn->items as $item) {
                // Skip updating inventory for QC fails
                if ($item->qc_status === 'Fail') {
                    continue;
                }

                // Cumulative update on PO Item
                if ($item->purchase_order_item_id) {
                    $poItem = PurchaseItem::findOrFail($item->purchase_order_item_id);
                    $newCumulative = $poItem->received_qty_cumulative + $item->received_qty;
                    $status = $newCumulative >= $poItem->quantity ? 'fulfilled' : 'partial';

                    $poItem->update([
                        'received_qty_cumulative' => $newCumulative,
                        'status' => $status,
                    ]);
                }

                // 4. Batch creation/incremental update
                // Find existing stock lot by matching medicine, price, and expiry
                $batch = MedicineBatch::where('medicine_id', $item->medicine_id)
                    ->where('purchase_price', (float) $item->unit_cost)
                    ->where('expiry_date', Carbon::parse($item->expiry_date)->toDateString())
                    ->first();

                $totalQtyAdded = $item->received_qty + $item->free_qty;

                if ($batch) {
                    $batch->update([
                        'selling_price' => $item->selling_price,
                        'landed_unit_cost' => $item->landed_unit_cost,
                        'qty_received' => $batch->qty_received + $totalQtyAdded,
                        'qty_available' => $batch->qty_available + $totalQtyAdded,
                    ]);
                    
                    // Sync the GRN item to use this resolved batch number
                    $item->update(['batch_no' => $batch->batch_number]);
                } else {
                    // Create a new distinct lot for this price/expiry combination
                    $batch = MedicineBatch::create([
                        'uuid' => (string) Str::uuid(),
                        'medicine_id' => $item->medicine_id,
                        'batch_number' => $item->batch_no,
                        'supplier_id' => $grn->supplier_id,
                        'grn_id' => $grn->id,
                        'mfg_date' => $item->mfg_date,
                        'expiry_date' => $item->expiry_date,
                        'purchase_price' => $item->unit_cost,
                        'selling_price' => $item->selling_price,
                        'landed_unit_cost' => $item->landed_unit_cost,
                        'qty_received' => $totalQtyAdded,
                        'qty_available' => $totalQtyAdded,
                        'status' => 'active',
                        'branch_id' => $grn->branch_id ?? 1,
                    ]);
                }

                // 5. Log Stock Transaction
                $this->stockService->addStock(
                    $batch->id,
                    $totalQtyAdded,
                    'in',
                    (string) $grn->grn_no,
                    $grn->branch_id ?? 1,
                    $item->medicine_id
                );
            }
        });
    }

    /**
     * Issue a Supplier Debit Note for failed QC items.
     */
    protected function issueSupplierDebitNote(GoodsReceivedNote $grn, GrnItem $item, int $userId): void
    {
        $amount = $item->received_qty * $item->unit_cost;

        SupplierDebitNote::create([
            'grn_id' => $grn->id,
            'supplier_id' => $grn->supplier_id,
            'medicine_id' => $item->medicine_id,
            'batch_no' => $item->batch_no,
            'qty' => $item->received_qty,
            'reason' => $item->rejection_reason ?? 'QC Failed',
            'amount' => $amount,
            'status' => 'draft',
        ]);
    }
}
