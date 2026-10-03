<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WaController extends Controller
{
    /**
     * Kirim beberapa pesan dari outbox (antrean) dengan delay antar pesan.
     * Dipanggil berulang oleh frontend sampai remaining = 0.
     */
    public function process(Request $request)
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:3',
        ]);

        $result = WhatsAppService::process((int) $request->input('limit', 1));

        return response()->json($result);
    }

    public function status()
    {
        return response()->json([
            'pending' => WhatsAppService::pendingCount(),
            'retry_after' => WhatsAppService::retryAfter(),
            'delay' => (int) config('services.fonnte.delay', 60),
        ]);
    }
}
