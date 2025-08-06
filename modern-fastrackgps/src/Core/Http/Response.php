<?php

declare(strict_types=1);

namespace FastrackGps\Core\Http;

use FastrackGps\Core\View\TwigRenderer;

final class Response
{
    private static ?TwigRenderer $renderer = null;

    public function __construct(
        private readonly string $content = '',
        private readonly int $statusCode = 200,
        private readonly array $headers = []
    ) {
    }

    public static function setRenderer(TwigRenderer $renderer): void
    {
        self::$renderer = $renderer;
    }

    public static function make(string $content = '', int $status = 200, array $headers = []): self
    {
        return new self($content, $status, $headers);
    }

    public static function json(array $data, int $status = 200): self
    {
        return new self(
            json_encode($data, JSON_THROW_ON_ERROR),
            $status,
            ['Content-Type' => 'application/json']
        );
    }

    public static function redirect(string $url, int $status = 302): self
    {
        return new self('', $status, ['Location' => $url]);
    }

    public static function view(string $template, array $data = []): self
    {
        if (self::$renderer === null) {
            // Fallback template rendering
            $content = self::renderTemplate($template, $data);
            return new self($content);
        }

        try {
            $content = self::$renderer->render($template, $data);
            return new self($content);
        } catch (\Exception $e) {
            // Log error and return error page
            error_log("Template rendering error: " . $e->getMessage());
            try {
                $errorContent = self::$renderer->render('error/500', ['error' => $e->getMessage()]);
                return new self($errorContent, 500);
            } catch (\Exception $fallbackError) {
                return new self("Template error: " . $e->getMessage(), 500);
            }
        }
    }

    public function send(): void
    {
        // Send status code
        http_response_code($this->statusCode);

        // Send headers
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        // Send content
        echo $this->content;
    }

    private static function renderTemplate(string $template, array $data): string
    {
        // Fallback template rendering
        $templatePath = __DIR__ . "/../../../templates/{$template}.html.twig";
        
        if (!file_exists($templatePath)) {
            return "Template not found: {$template}";
        }

        // Simple template data extraction for fallback
        ob_start();
        echo "<!-- Fallback template rendering -->\n";
        echo "<h1>Template: {$template}</h1>\n";
        echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
        return ob_get_clean();
    }
}