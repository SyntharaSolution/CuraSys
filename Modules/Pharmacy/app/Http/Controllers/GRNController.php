<?php

namespace Modules\Pharmacy\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GoodsReceivedNote;
use App\Models\Purchase;
use App\Models\SupplierDebitNote;
use Modules\Pharmacy\Services\GrnService;
use Illuminate\Http\JsonResponse;

class GRNController extends Controller
{
    protected GrnService $grnService;

    public function __construct(GrnService $grnService)
    {
        $this->grnService = $grnService;
    }

    /**
     * Get all Goods Received Notes.
     */
    public function index(Request $request): JsonResponse
    {
        $query = GoodsReceivedNote::with(['supplier', 'receiver', 'purchaseOrder']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return response()->json(['data' => $query->orderBy('created_at', 'desc')->get()]);
    }

    /**
     * Submit a new GRN.
     */
    public function submitGrn(Request $request): JsonResponse
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchases,id',
            'supplier_invoice_no' => 'nullable|string',
            'supplier_invoice_date' => 'nullable|date',
            'delivery_note_no' => 'nullable|string',
            'freight_charges' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.purchase_order_item_id' => 'nullable|exists:purchase_items,id',
            'items.*.ordered_qty' => 'required|integer|min:0',
            'items.*.received_qty' => 'required|integer|min:0',
            'items.*.free_qty' => 'nullable|integer|min:0',
            'items.*.batch_no' => 'required|string',
            'items.*.mfg_date' => 'nullable|date',
            'items.*.expiry_date' => 'required|date',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.qc_status' => 'required|in:Pass,Fail,Partial',
            'items.*.rejection_reason' => 'nullable|required_if:items.*.qc_status,Fail|string',
            'override_expiry' => 'nullable|boolean',
        ]);

        try {
            $grn = $this->grnService->processGrn($request->all(), auth()->id() ?? 1);
            return response()->json([
                'message' => $grn->status === 'pending_approval' 
                    ? 'GRN submitted, but flagged for manager approval due to PO variance.' 
                    : 'GRN processed and stock updated successfully.',
                'grn' => $grn
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'GRN processing failed',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get specific PO details with items.
     */
    public function getPoDetails($poId): JsonResponse
    {
        $po = Purchase::with(['items.medicine'])->findOrFail($poId);
        return response()->json(['data' => $po]);
    }

    /**
     * Manager approval override of flagged variance.
     */
    public function approveVariance(Request $request, $id): JsonResponse
    {
        $grn = GoodsReceivedNote::findOrFail($id);

        if ($grn->status !== 'pending_approval') {
            return response()->json(['message' => 'This GRN does not require variance approval.'], 400);
        }

        $userId = auth()->id() ?? 1;

        try {
            $this->grnService->approveGrn($grn, $userId);
            return response()->json(['message' => 'GRN variance approved. Inventory and ledger updated.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Approval failed', 'error' => $e->getMessage()], 422);
        }
    }

    /**
     * Get issued debit notes.
     */
    public function getDebitNotes(): JsonResponse
    {
        $notes = SupplierDebitNote::with(['grn', 'supplier', 'medicine'])->get();
        return response()->json(['data' => $notes]);
    }
}
