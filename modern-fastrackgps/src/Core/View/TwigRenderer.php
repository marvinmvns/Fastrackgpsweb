<?php

declare(strict_types=1);

namespace FastrackGps\Core\View;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;

final class TwigRenderer
{
    private Environment $twig;

    public function __construct(string $templatePath, bool $debug = false)
    {
        $loader = new FilesystemLoader($templatePath);
        
        $this->twig = new Environment($loader, [
            'cache' => $debug ? false : sys_get_temp_dir() . '/twig_cache',
            'debug' => $debug,
            'strict_variables' => true,
            'autoescape' => 'html'
        ]);

        if ($debug) {
            $this->twig->addExtension(new DebugExtension());
        }

        $this->addGlobalFunctions();
    }

    public function render(string $template, array $variables = []): string
    {
        return $this->twig->render($template, $variables);
    }

    private function addGlobalFunctions(): void
    {
        // Função para formatar datas
        $this->twig->addFunction(new \Twig\TwigFunction('format_date', function ($date, $format = 'd/m/Y H:i') {
            if ($date instanceof \DateTimeInterface) {
                return $date->format($format);
            }
            return $date;
        }));

        // Função para formatar valores monetários
        $this->twig->addFunction(new \Twig\TwigFunction('format_money', function ($value) {
            return 'R$ ' . number_format((float) $value, 2, ',', '.');
        }));

        // Função para formatar coordenadas
        $this->twig->addFunction(new \Twig\TwigFunction('format_coordinates', function ($lat, $lng) {
            return sprintf('%.6f, %.6f', $lat, $lng);
        }));

        // Função para gerar URLs
        $this->twig->addFunction(new \Twig\TwigFunction('url', function ($path) {
            return '/' . ltrim($path, '/');
        }));

        // Função para assets
        $this->twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
            return '/assets/' . ltrim($path, '/');
        }));

        // Função para verificar se usuário tem permissão
        $this->twig->addFunction(new \Twig\TwigFunction('can', function ($permission, $user = null) {
            // Implementar lógica de permissões
            return true; // Placeholder
        }));

        // Variáveis globais
        $this->twig->addGlobal('app_name', 'FastrackGPS');
        $this->twig->addGlobal('app_version', '2.0.0');
    }
}