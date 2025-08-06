<?php
/**
 * Script para atualizar todas as referências de arquivos nos sistemas
 * Executa as substituições necessárias para usar shared-modules
 */

echo "=== Atualizando referências para shared-modules ===\n\n";

// Mapeamento de substituições para sistema legacy
$legacyReplacements = [
    // Includes/requires de configuração
    "include('seguranca.php')" => "include('../shared-modules/config/seguranca.php')",
    'include("seguranca.php")' => 'include("../shared-modules/config/seguranca.php")',
    "require('seguranca.php')" => "require('../shared-modules/config/seguranca.php')",
    'require("seguranca.php")' => 'require("../shared-modules/config/seguranca.php")',
    
    "include('mysql.php')" => "include('../shared-modules/config/mysql-legacy.php')",
    'include("mysql.php")' => 'include("../shared-modules/config/mysql-legacy.php")',
    "require('mysql.php')" => "require('../shared-modules/config/mysql-legacy.php')",
    'require("mysql.php")' => 'require("../shared-modules/config/mysql-legacy.php")',
    
    "include('config.php')" => "include('../shared-modules/config/google-maps.php')",
    'include("config.php")' => 'include("../shared-modules/config/google-maps.php")',
    "require('config.php')" => "require('../shared-modules/config/google-maps.php')",
    'require("config.php")' => 'require("../shared-modules/config/google-maps.php")',
    
    // Includes relativos para subdiretórios
    "include('../seguranca.php')" => "include('../../shared-modules/config/seguranca.php')",
    'include("../seguranca.php")' => 'include("../../shared-modules/config/seguranca.php")',
    "include('../mysql.php')" => "include('../../shared-modules/config/mysql-legacy.php')",
    'include("../mysql.php")' => 'include("../../shared-modules/config/mysql-legacy.php")',
    "include('../config.php')" => "include('../../shared-modules/config/google-maps.php')",
    'include("../config.php")' => 'include("../../shared-modules/config/google-maps.php")',
    
    // Caminhos de assets para CSS
    'href="../imagens/' => 'href="' . getSharedAssetUrl('images/') . '',
    "href='../imagens/" => "href='" . getSharedAssetUrl('images/') . "",
    'src="../imagens/' => 'src="' . getSharedAssetUrl('images/') . '',
    "src='../imagens/" => "src='" . getSharedAssetUrl('images/') . "",
    
    // Caminhos de JavaScript
    'src="../javascript/' => 'src="' . getSharedAssetUrl('js/') . '',
    "src='../javascript/" => "src='" . getSharedAssetUrl('js/') . "",
    'src="javascript/' => 'src="' . getSharedAssetUrl('js/') . '',
    "src='javascript/" => "src='" . getSharedAssetUrl('js/') . "",
    
    // CSS
    'href="../css/' => 'href="' . getSharedAssetUrl('css/') . '',
    "href='../css/" => "href='" . getSharedAssetUrl('css/') . "",
    'href="css/' => 'href="' . getSharedAssetUrl('css/') . '',
    "href='css/" => "href='" . getSharedAssetUrl('css/') . "",
];

// Função auxiliar para obter caminho de asset compartilhado
function getSharedAssetUrl($path) {
    return '../shared-modules/assets/' . $path;
}

// Função para processar arquivo
function processFile($filePath, $replacements) {
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return false;
    }
    
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    // Se houve mudanças, salvar arquivo
    if ($content !== $originalContent) {
        if (file_put_contents($filePath, $content)) {
            echo "✓ Atualizado: $filePath\n";
            return true;
        } else {
            echo "✗ Erro ao salvar: $filePath\n";
            return false;
        }
    }
    
    return false;
}

// Função para processar diretório recursivamente
function processDirectory($directory, $replacements, $extensions = ['php', 'html']) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    $processedFiles = 0;
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
            
            if (in_array(strtolower($extension), $extensions)) {
                if (processFile($file->getRealPath(), $replacements)) {
                    $processedFiles++;
                }
            }
        }
    }
    
    return $processedFiles;
}

// Processar sistema legacy
echo "Processando sistema legacy...\n";
$legacyFiles = processDirectory('legacy-fastrackgps', $legacyReplacements);
echo "Arquivos atualizados no sistema legacy: $legacyFiles\n\n";

// Atualizar arquivo legacy mysql.php para usar shared
$legacyMysqlPath = 'legacy-fastrackgps/mysql.php';
if (file_exists($legacyMysqlPath)) {
    $newContent = "<?php
// Redirecionamento para configuração compartilhada
require_once '../shared-modules/config/mysql-legacy.php';
?>";
    file_put_contents($legacyMysqlPath, $newContent);
    echo "✓ Atualizado arquivo mysql.php legacy para usar shared-modules\n";
}

// Atualizar arquivo legacy config.php
$legacyConfigPath = 'legacy-fastrackgps/config.php';
if (file_exists($legacyConfigPath)) {
    $newContent = "<?php
// Redirecionamento para configuração compartilhada
require_once '../shared-modules/config/google-maps.php';
?>";
    file_put_contents($legacyConfigPath, $newContent);
    echo "✓ Atualizado arquivo config.php legacy para usar shared-modules\n";
}

// Atualizar arquivo legacy seguranca.php
$legacySecurityPath = 'legacy-fastrackgps/seguranca.php';
if (file_exists($legacySecurityPath)) {
    $newContent = "<?php
// Redirecionamento para configuração compartilhada
require_once '../shared-modules/config/seguranca.php';
?>";
    file_put_contents($legacySecurityPath, $newContent);
    echo "✓ Atualizado arquivo seguranca.php legacy para usar shared-modules\n";
}

// Criar symlinks para assets em sistema legacy
echo "\nCriando symlinks para assets...\n";

$legacyAssetsDir = 'legacy-fastrackgps/shared-assets';
if (!file_exists($legacyAssetsDir)) {
    if (symlink('../shared-modules/assets', $legacyAssetsDir)) {
        echo "✓ Symlink criado: $legacyAssetsDir -> ../shared-modules/assets\n";
    } else {
        echo "✗ Erro ao criar symlink: $legacyAssetsDir\n";
    }
}

// Processar sistema moderno (menos mudanças necessárias)
echo "\nProcessando sistema moderno...\n";
$modernReplacements = [
    // Apenas algumas referências que podem precisar de ajuste
    '../config/' => '../shared-modules/config/',
    './config/' => '../shared-modules/config/',
];

$modernFiles = processDirectory('modern-fastrackgps', $modernReplacements);
echo "Arquivos atualizados no sistema moderno: $modernFiles\n\n";

echo "=== Atualização concluída ===\n";
echo "Total de arquivos processados: " . ($legacyFiles + $modernFiles) . "\n";
echo "\nVerifique os logs em shared-modules/logs/ para detalhes.\n";
?>