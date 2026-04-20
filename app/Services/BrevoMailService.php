<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class BrevoMailService
{
    private string $apiKey;
    private string $senderEmail;
    private string $senderName;

    public function __construct()
    {
        $this->apiKey = config('services.brevo.api_key', '');
        $this->senderEmail = config('services.brevo.sender_email', 'no-reply@aimurah.my.id');
        $this->senderName = config('services.brevo.sender_name', 'AIMurah');
    }

    /**
     * Send an email via Brevo Transactional Email API.
     *
     * @param string $toEmail Recipient email
     * @param string $toName Recipient name
     * @param string $subject Email subject
     * @param string $htmlContent HTML body content
     * @return array{success: bool, message: string}
     */
    public function send(string $toEmail, string $toName, string $subject, string $htmlContent): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'BREVO_API_KEY belum dikonfigurasi di .env',
            ];
        }

        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name' => $this->senderName,
                    'email' => $this->senderEmail,
                ],
                'to' => [
                    [
                        'email' => $toEmail,
                        'name' => $toName,
                    ],
                ],
                'subject' => $subject,
                'htmlContent' => $htmlContent,
            ]);

            if ($response->successful()) {
                Log::info("Brevo email sent to {$toEmail}", [
                    'messageId' => $response->json('messageId'),
                ]);

                return [
                    'success' => true,
                    'message' => 'Email berhasil dikirim.',
                ];
            }

            $error = $response->json('message', $response->body());
            Log::error("Brevo API error: {$error}", [
                'status' => $response->status(),
                'to' => $toEmail,
            ]);

            return [
                'success' => false,
                'message' => "Brevo API error: {$error}",
            ];
        } catch (\Exception $e) {
            Log::error("Brevo mail exception: {$e->getMessage()}", [
                'to' => $toEmail,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send an email using a Blade view as the HTML content.
     *
     * @param string $toEmail Recipient email
     * @param string $toName Recipient name
     * @param string $subject Email subject
     * @param string $view Blade view name (e.g. 'emails.invitation')
     * @param array $data Data to pass to the view
     * @return array{success: bool, message: string}
     */
    public function sendView(string $toEmail, string $toName, string $subject, string $view, array $data = []): array
    {
        $htmlContent = View::make($view, $data)->render();

        return $this->send($toEmail, $toName, $subject, $htmlContent);
    }
}
