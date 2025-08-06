<?php
/**
 * Script para corrigir referências específicas de assets
 * Corrige os caminhos após a primeira atualização
 */

echo "=== Corrigindo referências de assets ===\n\n";

// Função para processar arquivo com substituições mais precisas
function fixAssetReferences($filePath) {
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return false;
    }
    
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Correções específicas baseadas no contexto do arquivo
    $fileName = basename($filePath);
    $dirName = dirname($filePath);
    
    // Determinar nível de profundidade
    $levels = substr_count(str_replace(getcwd() . '/', '', $filePath), '/');
    $isInSubdir = strpos($filePath, '/administracao/') !== false || 
                  strpos($filePath, '/pagamento/') !== false || 
                  strpos($filePath, '/usuario/') !== false ||
                  strpos($filePath, '/ajax/') !== false ||
                  strpos($filePath, '/OBD/') !== false;
    
    // Correções para includes de configuração em subdiretórios
    if ($isInSubdir) {
        // Para arquivos em subdiretórios, usar ../../
        $content = str_replace(
            'include("../shared-modules/config/',
            'include("../../shared-modules/config/',
            $content
        );
        $content = str_replace(
            "include('../shared-modules/config/",
            "include('../../shared-modules/config/",
            $content
        );
        $content = str_replace(
            'require("../shared-modules/config/',
            'require("../../shared-modules/config/',
            $content
        );
        $content = str_replace(
            "require('../shared-modules/config/",
            "require('../../shared-modules/config/",
            $content
        );
    }
    
    // Correções para caminhos de imagens
    $content = preg_replace('/src=["\']\.\.\/imagens\//', 'src="../shared-modules/assets/images/', $content);
    $content = preg_replace('/href=["\']\.\.\/imagens\//', 'href="../shared-modules/assets/images/', $content);
    
    // Para subdiretórios, usar ../../
    if ($isInSubdir) {
        $content = preg_replace('/src=["\']\.\.\/\.\.\/imagens\//', 'src="../../shared-modules/assets/images/', $content);
        $content = preg_replace('/href=["\']\.\.\/\.\.\/imagens\//', 'href="../../shared-modules/assets/images/', $content);
    }
    
    // Correções para JavaScript
    $content = preg_replace('/src=["\']\.\.\/javascript\//', 'src="../shared-modules/assets/js/', $content);
    if ($isInSubdir) {
        $content = preg_replace('/src=["\']\.\.\/\.\.\/javascript\//', 'src="../../shared-modules/assets/js/', $content);
    }
    
    // Correções para CSS
    $content = preg_replace('/href=["\']\.\.\/css\//', 'href="../shared-modules/assets/css/', $content);
    if ($isInSubdir) {
        $content = preg_replace('/href=["\']\.\.\/\.\.\/css\//', 'href="../../shared-modules/assets/css/', $content);
    }
    
    // Correções para background-image no CSS inline
    $content = preg_replace('/background-image:url\(["\']?\.\.\/imagens\//', 'background-image:url("../shared-modules/assets/images/', $content);
    if ($isInSubdir) {
        $content = preg_replace('/background-image:url\(["\']?\.\.\/\.\.\/imagens\//', 'background-image:url("../../shared-modules/assets/images/', $content);
    }
    
    // Se houve mudanças, salvar
    if ($content !== $originalContent) {
        if (file_put_contents($filePath, $content)) {
            echo "✓ Corrigido: $filePath\n";
            return true;
        } else {
            echo "✗ Erro ao salvar: $filePath\n";
            return false;
        }
    }
    
    return false;
}

// Função para processar diretório
function fixDirectory($directory) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    $fixedFiles = 0;
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
            
            if (in_array(strtolower($extension), ['php', 'html'])) {
                if (fixAssetReferences($file->getRealPath())) {
                    $fixedFiles++;
                }
            }
        }
    }
    
    return $fixedFiles;
}

// Processar sistema legacy
echo "Corrigindo referências no sistema legacy...\n";
$fixedFiles = fixDirectory('legacy-fastrackgps');
echo "Arquivos corrigidos: $fixedFiles\n\n";

echo "=== Correção concluída ===\n";
?>