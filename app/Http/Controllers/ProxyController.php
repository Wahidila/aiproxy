<?php

namespace App\Http\Controllers;

use App\Services\EnowxAiService;
use App\Services\TokenTrackingService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProxyController extends Controller
{
    public function __construct(
        private EnowxAiService $enowxAiService,
        private TokenTrackingService $trackingService
    ) {
    }

    public function chatCompletions(Request $request)
    {
        return $this->proxyRequest($request, '/chat/completions');
    }

    public function messages(Request $request)
    {
        return $this->proxyRequest($request, '/messages');
    }

    public function responses(Request $request)
    {
        return $this->proxyRequest($request, '/responses');
    }

    public function models(Request $request)
    {
        $models = $this->enowxAiService->getModels();
        return response()->json($models);
    }

    public function health()
    {
        return response()->json(['status' => 'ok', 'timestamp' => now()->toIso8601String()]);
    }

    private function proxyRequest(Request $request, string $path)
    {
        $user = $request->get('_user');
        $apiKey = $request->get('_api_key');
        $body = $request->all();
        $isStreaming = $body['stream'] ?? false;

        if ($isStreaming) {
            $trackingService = $this->trackingService;
            $model = $body['model'] ?? 'unknown';

            // Pass a callback that tracks usage after stream completes
            $onComplete = function (array $usage) use ($user, $apiKey, $model, $path, $trackingService) {
                $trackingService->recordUsage(
                    $user,
                    $apiKey,
                    $model,
                    $usage['input_tokens'],
                    $usage['output_tokens'],
                    $path,
                    $usage['status_code'],
                    $usage['response_time_ms']
                );
            };

            return $this->enowxAiService->forward($request, $path, $onComplete);
        }

        // Non-streaming
        $result = $this->enowxAiService->forward($request, $path);

        // Track usage
        $this->trackingService->recordUsage(
            $user,
            $apiKey,
            $body['model'] ?? 'unknown',
            $result['input_tokens'],
            $result['output_tokens'],
            $path,
            $result['status'],
            $result['response_time_ms']
        );

        return response()->json($result['data'], $result['status']);
    }


}
