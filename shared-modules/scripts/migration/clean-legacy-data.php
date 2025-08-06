<?php
/**
 * Script de Limpeza de Dados Legacy
 * 
 * Este script limpa e corrige problemas identificados no sistema legacy
 * para preparar os dados para migração ao sistema moderno.
 * 
 * @package FastrackGPS
 * @subpackage Migration
 * @author FastrackGPS Team
 * @version 2.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class LegacyDataCleaner
{
    private PDO $connection;
    private array $cleaned = [];
    private bool $dryRun = false;
    
    public function __construct(bool $dryRun = false)
    {
        $this->dryRun = $dryRun;
        
        $config = require __DIR__ . '/../../config/database.php';
        
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
        $this->connection = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    
    public function clean(): void
    {
        if ($this->dryRun) {
            echo "🔍 MODO DRY-RUN ATIVADO - Nenhuma alteração será feita\n\n";
        } else {
            echo "🧹 Iniciando limpeza dos dados legacy...\n\n";
            
            // Criar backup antes de limpar
            $this->createBackup();
        }
        
        $this->cleanUsers();
        $this->cleanVehicles();
        $this->cleanPositions();
        $this->cleanOrphanRecords();
        $this->updateDataTypes();
        
        $this->generateSummary();
    }
    
    private function createBackup(): void
    {
        echo "💾 Criando backup antes da limpeza...\n";
        
        $config = require __DIR__ . '/../../config/database.php';
        $timestamp = date('Y-m-d-H-i-s');
        $backup_file = __DIR__ . "/../../database/backup-before-cleaning-{$timestamp}.sql";
        
        $command = sprintf(
            'mysqldump -h%s -u%s -p%s %s > %s',
            escapeshellarg($config['host']),
            escapeshellarg($config['username']),
            escapeshellarg($config['password']),
            escapeshellarg($config['database']),
            escapeshellarg($backup_file)
        );
        
        exec($command, $output, $return_code);
        
        if ($return_code === 0) {
            echo "   ✅ Backup criado: {$backup_file}\n\n";
        } else {
            throw new Exception("Falha ao criar backup. Abortando limpeza.");
        }
    }
    
    private function cleanUsers(): void
    {
        echo "👥 Limpando dados de usuários...\n";
        
        // Remover usuários com dados obrigatórios em branco
        $stmt = $this->connection->prepare("
            SELECT id, nome, email FROM usuarios 
            WHERE email IS NULL OR email = '' OR nome IS NULL OR nome = ''
        ");
        $stmt->execute();
        $invalid_users = $stmt->fetchAll();
        
        if (count($invalid_users) > 0) {
            echo "   🗑️  Removendo " . count($invalid_users) . " usuários com dados inválidos\n";
            
            foreach ($invalid_users as $user) {
                echo "      - ID {$user['id']}: '{$user['nome']}' <{$user['email']}>\n";
                
                if (!$this->dryRun) {
                    // Primeiro, mover veículos para usuário admin (ID 1) se existir
                    $admin_check = $this->connection->prepare("SELECT id FROM usuarios WHERE id = 1 LIMIT 1");
                    $admin_check->execute();
                    
                    if ($admin_check->fetch()) {
                        $this->connection->prepare("UPDATE veiculos SET id_usuario = 1 WHERE id_usuario = ?")
                            ->execute([$user['id']]);
                    }
                    
                    // Remover usuário
                    $this->connection->prepare("DELETE FROM usuarios WHERE id = ?")
                        ->execute([$user['id']]);
                }
            }
            
            $this->cleaned['users']['invalid_removed'] = count($invalid_users);
        }
        
        // Corrigir emails duplicados
        $stmt = $this->connection->query("
            SELECT email, GROUP_CONCAT(id ORDER BY id) as ids
            FROM usuarios 
            WHERE email IS NOT NULL AND email != ''
            GROUP BY email 
            HAVING COUNT(*) > 1
        ");
        $duplicates = $stmt->fetchAll();
        
        if (count($duplicates) > 0) {
            echo "   📧 Corrigindo " . count($duplicates) . " emails duplicados\n";
            
            foreach ($duplicates as $duplicate) {
                $ids = explode(',', $duplicate['ids']);
                $keep_id = array_shift($ids); // Manter o primeiro
                
                foreach ($ids as $remove_id) {
                    $new_email = $duplicate['email'] . '.duplicated.' . $remove_id;
                    echo "      - Alterando email do usuário ID {$remove_id} para: {$new_email}\n";
                    
                    if (!$this->dryRun) {
                        $this->connection->prepare("UPDATE usuarios SET email = ? WHERE id = ?")
                            ->execute([$new_email, $remove_id]);
                    }
                }
            }
            
            $this->cleaned['users']['duplicate_emails_fixed'] = count($duplicates);
        }
        
        echo "   ✅ Limpeza de usuários concluída\n\n";
    }
    
    private function cleanVehicles(): void
    {
        echo "🚗 Limpando dados de veículos...\n";
        
        // Corrigir veículos sem IMEI
        $stmt = $this->connection->query("
            SELECT id, nome FROM veiculos 
            WHERE imei IS NULL OR imei = ''
        ");
        $vehicles_without_imei = $stmt->fetchAll();
        
        if (count($vehicles_without_imei) > 0) {
            echo "   📱 Gerando IMEIs para " . count($vehicles_without_imei) . " veículos\n";
            
            foreach ($vehicles_without_imei as $vehicle) {
                $generated_imei = 'LEGACY' . str_pad((string)$vehicle['id'], 11, '0', STR_PAD_LEFT);
                echo "      - ID {$vehicle['id']} '{$vehicle['nome']}': {$generated_imei}\n";
                
                if (!$this->dryRun) {
                    $this->connection->prepare("UPDATE veiculos SET imei = ? WHERE id = ?")
                        ->execute([$generated_imei, $vehicle['id']]);
                }
            }
            
            $this->cleaned['vehicles']['imei_generated'] = count($vehicles_without_imei);
        }
        
        // Corrigir IMEIs duplicados
        $stmt = $this->connection->query("
            SELECT imei, GROUP_CONCAT(id ORDER BY id) as ids
            FROM veiculos 
            WHERE imei IS NOT NULL AND imei != ''
            GROUP BY imei 
            HAVING COUNT(*) > 1
        ");
        $duplicate_imeis = $stmt->fetchAll();
        
        if (count($duplicate_imeis) > 0) {
            echo "   📱 Corrigindo " . count($duplicate_imeis) . " IMEIs duplicados\n";
            
            foreach ($duplicate_imeis as $duplicate) {
                $ids = explode(',', $duplicate['ids']);
                array_shift($ids); // Manter o primeiro
                
                foreach ($ids as $vehicle_id) {
                    $new_imei = $duplicate['imei'] . '_DUP_' . $vehicle_id;
                    echo "      - Alterando IMEI do veículo ID {$vehicle_id} para: {$new_imei}\n";
                    
                    if (!$this->dryRun) {
                        $this->connection->prepare("UPDATE veiculos SET imei = ? WHERE id = ?")
                            ->execute([$new_imei, $vehicle_id]);
                    }
                }
            }
            
            $this->cleaned['vehicles']['duplicate_imeis_fixed'] = count($duplicate_imeis);
        }
        
        echo "   ✅ Limpeza de veículos concluída\n\n";
    }
    
    private function cleanPositions(): void
    {
        echo "📍 Limpando posições GPS...\n";
        
        // Remover posições com coordenadas inválidas
        $stmt = $this->connection->query("
            SELECT COUNT(*) as count 
            FROM posicoes 
            WHERE latitude = 0 OR longitude = 0 OR latitude IS NULL OR longitude IS NULL
        ");
        $invalid_positions = $stmt->fetch()['count'];
        
        if ($invalid_positions > 0) {
            echo "   🗑️  Removendo {$invalid_positions} posições com coordenadas inválidas\n";
            
            if (!$this->dryRun) {
                $this->connection->exec("
                    DELETE FROM posicoes 
                    WHERE latitude = 0 OR longitude = 0 OR latitude IS NULL OR longitude IS NULL
                ");
            }
            
            $this->cleaned['positions']['invalid_removed'] = $invalid_positions;
        }
        
        // Arquivar posições muito antigas (opcional)
        $stmt = $this->connection->query("
            SELECT COUNT(*) as count 
            FROM posicoes 
            WHERE data_hora < DATE_SUB(NOW(), INTERVAL 2 YEAR)
        ");
        $very_old_positions = $stmt->fetch()['count'];
        
        if ($very_old_positions > 0) {
            echo "   📦 Encontradas {$very_old_positions} posições com mais de 2 anos\n";
            echo "      💡 Considere executar arquivamento separadamente se necessário\n";
        }
        
        echo "   ✅ Limpeza de posições concluída\n\n";
    }
    
    private function cleanOrphanRecords(): void
    {
        echo "🔗 Removendo registros órfãos...\n";
        
        // Remover veículos órfãos (sem usuário válido)
        $stmt = $this->connection->query("
            SELECT COUNT(*) as count
            FROM veiculos v
            LEFT JOIN usuarios u ON v.id_usuario = u.id
            WHERE u.id IS NULL
        ");
        $orphan_vehicles = $stmt->fetch()['count'];
        
        if ($orphan_vehicles > 0) {
            echo "   🗑️  Removendo {$orphan_vehicles} veículos órfãos\n";
            
            if (!$this->dryRun) {
                $this->connection->exec("
                    DELETE v FROM veiculos v
                    LEFT JOIN usuarios u ON v.id_usuario = u.id
                    WHERE u.id IS NULL
                ");
            }
            
            $this->cleaned['orphans']['vehicles_removed'] = $orphan_vehicles;
        }
        
        // Remover posições órfãs (sem veículo válido) - em lotes
        $batch_size = 10000;
        $total_removed = 0;
        
        do {
            $stmt = $this->connection->query("
                SELECT COUNT(*) as count
                FROM posicoes p
                LEFT JOIN veiculos v ON p.id_veiculo = v.id
                WHERE v.id IS NULL
                LIMIT {$batch_size}
            ");
            $orphan_positions = $stmt->fetch()['count'];
            
            if ($orphan_positions > 0) {
                if ($total_removed === 0) {
                    echo "   🗑️  Removendo posições órfãs (em lotes de {$batch_size})...\n";
                }
                
                if (!$this->dryRun) {
                    $this->connection->exec("
                        DELETE p FROM posicoes p
                        LEFT JOIN veiculos v ON p.id_veiculo = v.id
                        WHERE v.id IS NULL
                        LIMIT {$batch_size}
                    ");
                }
                
                $total_removed += $orphan_positions;
                echo "      - Removidas {$orphan_positions} posições (total: {$total_removed})\n";
            }
            
        } while ($orphan_positions > 0 && $orphan_positions == $batch_size);
        
        if ($total_removed > 0) {
            $this->cleaned['orphans']['positions_removed'] = $total_removed;
        }
        
        echo "   ✅ Limpeza de registros órfãos concluída\n\n";
    }
    
    private function updateDataTypes(): void
    {
        echo "🔧 Atualizando tipos de dados...\n";
        
        // Lista de alterações de schema necessárias
        $schema_updates = [
            "ALTER TABLE usuarios MODIFY email VARCHAR(255) NOT NULL",
            "ALTER TABLE usuarios MODIFY nome VARCHAR(255) NOT NULL", 
            "ALTER TABLE veiculos MODIFY imei VARCHAR(20) NOT NULL UNIQUE",
            "ALTER TABLE posicoes MODIFY latitude DECIMAL(10,8) NOT NULL",
            "ALTER TABLE posicoes MODIFY longitude DECIMAL(11,8) NOT NULL",
        ];
        
        foreach ($schema_updates as $sql) {
            echo "   🔧 Executando: " . substr($sql, 0, 50) . "...\n";
            
            if (!$this->dryRun) {
                try {
                    $this->connection->exec($sql);
                    echo "      ✅ Sucesso\n";
                } catch (PDOException $e) {
                    echo "      ⚠️  Aviso: " . $e->getMessage() . "\n";
                }
            } else {
                echo "      🔍 (dry-run)\n";
            }
        }
        
        echo "   ✅ Atualização de tipos concluída\n\n";
    }
    
    private function generateSummary(): void
    {
        echo str_repeat("=", 60) . "\n";
        echo "📋 RESUMO DA LIMPEZA" . ($this->dryRun ? " (DRY-RUN)" : "") . "\n";
        echo str_repeat("=", 60) . "\n\n";
        
        $total_changes = 0;
        
        if (!empty($this->cleaned['users'])) {
            echo "👥 USUÁRIOS:\n";
            foreach ($this->cleaned['users'] as $action => $count) {
                echo "   - " . ucfirst(str_replace('_', ' ', $action)) . ": {$count}\n";
                $total_changes += $count;
            }
            echo "\n";
        }
        
        if (!empty($this->cleaned['vehicles'])) {
            echo "🚗 VEÍCULOS:\n";
            foreach ($this->cleaned['vehicles'] as $action => $count) {
                echo "   - " . ucfirst(str_replace('_', ' ', $action)) . ": {$count}\n";
                $total_changes += $count;
            }
            echo "\n";
        }
        
        if (!empty($this->cleaned['positions'])) {
            echo "📍 POSIÇÕES:\n";
            foreach ($this->cleaned['positions'] as $action => $count) {
                echo "   - " . ucfirst(str_replace('_', ' ', $action)) . ": {$count}\n";
                $total_changes += $count;
            }
            echo "\n";
        }
        
        if (!empty($this->cleaned['orphans'])) {
            echo "🔗 REGISTROS ÓRFÃOS:\n";
            foreach ($this->cleaned['orphans'] as $action => $count) {
                echo "   - " . ucfirst(str_replace('_', ' ', $action)) . ": {$count}\n";
                $total_changes += $count;
            }
            echo "\n";
        }
        
        if ($total_changes > 0) {
            if ($this->dryRun) {
                echo "🔍 Total de alterações que seriam feitas: {$total_changes}\n";
                echo "💡 Execute sem --dry-run para aplicar as alterações\n\n";
            } else {
                echo "✅ Total de alterações aplicadas: {$total_changes}\n";
                echo "🎯 Dados limpos e prontos para migração!\n\n";
            }
        } else {
            echo "✨ Nenhuma limpeza necessária - dados já estão consistentes!\n\n";
        }
        
        // Salvar log da limpeza
        $log_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'dry_run' => $this->dryRun,
            'changes' => $this->cleaned,
            'total_changes' => $total_changes
        ];
        
        $log_file = __DIR__ . '/../../logs/cleaning-log-' . date('Y-m-d-H-i-s') . '.json';
        file_put_contents($log_file, json_encode($log_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        echo "💾 Log salvo em: {$log_file}\n";
    }
}

// Verificar argumentos da linha de comando
$dry_run = in_array('--dry-run', $argv);

if ($dry_run) {
    echo "🔍 Executando em modo DRY-RUN (apenas visualização)\n";
    echo "💡 Execute sem --dry-run para aplicar as alterações\n\n";
}

// Executar limpeza
try {
    $cleaner = new LegacyDataCleaner($dry_run);
    $cleaner->clean();
} catch (Exception $e) {
    echo "❌ Erro durante limpeza: " . $e->getMessage() . "\n";
    echo "🔍 Verifique se o banco de dados está configurado corretamente\n";
    exit(1);
}