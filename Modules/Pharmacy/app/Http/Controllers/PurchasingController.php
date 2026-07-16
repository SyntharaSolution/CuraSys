<?php

namespace Modules\Pharmacy\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\MedicineBatch;
use Modules\Pharmacy\Services\StockService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PurchasingController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function getSuppliers()
    {
        return response()->json(['data' => Supplier::orderBy('created_at', 'desc')->get()]);
    }

    public function storeSupplier(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:suppliers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive'
        ]);

        $validated['uuid'] = Str::uuid();
        $validated['branch_id'] = 1; // Default for MVP

        $supplier = Supplier::create($validated);

        return response()->json(['message' => 'Supplier created successfully', 'data' => $supplier]);
    }

    public function updateSupplier(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:suppliers,email,' . $supplier->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive'
        ]);

        $supplier->update($validated);

        return response()->json(['message' => 'Supplier updated successfully', 'data' => $supplier]);
    }

    public function deleteSupplier($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted successfully']);
    }

    public function submitPurchase(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'reference_number' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.selling_price' => 'required|numeric|min:0',
            'items.*.batch_number' => 'required|string',
            'items.*.expiry_date' => 'required|date|after:today',
        ]);

        DB::transaction(function () use ($request) {
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += ($item['quantity'] * $item['unit_cost']);
            }

            $purchase = Purchase::create([
                'uuid' => Str::uuid(),
                'branch_id' => 1, // hardcoded for MVP
                'supplier_id' => $request->supplier_id,
                'reference_number' => $request->reference_number,
                'total_amount' => $totalAmount,
                'status' => 'received' // Automatically received for MVP GRN flow
            ]);

            foreach ($request->items as $item) {
                // 1. Log Purchase Item
                PurchaseItem::create([
                    'uuid' => Str::uuid(),
                    'purchase_id' => $purchase->id,
                    'medicine_id' => $item['medicine_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['quantity'] * $item['unit_cost'],
                    'batch_number' => $item['batch_number'],
                    'expiry_date' => $item['expiry_date']
                ]);

                // 2. Create Medicine Batch
                // Search for an existing active batch with this number for this medicine to merge, or create new
                $batch = MedicineBatch::where('medicine_id', $item['medicine_id'])
                    ->where('batch_number', $item['batch_number'])
                    ->first();

                if ($batch) {
                    // Update price in case it changed
                    $batch->update([
                        'purchase_price' => $item['unit_cost'],
                        'selling_price' => $item['selling_price'],
                        'expiry_date' => $item['expiry_date'] // update expiry if necessary
                    ]);
                } else {
                    $batch = MedicineBatch::create([
                        'uuid' => Str::uuid(),
                        'medicine_id' => $item['medicine_id'],
                        'batch_number' => $item['batch_number'],
                        'expiry_date' => $item['expiry_date'],
                        'purchase_price' => $item['unit_cost'],
                        'selling_price' => $item['selling_price'],
                        'branch_id' => 1
                    ]);
                }

                // 3. Update Stock Ledger (StockService handles StockTransaction)
                $this->stockService->addStock(
                    $batch->id,
                    $item['quantity'],
                    "in",
                    $purchase->id
                );
            }
        });

        return response()->json(['message' => 'Purchase recorded and stock updated successfully.']);
    }

    public function getPurchases()
    {
        $purchases = Purchase::with(['items.medicine' => function($q) {
            $q->select('id', 'name', 'generic_name');
        }])->orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $purchases]);
    }

    public function getExpiringBatches()
    {
        $batches = MedicineBatch::with(['medicine:id,name,generic_name,sku'])
            ->whereNotNull('expiry_date')
            ->orderBy('expiry_date', 'asc')
            ->get();
            
        return response()->json(['data' => $batches]);
    }
}
