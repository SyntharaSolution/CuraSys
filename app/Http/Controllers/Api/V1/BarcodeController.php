<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BarcodeController extends Controller
{
    /**
     * Generate barcode for products or labels.
     */
    public function generate(Request $request): JsonResponse
    {
        $code = $request->query('code', '123456789');
        $type = $request->query('type', 'C128');
        
        // Use milon/barcode or simple-qrcode here to generate base64/SVG
        $barcodeData = ''; 

        return response()->json([
            'status' => 'success',
            'data' => [
                'code' => $code,
                'type' => $type,
                'image' => $barcodeData,
            ]
        ]);
    }
}
