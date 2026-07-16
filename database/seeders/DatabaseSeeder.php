<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\MedicineBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Pharmacy\Services\StockService;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
        ]);

        // 1. Seed default Branch
        $branch = Branch::firstOrCreate(
            ['name' => 'Main Clinic Pharmacy'],
            [
                'uuid' => (string) Str::uuid(),
                'is_main_branch' => true
            ]
        );

        // 2. Seed Users
        $admin = User::updateOrCreate(
            ['email' => 'admin@curasys.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'branch_id' => $branch->id,
                'uuid' => (string) Str::uuid()
            ]
        );
        $admin->assignRole('Super Admin');

        $cashier = User::updateOrCreate(
            ['email' => 'cashier@curasys.com'],
            [
                'name' => 'John Cashier',
                'password' => Hash::make('password'),
                'branch_id' => $branch->id,
                'uuid' => (string) Str::uuid()
            ]
        );
        $cashier->assignRole('Cashier');

        // Seed Sales Person
        $salesPerson = User::updateOrCreate(
            ['email' => 'sales@curasys.com'],
            [
                'name' => 'Sara Sales',
                'password' => Hash::make('password'),
                'login_code' => '4321',
                'branch_id' => $branch->id,
                'uuid' => (string) Str::uuid()
            ]
        );
        $salesPerson->assignRole('Sales Person');

        // 3. Seed Categories & Medicines
        $catAnalgesics = MedicineCategory::firstOrCreate(
            ['name' => 'Analgesics'],
            ['uuid' => (string) Str::uuid()]
        );

        $catCosmetics = MedicineCategory::firstOrCreate(
            ['name' => 'Beauty & Cosmetics'],
            ['uuid' => (string) Str::uuid()]
        );

        $catGroceries = MedicineCategory::firstOrCreate(
            ['name' => 'Groceries & OTC'],
            ['uuid' => (string) Str::uuid()]
        );

        // Medicines
        $med1 = Medicine::firstOrCreate(
            ['name' => 'Ibuprofen 400mg'],
            [
                'generic_name' => 'Ibuprofen',
                'category_id' => $catAnalgesics->id,
                'branch_id' => $branch->id,
                'uuid' => (string) Str::uuid()
            ]
        );

        $med2 = Medicine::firstOrCreate(
            ['name' => 'Amoxicillin 500mg'],
            [
                'generic_name' => 'Amoxicillin',
                'category_id' => $catAnalgesics->id,
                'branch_id' => $branch->id,
                'uuid' => (string) Str::uuid()
            ]
        );

        // Cosmetics
        $med3 = Medicine::firstOrCreate(
            ['name' => 'Neutrogena Sunscreen SPF 50'],
            [
                'generic_name' => 'Sunscreen Cream',
                'category_id' => $catCosmetics->id,
                'branch_id' => $branch->id,
                'uuid' => (string) Str::uuid()
            ]
        );

        $med4 = Medicine::firstOrCreate(
            ['name' => 'CeraVe Moisturizing Lotion'],
            [
                'generic_name' => 'Moisturizer',
                'category_id' => $catCosmetics->id,
                'branch_id' => $branch->id,
                'uuid' => (string) Str::uuid()
            ]
        );

        // Groceries
        $med5 = Medicine::firstOrCreate(
            ['name' => 'Evian Mineral Water 1L'],
            [
                'generic_name' => 'Mineral Water',
                'category_id' => $catGroceries->id,
                'branch_id' => $branch->id,
                'uuid' => (string) Str::uuid()
            ]
        );

        $med6 = Medicine::firstOrCreate(
            ['name' => 'Ensure Nutrition Drink'],
            [
                'generic_name' => 'Nutrition Drink',
                'category_id' => $catGroceries->id,
                'branch_id' => $branch->id,
                'uuid' => (string) Str::uuid()
            ]
        );

        // 4. Seed Batches and Stock
        $batch1 = MedicineBatch::firstOrCreate(
            ['batch_number' => 'BCH-IBU01'],
            [
                'medicine_id' => $med1->id,
                'expiry_date' => now()->addMonths(12)->toDateString(),
                'purchase_price' => 2.50,
                'selling_price' => 5.00,
                'status' => 'active',
                'branch_id' => $branch->id,
                'uuid' => (string) Str::uuid()
            ]
        );

        $batch2 = MedicineBatch::firstOrCreate(
            ['batch_number' => 'BCH-AMX01'],
            [
                'medicine_id' => $med2->id,
                'expiry_date' => now()->addMonths(18)->toDateString(),
                'purchase_price' => 4.00,
                'selling_price' => 8.50,
                'status' => 'active',
                'branch_id' => $branch->id,
                'uuid' => (string) Str::uuid()
            ]
        );

        $batch3 = MedicineBatch::firstOrCreate(
            ['batch_number' => 'BCH-SUN01'],
            [
                'medicine_id' => $med3->id,
                'expiry_date' => now()->addMonths(24)->toDateString(),
                'purchase_price' => 7.50,
                'selling_price' => 15.00,
                'status' => 'active',
                'branch_id' => $branch->id,
                'uuid' => (string) Str::uuid()
            ]
        );

        $batch4 = MedicineBatch::firstOrCreate(
            ['batch_number' => 'BCH-CER01'],
            [
                'medicine_id' => $med4->id,
                'expiry_date' => now()->addMonths(24)->toDateString(),
                'purchase_price' => 9.00,
                'selling_price' => 18.50,
                'status' => 'active',
                'branch_id' => $branch->id,
                'uuid' => (string) Str::uuid()
            ]
        );

        $batch5 = MedicineBatch::firstOrCreate(
            ['batch_number' => 'BCH-EVI01'],
            [
                'medicine_id' => $med5->id,
                'expiry_date' => now()->addMonths(36)->toDateString(),
                'purchase_price' => 1.00,
                'selling_price' => 2.50,
                'status' => 'active',
                'branch_id' => $branch->id,
                'uuid' => (string) Str::uuid()
            ]
        );

        $batch6 = MedicineBatch::firstOrCreate(
            ['batch_number' => 'BCH-ENS01'],
            [
                'medicine_id' => $med6->id,
                'expiry_date' => now()->addMonths(12)->toDateString(),
                'purchase_price' => 2.00,
                'selling_price' => 4.50,
                'status' => 'active',
                'branch_id' => $branch->id,
                'uuid' => (string) Str::uuid()
            ]
        );

        // Add physical stock to batches
        $stockService = resolve(StockService::class);
        $stockService->addStock($batch1->id, 500, 'in', 'INIT-STOCK-IBU');
        $stockService->addStock($batch2->id, 500, 'in', 'INIT-STOCK-AMX');
        $stockService->addStock($batch3->id, 200, 'in', 'INIT-STOCK-SUN');
        $stockService->addStock($batch4->id, 200, 'in', 'INIT-STOCK-CER');
        $stockService->addStock($batch5->id, 300, 'in', 'INIT-STOCK-EVI');
        $stockService->addStock($batch6->id, 150, 'in', 'INIT-STOCK-ENS');

        // 5. Seed Customer
        $customer = Customer::firstOrCreate(
            ['customer_code' => 'CUST-WALK'],
            [
                'name' => 'Walk-in Customer',
                'customer_type' => 'walk-in'
            ]
        );

        // 6. Create Pending Bills
        $sale1 = Sale::create([
            'invoice_no' => 'INV-' . strtoupper(Str::random(6)),
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'user_id' => $salesPerson->id,
            'total_amount' => 50.00,
            'discount_amount' => 0.00,
            'tax_amount' => 0.00,
            'net_total' => 50.00,
            'status' => 'pending',
            'uuid' => (string) Str::uuid()
        ]);

        SaleItem::create([
            'sale_uuid' => $sale1->uuid,
            'medicine_id' => $med1->id,
            'batch_id' => $batch1->id,
            'quantity' => 10,
            'unit_price' => 5.00,
            'subtotal' => 50.00,
            'uuid' => (string) Str::uuid()
        ]);

        $sale2 = Sale::create([
            'invoice_no' => 'INV-' . strtoupper(Str::random(6)),
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'user_id' => $salesPerson->id,
            'total_amount' => 85.00,
            'discount_amount' => 5.00,
            'tax_amount' => 0.00,
            'net_total' => 80.00,
            'status' => 'pending',
            'uuid' => (string) Str::uuid()
        ]);

        SaleItem::create([
            'sale_uuid' => $sale2->uuid,
            'medicine_id' => $med2->id,
            'batch_id' => $batch2->id,
            'quantity' => 10,
            'unit_price' => 8.50,
            'subtotal' => 85.00,
            'uuid' => (string) Str::uuid()
        ]);

        $sale3 = Sale::create([
            'invoice_no' => 'INV-' . strtoupper(Str::random(6)),
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'user_id' => $salesPerson->id,
            'total_amount' => 75.00,
            'discount_amount' => 0.00,
            'tax_amount' => 0.00,
            'net_total' => 75.00,
            'status' => 'pending',
            'uuid' => (string) Str::uuid()
        ]);

        SaleItem::create([
            'sale_uuid' => $sale3->uuid,
            'medicine_id' => $med1->id,
            'batch_id' => $batch1->id,
            'quantity' => 15,
            'unit_price' => 5.00,
            'subtotal' => 75.00,
            'uuid' => (string) Str::uuid()
        ]);

        $sale4 = Sale::create([
            'invoice_no' => 'INV-' . strtoupper(Str::random(6)),
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'user_id' => $salesPerson->id,
            'total_amount' => 42.50,
            'discount_amount' => 0.00,
            'tax_amount' => 0.00,
            'net_total' => 42.50,
            'status' => 'pending',
            'uuid' => (string) Str::uuid()
        ]);

        SaleItem::create([
            'sale_uuid' => $sale4->uuid,
            'medicine_id' => $med2->id,
            'batch_id' => $batch2->id,
            'quantity' => 5,
            'unit_price' => 8.50,
            'subtotal' => 42.50,
            'uuid' => (string) Str::uuid()
        ]);

        $sale5 = Sale::create([
            'invoice_no' => 'INV-' . strtoupper(Str::random(6)),
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'user_id' => $salesPerson->id,
            'total_amount' => 100.00,
            'discount_amount' => 0.00,
            'tax_amount' => 0.00,
            'net_total' => 100.00,
            'status' => 'pending',
            'uuid' => (string) Str::uuid()
        ]);

        SaleItem::create([
            'sale_uuid' => $sale5->uuid,
            'medicine_id' => $med1->id,
            'batch_id' => $batch1->id,
            'quantity' => 20,
            'unit_price' => 5.00,
            'subtotal' => 100.00,
            'uuid' => (string) Str::uuid()
        ]);
    }
}
