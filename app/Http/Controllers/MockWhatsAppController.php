<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MockWhatsAppController extends Controller
{
    /**
     * Health check endpoint
     */
    public function health()
    {
        return response()->json([
            'status' => 'OK',
            'timestamp' => now()->toISOString(),
            'message' => 'Mock WhatsApp server running (Laravel)',
            'activeClients' => 0,
            'activeQrCodes' => 0
        ]);
    }

    /**
     * Connect device endpoint
     */
    public function connect(Request $request, $deviceKey)
    {
        Log::info("Connect request for device: {$deviceKey}");
        
        return response()->json([
            'success' => true,
            'message' => 'Connection initiated (mock mode)',
            'deviceKey' => $deviceKey
        ]);
    }

    /**
     * Disconnect device endpoint
     */
    public function disconnect(Request $request, $deviceKey)
    {
        Log::info("Disconnect request for device: {$deviceKey}");
        
        return response()->json([
            'success' => true,
            'message' => 'Device disconnected (mock mode)',
            'deviceKey' => $deviceKey
        ]);
    }

    /**
     * Get device status endpoint
     */
    public function status($deviceKey)
    {
        Log::info("Status request for device: {$deviceKey}");
        
        return response()->json([
            'success' => true,
            'deviceKey' => $deviceKey,
            'status' => 'disconnected',
            'message' => 'Mock mode - no real connection',
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Get QR code endpoint
     */
    public function qrCode($deviceKey)
    {
        Log::info("QR request for device: {$deviceKey}");
        
        // Generate dummy QR code
        $dummyQR = "dummy_qr_code_{$deviceKey}_" . time();
        
        return response()->json([
            'success' => true,
            'qr' => $dummyQR,
            'expires_at' => now()->addMinutes(5)->timestamp * 1000,
            'timestamp' => now()->timestamp * 1000
        ]);
    }
} 