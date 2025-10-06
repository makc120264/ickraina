<?php

namespace App\Http\Responses;

class ApiResponse
{
    public static function success($statusCode, $payload = null, $message = '')
    {
        return response()->json([
            'statusCode' => (int) $statusCode,
            'payload' => $payload,
            'message' => $message,
        ], (int) $statusCode);
    }

    public static function error($statusCode, $message, $errors = null)
    {
        return response()->json([
            'statusCode' => (int) $statusCode,
            'payload' => ['errors' => $errors],
            'message' => $message,
        ], (int) $statusCode);
    }
}
