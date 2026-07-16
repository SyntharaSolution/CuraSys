<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SyncController extends Controller
{
    /**
     * Pull updates from the central server.
     * Called by the offline POS clients.
     */
    public function pull(Request $request): JsonResponse
    {
        // Example: $since = $request->query('since');
        // Retrieve master data updated after $since
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'medicines' => [],
                'branches' => [],
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Push local offline transactions to the central server.
     */
    public function push(Request $request): JsonResponse
    {
        // Validate request payload
        // Process offline transactions (Sales, Patients)
        // Adjust stock via stock_transactions ledger
        
        return response()->json([
            'status' => 'success',
            'message' => 'Sync processed successfully',
            'synced_records' => 0,
        ]);
    }
}
