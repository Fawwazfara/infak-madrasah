<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Mengirim pesan WhatsApp menggunakan Fonnte API
     *
     * @param string $phone
     * @param string $message
     * @return bool
     */
    public static function sendMessage($phone, $message)
    {
        $token = config('services.fonnte.token');

        if (empty($token)) {
            Log::warning('Fonnte token is missing. WhatsApp message not sent.');
            return false;
        }

        if (empty($phone)) {
            return false;
        }

        // Format nomor HP (opsional, fonnte biasanya bisa handle format 08 atau 62)
        // Kita pastikan saja tidak ada spasi atau strip
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Fonnte API Endpoint
        $url = 'https://api.fonnte.com/send';

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post($url, [
                'target' => $phone,
                'message' => $message,
                'countryCode' => '62', // Default kode negara Indonesia
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp message sent to {$phone}", ['response' => $response->json()]);
                return true;
            } else {
                Log::error("Failed to send WhatsApp message to {$phone}", ['response' => $response->json()]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Exception when sending WhatsApp message to {$phone}: " . $e->getMessage());
            return false;
        }
    }
}
