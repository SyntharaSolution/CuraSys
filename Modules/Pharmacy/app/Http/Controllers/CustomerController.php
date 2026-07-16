<?php

namespace Modules\Pharmacy\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\LoyaltyTransaction;
use Modules\Pharmacy\Services\CustomerLedgerService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    protected CustomerLedgerService $ledgerService;

    public function __construct(CustomerLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * List customers with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Customer::with(['loyalty.tier', 'patient']);

        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('NIC_Passport', 'like', "%{$search}%");
            });
        }

        if ($type = $request->get('customer_type')) {
            $query->where('customer_type', $type);
        }

        return response()->json(['data' => $query->orderBy('created_at', 'desc')->get()]);
    }

    /**
     * Create a new customer.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'NIC_Passport' => 'nullable|string|max:50',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'customer_type' => 'required|in:retail,wholesale,corporate,insurance_linked',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_terms_days' => 'nullable|integer|min:0',
            'patient_id' => 'nullable|exists:patients,id',
        ]);

        // Duplicate phone/NIC check
        if (!empty($request->phone)) {
            $duplicatePhone = Customer::where('phone', $request->phone)->first();
            if ($duplicatePhone) {
                return response()->json(['message' => 'A customer with this phone number already exists.', 'duplicate' => $duplicatePhone], 422);
            }
        }

        if (!empty($request->NIC_Passport)) {
            $duplicateNic = Customer::where('NIC_Passport', $request->NIC_Passport)->first();
            if ($duplicateNic) {
                return response()->json(['message' => 'A customer with this NIC/Passport already exists.', 'duplicate' => $duplicateNic], 422);
            }
        }

        $data = $request->all();
        $data['customer_code'] = 'CUST-' . strtoupper(Str::random(6));
        $data['created_by'] = auth()->id() ?? 1;

        $customer = Customer::create($data);

        return response()->json(['message' => 'Customer created successfully', 'data' => $customer], 201);
    }

    /**
     * Update customer.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'NIC_Passport' => 'nullable|string|max:50',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'customer_type' => 'required|in:retail,wholesale,corporate,insurance_linked',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_terms_days' => 'nullable|integer|min:0',
        ]);

        $customer->update($request->all());

        return response()->json(['message' => 'Customer details updated successfully', 'data' => $customer]);
    }

    /**
     * Get Customer Credit Ledger history.
     */
    public function getLedger(Request $request, $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $ledger = CustomerLedger::where('customer_id', $customer->id)
            ->orderBy('transaction_date', 'desc')
            ->get();

        $outstanding = $this->ledgerService->getOutstandingBalance($customer->id);

        return response()->json([
            'customer' => $customer,
            'outstanding_balance' => $outstanding,
            'ledger' => $ledger
        ]);
    }

    /**
     * Get Customer Statement Data.
     */
    public function getStatement(Request $request, $id): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $statement = $this->ledgerService->getStatement($id, $request->start_date, $request->end_date);
        return response()->json(['data' => $statement]);
    }

    /**
     * Receive Credit Payment post to ledger.
     */
    public function receiveCreditPayment(Request $request, $id): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string',
            'reference_no' => 'nullable|string',
        ]);

        $customer = Customer::findOrFail($id);
        $payment = $this->ledgerService->recordPayment(
            $customer,
            (float) $request->amount,
            $request->method,
            $request->reference_no,
            auth()->id() ?? 1
        );

        return response()->json(['message' => 'Payment received and posted to ledger.', 'payment' => $payment]);
    }

    /**
     * Get loyalty points statement and logs.
     */
    public function getLoyalty(Request $request, $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $transactions = LoyaltyTransaction::where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'points_balance' => $customer->loyalty_points_balance,
            'transactions' => $transactions
        ]);
    }

    /**
     * Merge duplicate customer entries.
     */
    public function mergeCustomers(Request $request): JsonResponse
    {
        $request->validate([
            'primary_customer_id' => 'required|exists:customers,id',
            'duplicate_customer_id' => 'required|exists:customers,id|different:primary_customer_id',
        ]);

        DB::transaction(function () use ($request) {
            $primary = Customer::findOrFail($request->primary_customer_id);
            $duplicate = Customer::findOrFail($request->duplicate_customer_id);

            // Re-point transactions
            CustomerLedger::where('customer_id', $duplicate->id)->update(['customer_id' => $primary->id]);
            LoyaltyTransaction::where('customer_id', $duplicate->id)->update(['customer_id' => $primary->id]);

            // Combine loyalty points balance
            $primary->increment('loyalty_points_balance', $duplicate->loyalty_points_balance);

            // Delete duplicate customer
            $duplicate->delete();
        });

        return response()->json(['message' => 'Customers merged successfully.']);
    }
}
