<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            // Also check x-api-key header (Anthropic format)
            $token = $request->header('x-api-key');
        }

        if (!$token) {
            return response()->json([
                'error' => [
                    'message' => 'Missing API key. Include it in Authorization: Bearer sk-xxx header.',
                    'type' => 'authentication_error',
                    'code' => 'missing_api_key',
                ]
            ], 401);
        }

        $apiKey = ApiKey::where('key', $token)->where('is_active', true)->first();

        if (!$apiKey) {
            return response()->json([
                'error' => [
                    'message' => 'Invalid or inactive API key.',
                    'type' => 'authentication_error',
                    'code' => 'invalid_api_key',
                ]
            ], 401);
        }

        $user = $apiKey->user;

        // Check if user is banned
        if ($user->isBanned()) {
            return response()->json([
                'error' => [
                    'message' => 'Your account has been suspended. Contact admin for more information.',
                    'type' => 'account_suspended',
                    'code' => 'account_banned',
                ]
            ], 403);
        }

        // Attach user and api key to request
        $request->merge([
            '_api_key' => $apiKey,
            '_user' => $user,
        ]);

        return $next($request);
    }
}
