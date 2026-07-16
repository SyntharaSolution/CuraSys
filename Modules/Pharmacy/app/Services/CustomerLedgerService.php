<?php

namespace Modules\Pharmacy\Services;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\CustomerPayment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerLedgerService
{
    /**
     * Get the current outstanding balance for a customer.
     * Outstanding = Total Debits - Total Credits.
     */
    public function getOutstandingBalance(int $customerId): float
    {
        $totals = CustomerLedger::where('customer_id', $customerId)
            ->select(DB::raw('SUM(debit) as debits, SUM(credit) as credits'))
            ->first();

        return (float) (($totals->debits ?? 0) - ($totals->credits ?? 0));
    }

    /**
     * Check if a sale violates the customer's credit limit.
     */
    public function checkCreditLimit(Customer $customer, float $saleAmount): bool
    {
        if ($customer->credit_limit <= 0) {
            return false; // No credit terms
        }

        $outstanding = $this->getOutstandingBalance($customer->id);
        return ($outstanding + $saleAmount) <= $customer->credit_limit;
    }

    /**
     * Post a transaction to the customer ledger.
     */
    public function postTransaction(Customer $customer, string $type, string $referenceId, float $debit, float $credit, string $notes = ''): CustomerLedger
    {
        return DB::transaction(function () use ($customer, $type, $referenceId, $debit, $credit, $notes) {
            $currentOutstanding = $this->getOutstandingBalance($customer->id);
            $newBalance = $currentOutstanding + $debit - $credit;

            return CustomerLedger::create([
                'customer_id' => $customer->id,
                'transaction_type' => $type, // sale, payment, return, adjustment
                'reference_id' => $referenceId,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $newBalance,
                'transaction_date' => now(),
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Record a customer payment.
     */
    public function recordPayment(Customer $customer, float $amount, string $method, ?string $referenceNo = null, ?int $userId = null): CustomerPayment
    {
        return DB::transaction(function () use ($customer, $amount, $method, $referenceNo, $userId) {
            $payment = CustomerPayment::create([
                'customer_id' => $customer->id,
                'amount' => $amount,
                'method' => $method,
                'reference_no' => $referenceNo,
                'received_by' => $userId ?? auth()->id() ?? 1,
                'received_at' => now(),
            ]);

            // Post to ledger as credit
            $this->postTransaction(
                $customer,
                'payment',
                (string) $payment->id,
                0,
                $amount,
                "Received payment via {$method}. Ref: {$referenceNo}"
            );

            return $payment;
        });
    }

    /**
     * Calculate outstanding balance aging buckets.
     * Buckets: 0-30 days, 31-60 days, 61-90 days, 90+ days.
     */
    public function getAgingReport(int $customerId): array
    {
        // To compute aging, we look at unpaid debits (sales) and offset them against credits (payments).
        // A simple FIFO allocation of payments to outstanding sales:
        $debits = CustomerLedger::where('customer_id', $customerId)
            ->where('debit', '>', 0)
            ->orderBy('transaction_date', 'asc')
            ->get();

        $credits = CustomerLedger::where('customer_id', $customerId)
            ->where('credit', '>', 0)
            ->sum('credit');

        $remainingCredit = $credits;
        $buckets = [
            '0-30' => 0.0,
            '31-60' => 0.0,
            '61-90' => 0.0,
            '90+' => 0.0,
        ];

        foreach ($debits as $ledger) {
            $unpaidAmount = $ledger->debit;
            
            if ($remainingCredit > 0) {
                if ($remainingCredit >= $unpaidAmount) {
                    $remainingCredit -= $unpaidAmount;
                    continue; // fully paid
                } else {
                    $unpaidAmount -= $remainingCredit;
                    $remainingCredit = 0;
                }
            }

            // Calculate age of this unpaid debit
            $ageInDays = Carbon::parse($ledger->transaction_date)->diffInDays(now());

            if ($ageInDays <= 30) {
                $buckets['0-30'] += $unpaidAmount;
            } elseif ($ageInDays <= 60) {
                $buckets['31-60'] += $unpaidAmount;
            } elseif ($ageInDays <= 90) {
                $buckets['61-90'] += $unpaidAmount;
            } else {
                $buckets['90+'] += $unpaidAmount;
            }
        }

        return $buckets;
    }

    /**
     * Generate Statement Data.
     */
    public function getStatement(int $customerId, string $startDate, string $endDate): array
    {
        $customer = Customer::findOrFail($customerId);
        
        $openingBalance = CustomerLedger::where('customer_id', $customerId)
            ->whereDate('transaction_date', '<', $startDate)
            ->select(DB::raw('SUM(debit) - SUM(credit) as balance'))
            ->first()->balance ?? 0;

        $entries = CustomerLedger::where('customer_id', $customerId)
            ->whereBetween('transaction_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('transaction_date', 'asc')
            ->get();

        return [
            'customer' => $customer,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'opening_balance' => (float) $openingBalance,
            'entries' => $entries,
            'closing_balance' => $this->getOutstandingBalance($customerId),
            'aging' => $this->getAgingReport($customerId)
        ];
    }
}
