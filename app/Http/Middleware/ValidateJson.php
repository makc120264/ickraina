<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;

class ValidateJson
{
    /**
     * Ensures JSON content-type and valid JSON body for mutating requests.
     */
    public function handle(Request $request, Closure $next)
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)) {
            $contentType = $request->header('Content-Type');
            if (!$contentType || stripos($contentType, 'application/json') === false) {
                return ApiResponse::error(415, 'Unsupported Media Type', [
                    'content_type' => 'Content-Type must be application/json'
                ]);
            }

            // Laravel automatically parses JSON into $request->json()/all(), but ensure it's valid
            $raw = $request->getContent();
            if (strlen($raw) && json_decode($raw, true) === null && json_last_error() !== JSON_ERROR_NONE) {
                return ApiResponse::error(400, 'Invalid JSON', [
                    'body' => 'Request body contains invalid JSON'
                ]);
            }
        }

        return $next($request);
    }
}
