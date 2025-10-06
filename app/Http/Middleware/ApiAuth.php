<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;

class ApiAuth
{
    /**
     * Simple API key auth via X-API-KEY header.
     */
    public function handle(Request $request, Closure $next)
    {
        $provided = $request->header('X-API-KEY');
        $expected = env('API_KEY', 'secret123');

        if (!$provided || $provided !== $expected) {
            return ApiResponse::error(401, 'Unauthorized', ['auth' => 'Invalid or missing API key']);
        }

        return $next($request);
    }
}
