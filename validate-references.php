<?php
/**
 * Script para validar se todas as referências estão funcionando
 * Verifica se arquivos incluídos existem e são acessíveis
 */

echo "=== Validando referências atualizadas ===\n\n";

$errors = [];
$warnings = [];
$successCount = 0;

// Função para validar includes/requires em um arquivo
function validateIncludes($filePath) {
    global $errors, $warnings, $successCount;
    
    if (!file_exists($filePath)) {
        $errors[] = "Arquivo não encontrado: $filePath";
        return;
    }
    
    $content = file_get_contents($filePath);
    $relativePath = str_replace(getcwd() . '/', '', $filePath);
    
    // Encontrar todos os includes/requires
    preg_match_all('/(?:include|require)(?:_once)?\s*\(?["\']([^"\']+)["\']/', $content, $matches);
    
    if (!empty($matches[1])) {
        foreach ($matches[1] as $includePath) {
            // Resolver caminho relativo baseado no arquivo atual
            $fullIncludePath = dirname($filePath) . '/' . $includePath;
            $fullIncludePath = realpath($fullIncludePath);
            
            if ($fullIncludePath && file_exists($fullIncludePath)) {
                $successCount++;
                echo "✓ $relativePath -> $includePath\n";
            } else {
                $errors[] = "Include não encontrado em $relativePath: $includePath";
            }
        }
    }
}

// Função para validar assets (CSS, JS, imagens)
function validateAssets($filePath) {
    global $warnings;
    
    if (!file_exists($filePath)) {
        return;
    }
    
    $content = file_get_contents($filePath);
    $relativePath = str_replace(getcwd() . '/', '', $filePath);
    
    // Encontrar referências de assets
    preg_match_all('/(?:src|href)=["\']([^"\']+\.(?:css|js|png|jpg|gif|ico))["\']/', $content, $matches);
    
    if (!empty($matches[1])) {
        foreach ($matches[1] as $assetPath) {
            // Pular URLs absolutas
            if (strpos($assetPath, 'http') === 0) {
                continue;
            }
            
            // Resolver caminho relativo
            $fullAssetPath = dirname($filePath) . '/' . $assetPath;
            $fullAssetPath = realpath($fullAssetPath);
            
            if (!$fullAssetPath || !file_exists($fullAssetPath)) {
                $warnings[] = "Asset não encontrado em $relativePath: $assetPath";
            }
        }
    }
}

// Validar arquivos do sistema legacy
echo "Validando sistema legacy...\n";
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator('legacy-fastrackgps', RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->isFile() && in_array($file->getExtension(), ['php', 'html'])) {
        validateIncludes($file->getRealPath());
        validateAssets($file->getRealPath());
    }
}

echo "\n";

// Validar arquivos compartilhados
echo "Validando módulos compartilhados...\n";
$sharedFiles = [
    'shared-modules/config/database.php',
    'shared-modules/config/shared-config.php',
    'shared-modules/config/mysql-legacy.php',
    'shared-modules/config/google-maps.php',
    'shared-modules/config/seguranca.php'
];

foreach ($sharedFiles as $file) {
    if (file_exists($file)) {
        echo "✓ Arquivo compartilhado existe: $file\n";
        $successCount++;
    } else {
        $errors[] = "Arquivo compartilhado não encontrado: $file";
    }
}

// Validar symlinks
echo "\nValidando symlinks...\n";
$symlinks = [
    'legacy-fastrackgps/shared-assets' => '../shared-modules/assets',
    'legacy-fastrackgps/imagens' => '../../shared-modules/assets/images',
    'legacy-fastrackgps/javascript' => '../../shared-modules/assets/js',
    'legacy-fastrackgps/css' => '../../shared-modules/assets/css',
    'modern-fastrackgps/public/shared' => '../../shared-modules/assets',
];

foreach ($symlinks as $link => $target) {
    if (is_link($link)) {
        $actualTarget = readlink($link);
        if ($actualTarget === $target) {
            echo "✓ Symlink correto: $link -> $target\n";
            $successCount++;
        } else {
            $warnings[] = "Symlink incorreto: $link aponta para $actualTarget, deveria ser $target";
        }
    } else if (file_exists($link)) {
        $warnings[] = "Existe arquivo/diretório onde deveria ser symlink: $link";
    } else {
        $errors[] = "Symlink não encontrado: $link";
    }
}

// Relatório final
echo "\n=== Relatório de Validação ===\n";
echo "✅ Referências válidas: $successCount\n";
echo "❌ Erros: " . count($errors) . "\n";
echo "⚠️  Avisos: " . count($warnings) . "\n\n";

if (!empty($errors)) {
    echo "ERROS ENCONTRADOS:\n";
    foreach ($errors as $error) {
        echo "  ❌ $error\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "AVISOS:\n";
    foreach ($warnings as $warning) {
        echo "  ⚠️  $warning\n";
    }
    echo "\n";
}

if (empty($errors)) {
    echo "🎉 Todas as referências críticas estão funcionando!\n";
    if (!empty($warnings)) {
        echo "ℹ️  Há alguns avisos que podem ser ignorados ou corrigidos opcionalmente.\n";
    }
} else {
    echo "❌ Há erros que precisam ser corrigidos antes do sistema funcionar corretamente.\n";
}

echo "\n=== Validação concluída ===\n";
?>