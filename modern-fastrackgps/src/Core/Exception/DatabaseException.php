<?php

declare(strict_types=1);

namespace FastrackGps\Core\Exception;

final class DatabaseException extends BaseException
{
    public static function connectionFailed(string $details = ''): self
    {
        return new self(
            'Database connection failed: ' . $details,
            'DB_CONNECTION_FAILED',
            500
        );
    }

    public static function queryFailed(string $query, string $error): self
    {
        return new self(
            'Database query failed: ' . $error,
            'DB_QUERY_FAILED',
            500
        );
    }
}