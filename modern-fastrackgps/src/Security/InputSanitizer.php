<?php

declare(strict_types=1);

namespace FastrackGps\Security;

final class InputSanitizer
{
    /**
     * Sanitize string input to prevent XSS attacks
     */
    public static function sanitizeString(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitize integer input
     */
    public static function sanitizeInt(mixed $input): ?int
    {
        $filtered = filter_var($input, FILTER_VALIDATE_INT);
        return $filtered !== false ? $filtered : null;
    }

    /**
     * Sanitize float input
     */
    public static function sanitizeFloat(mixed $input): ?float
    {
        $filtered = filter_var($input, FILTER_VALIDATE_FLOAT);
        return $filtered !== false ? $filtered : null;
    }

    /**
     * Sanitize email input
     */
    public static function sanitizeEmail(string $input): ?string
    {
        $sanitized = filter_var($input, FILTER_SANITIZE_EMAIL);
        $validated = filter_var($sanitized, FILTER_VALIDATE_EMAIL);
        return $validated !== false ? $validated : null;
    }

    /**
     * Sanitize URL input
     */
    public static function sanitizeUrl(string $input): ?string
    {
        $sanitized = filter_var($input, FILTER_SANITIZE_URL);
        $validated = filter_var($sanitized, FILTER_VALIDATE_URL);
        return $validated !== false ? $validated : null;
    }

    /**
     * Remove all HTML tags from input
     */
    public static function stripTags(string $input, string $allowedTags = ''): string
    {
        return strip_tags($input, $allowedTags);
    }

    /**
     * Sanitize SQL identifier (table names, column names)
     */
    public static function sanitizeSqlIdentifier(string $input): string
    {
        // Only allow alphanumeric characters and underscores
        return preg_replace('/[^a-zA-Z0-9_]/', '', $input);
    }
}