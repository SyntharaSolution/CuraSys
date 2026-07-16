<?php

namespace Modules\Pharmacy\Services;

use App\Models\Customer;
use App\Models\CustomerLoyalty;
use App\Models\LoyaltyTransaction;
use App\Models\LoyaltyTier;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    /**
     * Award points to a customer based on net sale amount.
     */
    public function awardPoints(Customer $customer, float $netAmount, string $saleUuid): int
    {
        return DB::transaction(function () use ($customer, $netAmount, $saleUuid) {
            $loyalty = CustomerLoyalty::firstOrCreate(
                ['customer_id' => $customer->id],
                ['points_balance' => 0]
            );

            // Earn rate: 1 point per $1 spent
            $pointsMultiplier = 1.00;
            if ($loyalty->tier) {
                $pointsMultiplier = (float) $loyalty->tier->points_multiplier;
            }

            $pointsEarned = (int) floor($netAmount * $pointsMultiplier);

            if ($pointsEarned > 0) {
                // Log transaction
                LoyaltyTransaction::create([
                    'customer_id' => $customer->id,
                    'sale_uuid' => $saleUuid,
                    'type' => 'earn',
                    'points' => $pointsEarned,
                    'note' => "Points earned from purchase. Net Amount: \${$netAmount}",
                ]);

                // Update loyalty record
                $loyalty->increment('points_earned', $pointsEarned);
                $loyalty->increment('points_balance', $pointsEarned);

                // Update customer table cache
                $customer->increment('loyalty_points_balance', $pointsEarned);

                // Re-evaluate tier
                $this->evaluateLoyaltyTier($customer, $loyalty);
            }

            return $pointsEarned;
        });
    }

    /**
     * Redeem loyalty points at POS.
     * Returns the discount amount applied.
     */
    public function redeemPoints(Customer $customer, int $pointsToRedeem, float $billTotal, string $saleUuid): float
    {
        // 10 points = $1 discount
        $conversionRate = 0.10; 
        
        $loyalty = CustomerLoyalty::firstOrCreate(
            ['customer_id' => $customer->id],
            ['points_balance' => 0]
        );

        if ($loyalty->points_balance < $pointsToRedeem) {
            throw new \Exception("Insufficient points balance. Available: {$loyalty->points_balance}");
        }

        // Constraints
        $minRedemption = 50; // points
        if ($pointsToRedeem < $minRedemption) {
            throw new \Exception("Minimum redemption threshold is {$minRedemption} points.");
        }

        $discountAmount = $pointsToRedeem * $conversionRate;
        $maxDiscount = $billTotal * 0.50; // Max 50% of the bill

        if ($discountAmount > $maxDiscount) {
            $discountAmount = $maxDiscount;
            $pointsToRedeem = (int) ceil($discountAmount / $conversionRate);
        }

        DB::transaction(function () use ($customer, $loyalty, $pointsToRedeem, $discountAmount, $saleUuid) {
            LoyaltyTransaction::create([
                'customer_id' => $customer->id,
                'sale_uuid' => $saleUuid,
                'type' => 'redeem',
                'points' => $pointsToRedeem,
                'note' => "Points redeemed for \${$discountAmount} discount.",
            ]);

            $loyalty->increment('points_redeemed', $pointsToRedeem);
            $loyalty->decrement('points_balance', $pointsToRedeem);

            $customer->decrement('loyalty_points_balance', $pointsToRedeem);
        });

        return $discountAmount;
    }

    /**
     * Evaluate and update loyalty tier based on cumulative points earned.
     */
    protected function evaluateLoyaltyTier(Customer $customer, CustomerLoyalty $loyalty): void
    {
        $totalEarned = $loyalty->points_earned;

        $matchingTier = LoyaltyTier::where('min_spend_threshold', '<=', $totalEarned)
            ->orderBy('min_spend_threshold', 'desc')
            ->first();

        if ($matchingTier && $loyalty->tier_id !== $matchingTier->id) {
            $loyalty->update([
                'tier_id' => $matchingTier->id,
                'tier_effective_date' => now()->toDateString()
            ]);
        }
    }

    /**
     * Expire points for inactive accounts (run via Scheduler).
     * e.g. Expire points if no transaction in last 12 months.
     */
    public function expireInactivePoints(int $inactivityMonths = 12): int
    {
        $expiredCount = 0;
        $customers = Customer::where('loyalty_points_balance', '>', 0)->get();

        foreach ($customers as $customer) {
            // Find last earn/redeem transaction
            $lastTx = LoyaltyTransaction::where('customer_id', $customer->id)
                ->orderBy('created_at', 'desc')
                ->first();

            $lastActiveDate = $lastTx ? $lastTx->created_at : $customer->created_at;

            if ($lastActiveDate->diffInMonths(now()) >= $inactivityMonths) {
                $pointsToExpire = $customer->loyalty_points_balance;

                DB::transaction(function () use ($customer, $pointsToExpire) {
                    LoyaltyTransaction::create([
                        'customer_id' => $customer->id,
                        'type' => 'expire',
                        'points' => $pointsToExpire,
                        'note' => "Points expired due to {$customer->loyalty_points_balance} months of inactivity.",
                    ]);

                    $loyalty = CustomerLoyalty::firstOrCreate(['customer_id' => $customer->id]);
                    $loyalty->decrement('points_balance', $pointsToExpire);

                    $customer->update(['loyalty_points_balance' => 0]);
                });

                $expiredCount++;
            }
        }

        return $expiredCount;
    }
}
