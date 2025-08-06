<?php
#!/usr/bin/env php
<?php
/**
 * FastrackGPS Migration Manager
 * 
 * Script centralizado para gerenciar todo o processo de migração
 * do sistema legacy para o sistema moderno.
 * 
 * @package FastrackGPS
 * @subpackage Migration
 * @author FastrackGPS Team
 * @version 2.0.0
 */

declare(strict_types=1);

class MigrationManager
{
    private string $scriptsPath;
    private array $steps = [
        1 => ['name' => 'Análise dos Dados Legacy', 'script' => 'analyze-legacy-data.php', 'required' => true],
        2 => ['name' => 'Limpeza dos Dados Legacy', 'script' => 'clean-legacy-data.php', 'required' => true],
        3 => ['name' => 'Migração dos Dados', 'script' => 'migrate-data.php', 'required' => true],
        4 => ['name' => 'Validação da Migração', 'script' => 'validate-migration.php', 'required' => true],
    ];
    
    public function __construct()
    {
        $this->scriptsPath = __DIR__ . '/migration/';
    }
    
    public function run(): void
    {
        $this->showHeader();
        $this->checkRequirements();
        
        $action = $this->getAction();
        
        switch ($action) {
            case 'full':
                $this->runFullMigration();
                break;
            case 'step':
                $this->runSpecificStep();
                break;
            case 'status':
                $this->showStatus();
                break;
            case 'rollback':
                $this->runRollback();
                break;
            default:
                $this->showHelp();
        }
    }
    
    private function showHeader(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║                   FastrackGPS Migration Manager             ║\n";
        echo "║                        Versão 2.0.0                         ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }
    
    private function checkRequirements(): void
    {
        echo "🔍 Verificando pré-requisitos...\n\n";
        
        // Verificar PHP version
        if (version_compare(PHP_VERSION, '7.4.0') < 0) {
            $this->error("PHP 7.4+ é necessário. Versão atual: " . PHP_VERSION);
        }
        echo "   ✅ PHP " . PHP_VERSION . " (OK)\n";
        
        // Verificar extensões
        $required_extensions = ['pdo', 'pdo_mysql', 'json'];
        foreach ($required_extensions as $ext) {
            if (!extension_loaded($ext)) {
                $this->error("Extensão PHP '{$ext}' não encontrada");
            }
        }
        echo "   ✅ Extensões PHP (OK)\n";
        
        // Verificar arquivos de configuração
        $config_file = __DIR__ . '/../config/database.php';
        if (!file_exists($config_file)) {
            $this->error("Arquivo de configuração não encontrado: {$config_file}");
        }
        echo "   ✅ Configuração do banco (OK)\n";
        
        // Verificar conexão com banco
        try {
            $config = require $config_file;
            $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
            $pdo = new PDO($dsn, $config['username'], $config['password']);
            echo "   ✅ Conexão com banco (OK)\n";
        } catch (Exception $e) {
            $this->error("Erro de conexão com banco: " . $e->getMessage());
        }
        
        // Verificar scripts de migração
        foreach ($this->steps as $step) {
            $script_path = $this->scriptsPath . $step['script'];
            if (!file_exists($script_path)) {
                $this->error("Script não encontrado: {$script_path}");
            }
        }
        echo "   ✅ Scripts de migração (OK)\n\n";
    }
    
    private function getAction(): string
    {
        global $argv;
        
        if (count($argv) < 2) {
            return 'help';
        }
        
        return $argv[1];
    }
    
    private function runFullMigração(): void
    {
        echo "🚀 Iniciando migração completa...\n\n";
        
        if (!$this->confirmAction("Executar migração completa?")) {
            echo "Operação cancelada.\n";
            return;
        }
        
        $start_time = microtime(true);
        $success_count = 0;
        
        foreach ($this->steps as $step_num => $step) {
            echo "📍 Passo {$step_num}: {$step['name']}\n";
            echo str_repeat("-", 60) . "\n";
            
            $step_start = microtime(true);
            $result = $this->executeScript($step['script']);
            $step_time = round(microtime(true) - $step_start, 2);
            
            if ($result === 0) {
                echo "   ✅ Concluído em {$step_time}s\n\n";
                $success_count++;
            } else {
                echo "   ❌ Falhou em {$step_time}s\n\n";
                
                if ($step['required']) {
                    echo "🚨 Passo obrigatório falhou. Abortando migração.\n";
                    return;
                }
            }
        }
        
        $total_time = round(microtime(true) - $start_time, 2);
        
        echo str_repeat("=", 60) . "\n";
        if ($success_count === count($this->steps)) {
            echo "🎉 MIGRAÇÃO CONCLUÍDA COM SUCESSO!\n";
        } else {
            echo "⚠️  MIGRAÇÃO CONCLUÍDA COM PROBLEMAS\n";
        }
        echo "⏱️  Tempo total: {$total_time}s\n";
        echo "📊 Passos concluídos: {$success_count}/" . count($this->steps) . "\n";
        echo str_repeat("=", 60) . "\n\n";
        
        if ($success_count === count($this->steps)) {
            $this->showNextSteps();
        }
    }
    
    private function runSpecificStep(): void
    {
        global $argv;
        
        if (count($argv) < 3) {
            echo "❌ Número do passo não fornecido.\n";
            echo "Uso: php migration-manager.php step <numero>\n\n";
            $this->listSteps();
            return;
        }
        
        $step_num = (int)$argv[2];
        
        if (!isset($this->steps[$step_num])) {
            echo "❌ Passo inválido: {$step_num}\n\n";
            $this->listSteps();
            return;
        }
        
        $step = $this->steps[$step_num];
        $dry_run = in_array('--dry-run', $argv);
        
        echo "📍 Executando Passo {$step_num}: {$step['name']}\n";
        echo str_repeat("=", 60) . "\n\n";
        
        if ($dry_run) {
            echo "🔍 MODO DRY-RUN ATIVADO\n\n";
        }
        
        $start_time = microtime(true);
        $args = $dry_run ? ['--dry-run'] : [];
        $result = $this->executeScript($step['script'], $args);
        $execution_time = round(microtime(true) - $start_time, 2);
        
        echo "\n" . str_repeat("=", 60) . "\n";
        if ($result === 0) {
            echo "✅ Passo concluído com sucesso em {$execution_time}s\n";
        } else {
            echo "❌ Passo falhou em {$execution_time}s\n";
            exit(1);
        }
    }
    
    private function showStatus(): void
    {
        echo "📊 Status da Migração\n";
        echo str_repeat("=", 60) . "\n\n";
        
        $config = require __DIR__ . '/../config/database.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        
        // Verificar existência das tabelas modernas
        $modern_tables = [
            'modern_users' => '👥 Usuários',
            'modern_vehicles' => '🚗 Veículos', 
            'modern_positions' => '📍 Posições',
            'modern_geofences' => '🔒 Cercas',
            'modern_alerts' => '🚨 Alertas',
            'modern_commands' => '📡 Comandos'
        ];
        
        $tables_status = [];
        $total_modern_records = 0;
        
        foreach ($modern_tables as $table => $label) {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->fetch()) {
                $count_stmt = $pdo->query("SELECT COUNT(*) as count FROM {$table}");
                $count = $count_stmt->fetch()['count'];
                $total_modern_records += $count;
                
                echo "   ✅ {$label}: {$count} registros\n";
                $tables_status[$table] = $count;
            } else {
                echo "   ❌ {$label}: Tabela não existe\n";
                $tables_status[$table] = null;
            }
        }
        
        echo "\n";
        
        // Status de logs de migração
        $logs_dir = __DIR__ . '/../logs/';
        if (is_dir($logs_dir)) {
            $log_files = glob($logs_dir . 'migration-*.json');
            if (!empty($log_files)) {
                $latest_log = max($log_files);
                $log_data = json_decode(file_get_contents($latest_log), true);
                
                echo "📋 Último Log de Migração:\n";
                echo "   📅 Data: {$log_data['timestamp']}\n";
                echo "   📊 Registros migrados: {$log_data['total_migrated']}\n";
                
                if (isset($log_data['dry_run']) && $log_data['dry_run']) {
                    echo "   🔍 Modo: DRY-RUN (teste)\n";
                }
                echo "\n";
            }
        }
        
        // Recomendações
        echo "💡 Recomendações:\n";
        
        $missing_tables = array_filter($tables_status, function($count) { return $count === null; });
        if (!empty($missing_tables)) {
            echo "   🔧 Execute a migração completa: php migration-manager.php full\n";
        } elseif ($total_modern_records === 0) {
            echo "   📊 Tabelas criadas mas vazias - execute migração dos dados\n";
        } else {
            echo "   ✅ Sistema moderno operacional!\n";
            echo "   🎯 Execute validação: php migration-manager.php step 4\n";
        }
        
        echo "\n";
    }
    
    private function runRollback(): void
    {
        echo "🔄 ROLLBACK DA MIGRAÇÃO\n";
        echo str_repeat("=", 60) . "\n\n";
        
        echo "⚠️  ATENÇÃO: Esta operação irá:\n";
        echo "   • Remover todas as tabelas modernas\n";
        echo "   • Restaurar backup se disponível\n";
        echo "   • Reverter alterações no sistema\n\n";
        
        if (!$this->confirmAction("Confirma o rollback?", "ROLLBACK")) {
            echo "Operação cancelada.\n";
            return;
        }
        
        $config = require __DIR__ . '/../config/database.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        
        try {
            echo "🗑️  Removendo tabelas modernas...\n";
            
            $modern_tables = [
                'modern_audit_logs',
                'modern_reports', 
                'modern_commands',
                'modern_alerts',
                'modern_geofence_vehicles',
                'modern_geofences',
                'modern_positions',
                'modern_vehicles',
                'modern_users'
            ];
            
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            foreach ($modern_tables as $table) {
                $pdo->exec("DROP TABLE IF EXISTS {$table}");
                echo "   ✅ Tabela {$table} removida\n";
            }
            
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            // Procurar por backups
            $backup_dir = __DIR__ . '/../database/';
            $backup_files = glob($backup_dir . 'backup-before-migration-*.sql');
            
            if (!empty($backup_files)) {
                $latest_backup = max($backup_files);
                echo "\n💾 Backup encontrado: " . basename($latest_backup) . "\n";
                
                if ($this->confirmAction("Restaurar este backup?")) {
                    echo "🔄 Restaurando backup...\n";
                    
                    $command = sprintf(
                        'mysql -h%s -u%s -p%s %s < %s',
                        escapeshellarg($config['host']),
                        escapeshellarg($config['username']),
                        escapeshellarg($config['password']),
                        escapeshellarg($config['database']),
                        escapeshellarg($latest_backup)
                    );
                    
                    exec($command, $output, $return_code);
                    
                    if ($return_code === 0) {
                        echo "   ✅ Backup restaurado com sucesso\n";
                    } else {
                        echo "   ❌ Erro ao restaurar backup\n";
                    }
                }
            }
            
            echo "\n🎉 Rollback concluído!\n";
            echo "💡 Verifique o funcionamento do sistema legacy\n\n";
            
        } catch (Exception $e) {
            echo "❌ Erro durante rollback: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    private function executeScript(string $script, array $args = []): int
    {
        $script_path = $this->scriptsPath . $script;
        $command = "php {$script_path}";
        
        if (!empty($args)) {
            $command .= ' ' . implode(' ', array_map('escapeshellarg', $args));
        }
        
        passthru($command, $return_code);
        return $return_code;
    }
    
    private function confirmAction(string $message, string $confirm_text = "SIM"): bool
    {
        echo "❓ {$message} (Digite '{$confirm_text}' para confirmar): ";
        $handle = fopen("php://stdin", "r");
        $input = trim(fgets($handle));
        fclose($handle);
        
        return strtoupper($input) === strtoupper($confirm_text);
    }
    
    private function listSteps(): void
    {
        echo "📋 Passos Disponíveis:\n\n";
        
        foreach ($this->steps as $num => $step) {
            $required = $step['required'] ? '(obrigatório)' : '(opcional)';
            echo "   {$num}. {$step['name']} {$required}\n";
            echo "      Script: {$step['script']}\n\n";
        }
    }
    
    private function showNextSteps(): void
    {
        echo "🎯 PRÓXIMOS PASSOS:\n\n";
        echo "   1️⃣  Monitorar logs e performance:\n";
        echo "      tail -f shared-modules/logs/migration.log\n\n";
        
        echo "   2️⃣  Testar sistema moderno:\n";
        echo "      • Fazer login no sistema\n";
        echo "      • Verificar listagem de veículos\n";
        echo "      • Testar envio de comandos\n\n";
        
        echo "   3️⃣  Coletar feedback dos usuários:\n";
        echo "      • Treinar equipe no novo sistema\n";
        echo "      • Documentar problemas encontrados\n\n";
        
        echo "   4️⃣  Configurar monitoramento contínuo:\n";
        echo "      • Logs de aplicação\n";
        echo "      • Métricas de performance\n";
        echo "      • Alertas de sistema\n\n";
        
        echo "📚 Documentação completa: README.md e MIGRATION_GUIDE.md\n\n";
    }
    
    private function showHelp(): void
    {
        echo "📖 FastrackGPS Migration Manager - Ajuda\n\n";
        echo "COMANDOS DISPONÍVEIS:\n\n";
        
        echo "  🚀 php migration-manager.php full\n";
        echo "     Executa migração completa (todos os passos)\n\n";
        
        echo "  📍 php migration-manager.php step <numero> [--dry-run]\n";
        echo "     Executa um passo específico da migração\n";
        echo "     --dry-run: Executa em modo teste (sem alterações)\n\n";
        
        echo "  📊 php migration-manager.php status\n";
        echo "     Mostra status atual da migração\n\n";
        
        echo "  🔄 php migration-manager.php rollback\n";
        echo "     Desfaz a migração (CUIDADO!)\n\n";
        
        echo "  ❓ php migration-manager.php help\n";
        echo "     Mostra esta ajuda\n\n";
        
        echo "EXEMPLOS:\n\n";
        echo "  # Testar análise dos dados sem alterações\n";
        echo "  php migration-manager.php step 1 --dry-run\n\n";
        
        echo "  # Executar migração completa\n";
        echo "  php migration-manager.php full\n\n";
        
        echo "  # Verificar status após migração\n";
        echo "  php migration-manager.php status\n\n";
        
        $this->listSteps();
        
        echo "💡 DICAS:\n\n";
        echo "  • Sempre execute backup antes da migração\n";
        echo "  • Use --dry-run para testar os scripts\n";
        echo "  • Monitore logs durante o processo\n";
        echo "  • Teste o sistema após cada passo\n\n";
        
        echo "📞 SUPORTE:\n";
        echo "  📧 suporte@fastrackgps.com\n";
        echo "  📖 Documentação: MIGRATION_GUIDE.md\n\n";
    }
    
    private function error(string $message): void
    {
        echo "❌ ERRO: {$message}\n\n";
        exit(1);
    }
}

// Executar o script
if (php_sapi_name() === 'cli') {
    $manager = new MigrationManager();
    $manager->run();
} else {
    echo "Este script deve ser executado via linha de comando.\n";
    exit(1);
}