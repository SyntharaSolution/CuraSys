<?php
$supplier = \App\Models\Supplier::create(['uuid' => \Illuminate\Support\Str::uuid(), 'branch_id' => 1, 'name' => 'Test Supplier', 'status' => 'active']);
$medicine = \App\Models\Medicine::first();

$request = new \Illuminate\Http\Request();
$request->merge([
    'supplier_id' => $supplier->id,
    'reference_number' => 'INV-1001',
    'items' => [
        [
            'medicine_id' => $medicine->id,
            'quantity' => 100,
            'unit_cost' => 10.50,
            'selling_price' => 15.00,
            'batch_number' => 'BCH-TEST1',
            'expiry_date' => now()->addMonths(6)->toDateString()
        ]
    ]
]);

$controller = app(\Modules\Pharmacy\Http\Controllers\PurchasingController::class);
$response = $controller->submitPurchase($request);
echo $response->getContent();
