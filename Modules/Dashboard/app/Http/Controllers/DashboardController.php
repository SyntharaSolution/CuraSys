<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Medicine;
use Modules\Pharmacy\Services\StockService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Get high level statistics.
     */
    public function stats(Request $request)
    {
        $user = $request->user() ?? \App\Models\User::first();
        
        // Check if cashier
        $isCashier = $user && $user->roles && $user->roles->contains(fn($r) => $r->name === 'Cashier');

        if ($isCashier) {
            $today = Carbon::today();

            // 1. Cash In (Sales + Float Adds today)
            $cashIn = \App\Models\CashMovement::whereHas('cashSession', fn($q) => $q->where('cashier_id', $user->id))
                ->whereDate('created_at', $today)
                ->whereIn('type', ['sale', 'float_add'])
                ->sum('amount');

            // 2. Cash Out (Drops + Payouts + Refunds today)
            $cashOut = \App\Models\CashMovement::whereHas('cashSession', fn($q) => $q->where('cashier_id', $user->id))
                ->whereDate('created_at', $today)
                ->whereIn('type', ['drop', 'payout', 'refund'])
                ->sum('amount');

            // 3. Today's Paid Invoices Count
            $todaysBills = Sale::where('cashier_id', $user->id)
                ->whereDate('created_at', $today)
                ->where('status', 'completed')
                ->count();

            // 4. Returns refund sum today
            $returnsRefund = \App\Models\SaleReturn::where('processed_by', $user->id)
                ->whereDate('created_at', $today)
                ->sum('total_refund');

            return response()->json([
                'is_cashier' => true,
                'cash_in' => $cashIn,
                'cash_out' => $cashOut,
                'todays_bills' => $todaysBills,
                'returns_refund' => $returnsRefund
            ]);
        }

        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // 1. Today's Revenue
        $todaysRevenue = Sale::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('net_total');

        // 2. Monthly Revenue
        $monthlyRevenue = Sale::where('created_at', '>=', $startOfMonth)
            ->where('status', 'completed')
            ->sum('net_total');

        // 3. Count Low Stock Items
        $medicines = Medicine::with('batches')->get();
        $lowStockCount = 0;
        
        foreach ($medicines as $medicine) {
            $totalStock = 0;
            foreach ($medicine->batches as $batch) {
                $totalStock += $this->stockService->getAvailableStock($batch->id);
            }
            if ($totalStock <= $medicine->reorder_level) {
                $lowStockCount++;
            }
        }

        return response()->json([
            'is_cashier' => false,
            'todays_revenue' => $todaysRevenue,
            'monthly_revenue' => $monthlyRevenue,
            'low_stock_count' => $lowStockCount
        ]);
    }

    /**
     * Get low stock items details.
     */
    public function lowStock(Request $request)
    {
        $medicines = Medicine::with(['batches' => function($q) {
            $q->whereDate('expiry_date', '>', now());
        }])->get();
        
        $lowStockItems = [];

        foreach ($medicines as $medicine) {
            $totalStock = 0;
            foreach ($medicine->batches as $batch) {
                $totalStock += $this->stockService->getAvailableStock($batch->id);
            }
            
            if ($totalStock <= $medicine->reorder_level) {
                $lowStockItems[] = [
                    'uuid' => $medicine->uuid,
                    'name' => $medicine->name,
                    'sku' => $medicine->sku,
                    'available_stock' => $totalStock,
                    'reorder_level' => $medicine->reorder_level
                ];
            }
        }

        return response()->json(['data' => $lowStockItems]);
    }

    /**
     * Get recent sales.
     */
    public function recentSales(Request $request)
    {
        $sales = Sale::with('items.medicine')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Format for frontend
        $data = $sales->map(function ($sale) {
            return [
                'uuid' => $sale->uuid,
                'total_amount' => $sale->total_amount,
                'payment_method' => $sale->payment_method,
                'created_at' => $sale->created_at->diffForHumans(),
                'items_count' => $sale->items->count(),
                'top_item' => $sale->items->first()?->medicine?->name ?? 'Unknown'
            ];
        });

        return response()->json(['data' => $data]);
    }
}
