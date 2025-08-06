<?php

declare(strict_types=1);

namespace FastrackGps\Core\Http;

final class JsonResponse
{
    public static function success(array $data = [], string $message = 'Success', int $status = 200): Response
    {
        return Response::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function error(string $message, array $errors = [], int $status = 400): Response
    {
        return Response::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    public static function notFound(string $message = 'Resource not found'): Response
    {
        return self::error($message, [], 404);
    }

    public static function unauthorized(string $message = 'Unauthorized'): Response
    {
        return self::error($message, [], 401);
    }

    public static function forbidden(string $message = 'Forbidden'): Response
    {
        return self::error($message, [], 403);
    }
}