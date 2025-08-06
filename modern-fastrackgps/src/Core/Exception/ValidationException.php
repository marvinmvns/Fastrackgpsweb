<?php

declare(strict_types=1);

namespace FastrackGps\Core\Exception;

final class ValidationException extends BaseException
{
    private array $errors;

    public function __construct(array $errors, string $message = 'Validation failed')
    {
        parent::__construct($message, 'VALIDATION_FAILED', 422);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public static function fieldRequired(string $field): self
    {
        return new self([$field => 'This field is required']);
    }

    public static function invalidFormat(string $field, string $format): self
    {
        return new self([$field => "Invalid format. Expected: {$format}"]);
    }
}