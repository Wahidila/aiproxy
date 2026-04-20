<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckLaravelFallback
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cache the setting for 60 seconds to avoid DB query per request
        $enabled = Cache::remember('laravel_fallback_enabled', 60, function () {
            return Setting::get('laravel_fallback_enabled', '0');
        });

        if ($enabled !== '1') {
            return response()->json([
                'error' => [
                    'message' => 'Laravel fallback API is disabled. Use Golang proxy at port 8080.',
                    'type' => 'service_unavailable',
                    'code' => 'laravel_fallback_disabled',
                ]
            ], 503);
        }

        return $next($request);
    }
}
