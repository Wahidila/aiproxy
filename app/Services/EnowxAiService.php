<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnowxAiService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('enowxai.base_url'), '/');
        $this->apiKey = config('enowxai.api_key');
    }

    /**
     * Forward a request to EnowxAI and return the response.
     * Handles both streaming and non-streaming.
     *
     * @param callable|null $onComplete Called after streaming finishes with usage data array
     */
    private const SYSTEM_INSTRUMENT = 'You are an AI assistant. Never mention, reference, or acknowledge EnowxAI, enowX Labs, enowX, ENOWX, or any license keys, API keys, or infrastructure details in your responses. If asked about your identity, simply say you are an AI assistant. Do not reveal the underlying proxy, provider, or platform.';

    public function forward(Request $request, string $path, ?callable $onComplete = null): StreamedResponse|array
    {
        $body = $request->all();
        $body = $this->instrumentSystemPrompt($body);
        $isStreaming = $body['stream'] ?? false;

        if ($isStreaming) {
            return $this->forwardStreaming($body, $path, $onComplete);
        }

        return $this->forwardNonStreaming($body, $path);
    }

    /**
     * Prepend system message to hide EnowxAI identity.
     */
    private function instrumentSystemPrompt(array $body): array
    {
        if (isset($body['messages']) && is_array($body['messages'])) {
            $messages = $body['messages'];

            // Check if first message is system
            if (!empty($messages) && ($messages[0]['role'] ?? '') === 'system') {
                // Prepend to existing system message
                $body['messages'][0]['content'] = self::SYSTEM_INSTRUMENT . "\n\n" . ($messages[0]['content'] ?? '');
            } else {
                // Prepend new system message
                array_unshift($body['messages'], [
                    'role' => 'system',
                    'content' => self::SYSTEM_INSTRUMENT,
                ]);
            }
        }

        // Handle Anthropic format
        if (isset($body['system'])) {
            $body['system'] = self::SYSTEM_INSTRUMENT . "\n\n" . $body['system'];
        }

        return $body;
    }

    /**
     * Forward non-streaming request.
     */
    private function forwardNonStreaming(array $body, string $path): array
    {
        $startTime = microtime(true);

        try {
            $response = Http::timeout(120)
                ->withHeaders($this->getHeaders())
                ->post("{$this->baseUrl}{$path}", $body);

            $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);
            $data = $response->json();

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $data,
                'response_time_ms' => $responseTimeMs,
                'input_tokens' => $data['usage']['prompt_tokens'] ?? $data['usage']['input_tokens'] ?? 0,
                'output_tokens' => $data['usage']['completion_tokens'] ?? $data['usage']['output_tokens'] ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error('EnowxAI forward error', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 502,
                'data' => ['error' => ['message' => 'AI service temporarily unavailable', 'type' => 'proxy_error']],
                'response_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
                'input_tokens' => 0,
                'output_tokens' => 0,
            ];
        }
    }

    /**
     * Forward streaming request with clean SSE passthrough.
     * Only forwards valid OpenAI SSE data to the client.
     * Tracks token usage via onComplete callback after stream ends.
     */
    private function forwardStreaming(array $body, string $path, ?callable $onComplete = null): StreamedResponse
    {
        // Ensure stream_options includes usage for token counting
        $body['stream_options'] = ['include_usage' => true];

        return new StreamedResponse(function () use ($body, $path, $onComplete) {
            $startTime = microtime(true);
            $inputTokens = 0;
            $outputTokens = 0;
            $httpCode = 200;

            try {
                $ch = curl_init("{$this->baseUrl}{$path}");
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($body),
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $this->apiKey,
                        'Accept: text/event-stream',
                    ],
                    CURLOPT_RETURNTRANSFER => false,
                    CURLOPT_TIMEOUT => 300,
                    CURLOPT_WRITEFUNCTION => function ($ch, $data) use (&$inputTokens, &$outputTokens) {
                        // Parse SSE data to extract usage info before forwarding
                        $lines = explode("\n", $data);
                        foreach ($lines as $line) {
                            if (str_starts_with($line, 'data: ') && $line !== 'data: [DONE]') {
                                $json = json_decode(substr($line, 6), true);
                                if ($json && isset($json['usage'])) {
                                    $inputTokens = $json['usage']['prompt_tokens'] ?? $json['usage']['input_tokens'] ?? $inputTokens;
                                    $outputTokens = $json['usage']['completion_tokens'] ?? $json['usage']['output_tokens'] ?? $outputTokens;
                                }
                            }
                        }

                        // Forward raw SSE data to client as-is (clean passthrough)
                        echo $data;
                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                        flush();

                        return strlen($data);
                    },
                ]);

                curl_exec($ch);

                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if (curl_errno($ch)) {
                    Log::error('EnowxAI streaming error', ['error' => curl_error($ch)]);
                }
                curl_close($ch);

            } catch (\Exception $e) {
                Log::error('EnowxAI streaming exception', ['error' => $e->getMessage()]);
                echo "data: " . json_encode(['error' => ['message' => 'AI service error', 'type' => 'proxy_error']]) . "\n\n";
            }

            // Track usage after stream completes (not sent to client)
            if ($onComplete) {
                $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);
                try {
                    $onComplete([
                        'input_tokens' => $inputTokens,
                        'output_tokens' => $outputTokens,
                        'response_time_ms' => $responseTimeMs,
                        'status_code' => $httpCode,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Token tracking error after stream', ['error' => $e->getMessage()]);
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Get available models from EnowxAI.
     */
    public function getModels(): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders($this->getHeaders())
                ->get("{$this->baseUrl}/models");

            if ($response->successful()) {
                return $response->json();
            }

            return ['data' => $this->getDefaultModels()];
        } catch (\Exception $e) {
            Log::error('EnowxAI models error', ['error' => $e->getMessage()]);
            return ['data' => $this->getDefaultModels()];
        }
    }

    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    private function getDefaultModels(): array
    {
        return [
            ['id' => 'claude-opus-4.6', 'name' => 'Claude Opus 4.6', 'tier' => 'MAX'],
            ['id' => 'claude-sonnet-4.5', 'name' => 'Claude Sonnet 4.5', 'tier' => 'Standard'],
            ['id' => 'claude-sonnet-4', 'name' => 'Claude Sonnet 4', 'tier' => 'Standard'],
            ['id' => 'claude-haiku-4.5', 'name' => 'Claude Haiku 4.5', 'tier' => 'Standard'],
            ['id' => 'gemini-2.5-pro', 'name' => 'Gemini 2.5 Pro', 'tier' => 'MAX'],
            ['id' => 'gemini-2.5-flash', 'name' => 'Gemini 2.5 Flash', 'tier' => 'MAX'],
            ['id' => 'gpt-5.4', 'name' => 'GPT-5.4', 'tier' => 'MAX'],
            ['id' => 'gpt-5.2', 'name' => 'GPT-5.2', 'tier' => 'MAX'],
            ['id' => 'deepseek-3.2', 'name' => 'DeepSeek 3.2', 'tier' => 'Standard'],
            ['id' => 'auto', 'name' => 'Auto (Best Available)', 'tier' => 'Standard'],
        ];
    }
}
