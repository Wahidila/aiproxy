<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PakasirService
{
    protected string $slug;
    protected string $apiKey;
    protected string $baseUrl;
    protected bool $sandbox;

    public function __construct()
    {
        $this->slug = config('services.pakasir.slug', 'aimurah');
        $this->apiKey = config('services.pakasir.api_key', '');
        $this->baseUrl = rtrim(config('services.pakasir.base_url', 'https://app.pakasir.com'), '/');
        $this->sandbox = (bool) config('services.pakasir.sandbox', true);
    }

    /**
     * Generate a unique order ID for a donation.
     */
    public function generateOrderId(int $donationId): string
    {
        return 'AIM-' . $donationId . '-' . time();
    }

    /**
     * Build the Pakasir payment redirect URL.
     */
    public function createPaymentUrl(int $amount, string $orderId, string $redirectUrl): string
    {
        $params = http_build_query([
            'order_id' => $orderId,
            'redirect' => $redirectUrl,
            'qris_only' => 1,
        ]);

        return $this->baseUrl . '/pay/' . $this->slug . '/' . $amount . '?' . $params;
    }

    /**
     * Verify a transaction via the Pakasir API.
     */
    public function verifyTransaction(int $amount, string $orderId): array
    {
        $url = $this->baseUrl . '/api/transactiondetail';

        try {
            $response = Http::timeout(15)->get($url, [
                'project' => $this->slug,
                'amount' => $amount,
                'order_id' => $orderId,
                'api_key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            Log::warning('Pakasir verifyTransaction failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'order_id' => $orderId,
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Pakasir verifyTransaction exception', [
                'message' => $e->getMessage(),
                'order_id' => $orderId,
            ]);

            return [];
        }
    }
}
