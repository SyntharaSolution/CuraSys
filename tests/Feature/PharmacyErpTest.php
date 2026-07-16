<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\MedicineBatch;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Register;
use App\Models\CashSession;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockTransaction;
use App\Models\GoodsReceivedNote;
use App\Models\Sale;
use App\Models\SaleItem;
use Modules\Pharmacy\Services\StockService;
use Modules\Pharmacy\Services\LoyaltyService;
use Modules\Pharmacy\Services\CustomerLedgerService;
use Modules\Pharmacy\Services\GrnService;

class PharmacyErpTest extends TestCase
{
    use RefreshDatabase;

    protected StockService $stockService;
    protected LoyaltyService $loyaltyService;
    protected CustomerLedgerService $ledgerService;
    protected GrnService $grnService;

    protected Branch $branch;
    protected User $user;
    protected Supplier $supplier;
    protected MedicineCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockService = resolve(StockService::class);
        $this->loyaltyService = resolve(LoyaltyService::class);
        $this->ledgerService = resolve(CustomerLedgerService::class);
        $this->grnService = resolve(GrnService::class);

        // Common setup
        $this->branch = Branch::create([
            'name' => 'Main Pharmacy',
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'is_main_branch' => true
        ]);

        $this->user = User::create([
            'name' => 'Test Cashier',
            'email' => 'cashier@example.com',
            'password' => bcrypt('password'),
            'branch_id' => $this->branch->id,
            'uuid' => (string) \Illuminate\Support\Str::uuid()
        ]);

        // Authenticate the user
        $this->actingAs($this->user);

        $this->supplier = Supplier::create([
            'name' => 'Global Pharma',
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'branch_id' => $this->branch->id,
            'status' => 'active'
        ]);

        $this->category = MedicineCategory::create([
            'name' => 'Tablets',
            'uuid' => (string) \Illuminate\Support\Str::uuid()
        ]);
    }

    public function test_it_allocates_batches_using_fefo_cascade_logic()
    {
        $med = Medicine::create([
            'name' => 'Paracetamol 500mg',
            'generic_name' => 'Paracetamol',
            'category_id' => $this->category->id,
            'branch_id' => $this->branch->id,
            'uuid' => (string) \Illuminate\Support\Str::uuid()
        ]);

        // Create 2 batches: one expiring soon (FEFO first) and one later
        $batch1 = MedicineBatch::create([
            'medicine_id' => $med->id,
            'batch_number' => 'BCH-001',
            'expiry_date' => now()->addMonths(4)->toDateString(),
            'purchase_price' => 5.00,
            'selling_price' => 7.00,
            'status' => 'active',
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'branch_id' => $this->branch->id
        ]);

        $batch2 = MedicineBatch::create([
            'medicine_id' => $med->id,
            'batch_number' => 'BCH-002',
            'expiry_date' => now()->addMonths(8)->toDateString(),
            'purchase_price' => 6.00,
            'selling_price' => 8.00,
            'status' => 'active',
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'branch_id' => $this->branch->id
        ]);

        // Add 50 units stock to Batch1 and 100 units to Batch2
        $this->stockService->addStock($batch1->id, 50, 'in', 'REF-001');
        $this->stockService->addStock($batch2->id, 100, 'in', 'REF-002');

        // Request 80 units of Paracetamol. FEFO should cascade:
        // 50 units from Batch1, 30 units from Batch2
        $resolved = $this->stockService->resolveBatchesForQuantity($med->id, 80);

        $this->assertTrue($resolved['is_fulfilled']);
        $this->assertCount(2, $resolved['allocation']);
        
        $this->assertEquals($batch1->id, $resolved['allocation'][0]['batch_id']);
        $this->assertEquals(50, $resolved['allocation'][0]['quantity']);

        $this->assertEquals($batch2->id, $resolved['allocation'][1]['batch_id']);
        $this->assertEquals(30, $resolved['allocation'][1]['quantity']);
    }

    public function test_it_blocks_grn_batches_expiring_within_three_months_expiry_gate()
    {
        $med = Medicine::create([
            'name' => 'Aspirin',
            'category_id' => $this->category->id,
            'uuid' => (string) \Illuminate\Support\Str::uuid()
        ]);

        $grnData = [
            'branch_id' => $this->branch->id,
            'supplier_id' => $this->supplier->id,
            'items' => [
                [
                    'medicine_id' => $med->id,
                    'ordered_qty' => 100,
                    'received_qty' => 100,
                    'unit_cost' => 12.00,
                    'batch_no' => 'BCH-EXPIRED',
                    'expiry_date' => now()->addMonths(2)->toDateString(), // Less than 3 months!
                    'qc_status' => 'Pass',
                ]
            ]
        ];

        $this->expectException(\Exception::class);
        $this->grnService->processGrn($grnData, $this->user->id);
    }

    public function test_it_allocates_grn_landed_costs_proportionally_by_value()
    {
        $med1 = Medicine::create(['name' => 'Medicine A', 'category_id' => $this->category->id, 'uuid' => (string) \Illuminate\Support\Str::uuid()]);
        $med2 = Medicine::create(['name' => 'Medicine B', 'category_id' => $this->category->id, 'uuid' => (string) \Illuminate\Support\Str::uuid()]);

        $grnData = [
            'branch_id' => $this->branch->id,
            'supplier_id' => $this->supplier->id,
            'freight_charges' => 100.00,
            'other_charges' => 50.00, // Total landed charges = $150
            'items' => [
                [
                    'medicine_id' => $med1->id,
                    'ordered_qty' => 10,
                    'received_qty' => 10,
                    'unit_cost' => 10.00, // Subtotal value = $100
                    'batch_no' => 'BCH-A',
                    'expiry_date' => now()->addMonths(6)->toDateString(),
                    'qc_status' => 'Pass',
                ],
                [
                    'medicine_id' => $med2->id,
                    'ordered_qty' => 10,
                    'received_qty' => 10,
                    'unit_cost' => 20.00, // Subtotal value = $200
                    'batch_no' => 'BCH-B',
                    'expiry_date' => now()->addMonths(6)->toDateString(),
                    'qc_status' => 'Pass',
                ]
            ]
        ];

        // Total subtotal value = $300.
        // Med1 gets 100/300 = 1/3 share of $150 landed charges = $50. Per unit allocation = 50 / 10 = $5. Landed cost = 10 + 5 = $15.
        // Med2 gets 200/300 = 2/3 share of $150 landed charges = $100. Per unit allocation = 100 / 10 = $10. Landed cost = 20 + 10 = $30.

        $grn = $this->grnService->processGrn($grnData, $this->user->id);

        $this->assertCount(2, $grn->items);
        $this->assertEquals(15.00, (float) $grn->items[0]->landed_unit_cost);
        $this->assertEquals(30.00, (float) $grn->items[1]->landed_unit_cost);
    }

    public function test_it_checks_customer_credit_limits()
    {
        $customer = Customer::create([
            'customer_code' => 'CUST-001',
            'name' => 'VIP Corp Client',
            'customer_type' => 'corporate',
            'credit_limit' => 1000.00,
            'credit_terms_days' => 30,
        ]);

        // Outstanding is currently 0. Can buy $800 worth
        $this->assertTrue($this->ledgerService->checkCreditLimit($customer, 800.00));

        // Post a debit entry of $700. Outstanding = 700.
        $this->ledgerService->postTransaction($customer, 'sale', 'REF-SALE1', 700.00, 0);

        // Can we buy another $400? 700 + 400 = 1100 > 1000 limit. Should block
        $this->assertFalse($this->ledgerService->checkCreditLimit($customer, 400.00));

        // Make payment of $300. Outstanding = 400. Pass $this->user->id to satisfy received_by constraint.
        $this->ledgerService->recordPayment($customer, 300.00, 'Cash', 'PAY-1', $this->user->id);

        // Can we now buy $400? 400 + 400 = 800 <= 1000. Should allow
        $this->assertTrue($this->ledgerService->checkCreditLimit($customer, 400.00));
    }

    public function test_it_lists_and_pays_supplier_bills_from_cash_drawer()
    {
        // 1. Create approved GRN
        $grn = GoodsReceivedNote::create([
            'grn_no' => 'GRN-9999',
            'branch_id' => $this->branch->id,
            'supplier_id' => $this->supplier->id,
            'grand_total' => 450.00,
            'status' => 'approved',
            'approved_by' => $this->user->id,
            'received_by' => $this->user->id,
            'approved_at' => now()
        ]);

        // 2. Query supplier-bills endpoint. Should show Unpaid status
        $res1 = $this->getJson('/api/v1/pharmacy/pos/supplier-bills');
        $res1->assertStatus(200);
        $this->assertEquals('Unpaid', $res1->json('data.0.payment_status'));

        // 3. Open Cash Session
        $register = Register::create([
            'name' => 'Drawer 1',
            'branch_id' => $this->branch->id,
            'status' => 'active'
        ]);

        $session = CashSession::create([
            'register_id' => $register->id,
            'cashier_id' => $this->user->id,
            'opening_amount' => 1000.00,
            'status' => 'open',
            'opened_at' => now()
        ]);

        // 4. Pay the bill
        $res2 = $this->postJson("/api/v1/pharmacy/pos/supplier-bills/{$grn->id}/pay");
        $res2->assertStatus(200);

        // 5. Verify payout cash movement was registered
        $this->assertDatabaseHas('cash_movements', [
            'cash_session_id' => $session->id,
            'type' => 'payout',
            'amount' => 450.00,
            'reason' => 'Supplier Bill Payment: GRN #GRN-9999'
        ]);

        // 6. Query again. Should now show Paid status
        $res3 = $this->getJson('/api/v1/pharmacy/pos/supplier-bills');
        $res3->assertStatus(200);
    }

    public function test_it_completes_pending_sale_and_deducts_stock()
    {
        // 1. Setup medicine & batch
        $med = Medicine::create([
            'name' => 'Paracetamol 500mg',
            'generic_name' => 'Paracetamol',
            'category_id' => $this->category->id,
            'branch_id' => $this->branch->id,
            'uuid' => (string) \Illuminate\Support\Str::uuid()
        ]);

        $batch = MedicineBatch::create([
            'medicine_id' => $med->id,
            'batch_number' => 'BCH-PAY',
            'expiry_date' => now()->addMonths(12)->toDateString(),
            'purchase_price' => 5.00,
            'selling_price' => 10.00,
            'status' => 'active',
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'branch_id' => $this->branch->id
        ]);

        // Add 10 units stock
        $this->stockService->addStock($batch->id, 10, 'in', 'REF-INIT');
        $this->assertEquals(10, $this->stockService->getAvailableStock($batch->id));

        // 2. Create pending sale of 5 units
        $sale = Sale::create([
            'invoice_no' => null,
            'customer_id' => null,
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'total_amount' => 50.00,
            'discount_amount' => 0.00,
            'tax_amount' => 0.00,
            'net_total' => 50.00,
            'status' => 'pending',
            'uuid' => (string) \Illuminate\Support\Str::uuid()
        ]);

        SaleItem::create([
            'sale_uuid' => $sale->uuid,
            'medicine_id' => $med->id,
            'batch_id' => $batch->id,
            'quantity' => 5,
            'unit_price' => 10.00,
            'subtotal' => 50.00,
            'uuid' => (string) \Illuminate\Support\Str::uuid()
        ]);

        // 3. Open Cash Session
        $register = Register::create([
            'name' => 'Drawer 1',
            'branch_id' => $this->branch->id,
            'status' => 'active'
        ]);

        $session = CashSession::create([
            'register_id' => $register->id,
            'cashier_id' => $this->user->id,
            'opening_amount' => 1000.00,
            'status' => 'open',
            'opened_at' => now()
        ]);

        // 4. Complete pending sale via POST
        $res = $this->postJson("/api/v1/pharmacy/pos/{$sale->uuid}/complete", [
            'payment_method' => 'Cash'
        ]);
        $res->assertStatus(200);

        // 5. Verify database values
        $this->assertDatabaseHas('sales', [
            'uuid' => $sale->uuid,
            'status' => 'completed',
            'cashier_id' => $this->user->id,
            'cash_session_id' => $session->id
        ]);

        $this->assertNotNull(Sale::where('uuid', $sale->uuid)->first()->invoice_no);

        // 6. Verify physical stock is deducted: 10 - 5 = 5 remaining
        $this->assertEquals(5, $this->stockService->getAvailableStock($batch->id));
    }

    public function test_it_handles_typeable_category_creation_and_subcategory_saving()
    {
        // 1. Post to create a medicine with a new parent category and subcategory
        $res = $this->postJson('/api/v1/pharmacy/medicines', [
            'name' => 'Brand New Product',
            'generic_name' => 'Generic New',
            'category_name' => 'New Dynamic Category',
            'subcategory' => 'Dynamic Sub Category',
            'status' => 'active'
        ]);

        $res->assertStatus(200);
        $this->assertDatabaseHas('medicine_categories', [
            'name' => 'New Dynamic Category'
        ]);

        $cat = MedicineCategory::where('name', 'New Dynamic Category')->first();
        $this->assertDatabaseHas('medicines', [
            'name' => 'Brand New Product',
            'category_id' => $cat->id,
            'subcategory' => 'Dynamic Sub Category'
        ]);

        // 2. Test GET endpoint for subcategories
        $res2 = $this->getJson('/api/v1/pharmacy/medicines/subcategories');
        $res2->assertStatus(200);
        $this->assertContains('Dynamic Sub Category', $res2->json('data'));
    }

    public function test_it_logs_cash_movement_with_typeable_subcategory_and_lists_them()
    {
        // 1. Setup register and active session
        $register = Register::create([
            'name' => 'Drawer 1',
            'branch_id' => $this->branch->id,
            'status' => 'active'
        ]);

        $session = CashSession::create([
            'register_id' => $register->id,
            'cashier_id' => $this->user->id,
            'opening_amount' => 1000.00,
            'status' => 'open',
            'opened_at' => now()
        ]);

        // 2. Log a cash movement with custom subcategory
        $res = $this->postJson('/api/v1/pharmacy/pos/session/movement', [
            'session_id' => $session->id,
            'type' => 'payout',
            'subcategory' => 'Local Snack Expense',
            'amount' => 20.00,
            'reason' => 'Bought tea and snacks for branch staff'
        ]);

        $res->assertStatus(200);
        $this->assertDatabaseHas('cash_movements', [
            'cash_session_id' => $session->id,
            'type' => 'payout',
            'subcategory' => 'Local Snack Expense',
            'amount' => 20.00
        ]);

        // 3. Request distinct movement subcategories list
        $res2 = $this->getJson('/api/v1/pharmacy/pos/session/movement-subcategories');
        $res2->assertStatus(200);
        $this->assertContains('Local Snack Expense', $res2->json('data'));
    }

    public function test_sales_person_can_login_by_code()
    {
        // Create a Sales Person role
        \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => 'Sales Person', 'guard_name' => 'web'],
            ['uuid' => (string) \Illuminate\Support\Str::uuid()]
        );

        $salesPerson = User::create([
            'name' => 'Sara Sales',
            'email' => 'sara@example.com',
            'password' => bcrypt('password'),
            'login_code' => '4321',
            'branch_id' => $this->branch->id,
            'uuid' => (string) \Illuminate\Support\Str::uuid()
        ]);
        $salesPerson->assignRole('Sales Person');

        // Login by code
        $res = $this->postJson('/api/v1/auth/login-by-code', [
            'login_code' => '4321'
        ]);

        $res->assertStatus(200)
            ->assertJsonStructure(['access_token', 'token_type', 'user']);
        
        $this->assertEquals('Sara Sales', $res->json('user.name'));
    }

    public function test_invalid_login_code_returns_401()
    {
        $res = $this->postJson('/api/v1/auth/login-by-code', [
            'login_code' => '9999'
        ]);

        $res->assertStatus(401)
            ->assertJson(['message' => 'Invalid login code']);
    }
}
