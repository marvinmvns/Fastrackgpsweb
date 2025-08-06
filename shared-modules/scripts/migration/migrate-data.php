<?php
/**
 * Script Principal de Migração de Dados
 * 
 * Este script migra dados do sistema legacy para o sistema moderno,
 * transformando e adaptando os dados para a nova estrutura.
 * 
 * @package FastrackGPS
 * @subpackage Migration
 * @author FastrackGPS Team
 * @version 2.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../../modern-fastrackgps/vendor/autoload.php';

use Ramsey\Uuid\Uuid;

class DataMigrator
{
    private PDO $legacyDb;
    private PDO $modernDb;
    private array $migrated = [];
    private array $mapping = []; // ID mapping between systems
    private bool $dryRun = false;
    
    public function __construct(bool $dryRun = false)
    {
        $this->dryRun = $dryRun;
        $this->setupConnections();
        $this->loadIdMappings();
        
        if ($this->dryRun) {
            echo "🔍 MODO DRY-RUN ATIVADO - Nenhuma alteração será feita\n\n";
        }
    }
    
    private function setupConnections(): void
    {
        $config = require __DIR__ . '/../../config/database.php';
        
        // Conexão com banco legacy
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
        $this->legacyDb = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        // Para este exemplo, usando mesmo banco (em produção seria banco separado)
        $this->modernDb = clone $this->legacyDb;
    }
    
    private function loadIdMappings(): void
    {
        // Carregar mapeamentos existentes de migrações anteriores
        $mapping_file = __DIR__ . '/../../logs/id-mappings.json';
        if (file_exists($mapping_file)) {
            $this->mapping = json_decode(file_get_contents($mapping_file), true) ?? [];
        }
    }
    
    private function saveIdMappings(): void
    {
        $mapping_file = __DIR__ . '/../../logs/id-mappings.json';
        file_put_contents($mapping_file, json_encode($this->mapping, JSON_PRETTY_PRINT));
    }
    
    public function migrate(): void
    {
        echo "🚀 Iniciando migração de dados do sistema legacy para o moderno...\n\n";
        
        if (!$this->dryRun) {
            $this->createBackup();
            $this->createModernTables();
        }
        
        $this->migrateUsers();
        $this->migrateVehicles();
        $this->migrateGeofences();
        $this->migratePositions(); // Por último devido ao volume
        $this->migrateAlerts();
        $this->migrateCommands();
        
        if (!$this->dryRun) {
            $this->saveIdMappings();
        }
        
        $this->generateSummary();
    }
    
    private function createBackup(): void
    {
        echo "💾 Criando backup antes da migração...\n";
        
        $config = require __DIR__ . '/../../config/database.php';
        $timestamp = date('Y-m-d-H-i-s');
        $backup_file = __DIR__ . "/../../database/backup-before-migration-{$timestamp}.sql";
        
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
            throw new Exception("Falha ao criar backup. Abortando migração.");
        }
    }
    
    private function createModernTables(): void
    {
        echo "🏗️  Criando tabelas do sistema moderno...\n";
        
        $tables = [
            'modern_users' => "
                CREATE TABLE IF NOT EXISTS modern_users (
                    id VARCHAR(36) PRIMARY KEY,
                    legacy_id INT NULL,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL UNIQUE,
                    password_hash VARCHAR(255) NOT NULL,
                    is_active BOOLEAN DEFAULT TRUE,
                    is_admin BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_legacy_id (legacy_id),
                    INDEX idx_email (email)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            
            'modern_vehicles' => "
                CREATE TABLE IF NOT EXISTS modern_vehicles (
                    id VARCHAR(36) PRIMARY KEY,
                    legacy_id INT NULL,
                    user_id VARCHAR(36) NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    imei VARCHAR(20) NOT NULL UNIQUE,
                    plate VARCHAR(20) NULL,
                    model VARCHAR(100) NULL,
                    color VARCHAR(50) NULL,
                    is_active BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES modern_users(id) ON DELETE CASCADE,
                    INDEX idx_legacy_id (legacy_id),
                    INDEX idx_user_id (user_id),
                    INDEX idx_imei (imei)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            
            'modern_positions' => "
                CREATE TABLE IF NOT EXISTS modern_positions (
                    id VARCHAR(36) PRIMARY KEY,
                    legacy_id INT NULL,
                    vehicle_id VARCHAR(36) NOT NULL,
                    latitude DECIMAL(10,8) NOT NULL,
                    longitude DECIMAL(11,8) NOT NULL,
                    altitude INT NULL,
                    speed INT NULL,
                    course INT NULL,
                    recorded_at TIMESTAMP NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (vehicle_id) REFERENCES modern_vehicles(id) ON DELETE CASCADE,
                    INDEX idx_vehicle_recorded (vehicle_id, recorded_at),
                    INDEX idx_recorded_at (recorded_at),
                    INDEX idx_legacy_id (legacy_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            
            'modern_geofences' => "
                CREATE TABLE IF NOT EXISTS modern_geofences (
                    id VARCHAR(36) PRIMARY KEY,
                    legacy_id INT NULL,
                    user_id VARCHAR(36) NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    type ENUM('circle', 'polygon') NOT NULL,
                    coordinates JSON NOT NULL,
                    radius INT NULL,
                    is_active BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES modern_users(id) ON DELETE CASCADE,
                    INDEX idx_legacy_id (legacy_id),
                    INDEX idx_user_id (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            
            'modern_alerts' => "
                CREATE TABLE IF NOT EXISTS modern_alerts (
                    id VARCHAR(36) PRIMARY KEY,
                    legacy_id INT NULL,
                    vehicle_id VARCHAR(36) NOT NULL,
                    type VARCHAR(50) NOT NULL,
                    message TEXT NOT NULL,
                    is_read BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (vehicle_id) REFERENCES modern_vehicles(id) ON DELETE CASCADE,
                    INDEX idx_vehicle_id (vehicle_id),
                    INDEX idx_type (type),
                    INDEX idx_created_at (created_at),
                    INDEX idx_legacy_id (legacy_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            
            'modern_commands' => "
                CREATE TABLE IF NOT EXISTS modern_commands (
                    id VARCHAR(36) PRIMARY KEY,
                    legacy_id INT NULL,
                    vehicle_id VARCHAR(36) NOT NULL,
                    command_type VARCHAR(50) NOT NULL,
                    command_data JSON NULL,
                    status ENUM('pending', 'sent', 'acknowledged', 'failed') DEFAULT 'pending',
                    response TEXT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    sent_at TIMESTAMP NULL,
                    acknowledged_at TIMESTAMP NULL,
                    FOREIGN KEY (vehicle_id) REFERENCES modern_vehicles(id) ON DELETE CASCADE,
                    INDEX idx_vehicle_id (vehicle_id),
                    INDEX idx_status (status),
                    INDEX idx_created_at (created_at),
                    INDEX idx_legacy_id (legacy_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            "
        ];
        
        foreach ($tables as $table_name => $sql) {
            echo "   🏗️  Criando tabela {$table_name}...\n";
            $this->modernDb->exec($sql);
        }
        
        echo "   ✅ Tabelas criadas com sucesso\n\n";
    }
    
    private function migrateUsers(): void
    {
        echo "👥 Migrando usuários...\n";
        
        $stmt = $this->legacyDb->query("
            SELECT id, nome, email, senha, ativo, tipo 
            FROM usuarios 
            WHERE email IS NOT NULL AND email != '' 
            ORDER BY id
        ");
        $users = $stmt->fetchAll();
        
        $migrated_count = 0;
        
        foreach ($users as $user) {
            $new_id = Uuid::uuid4()->toString();
            
            echo "   👤 {$user['nome']} <{$user['email']}>\n";
            
            if (!$this->dryRun) {
                $stmt = $this->modernDb->prepare("
                    INSERT INTO modern_users 
                    (id, legacy_id, name, email, password_hash, is_active, is_admin) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $new_id,
                    $user['id'],
                    $user['nome'],
                    $user['email'],
                    password_hash($user['senha'] ?? 'temp123', PASSWORD_DEFAULT), // Hash da senha legacy
                    (bool)$user['ativo'],
                    in_array($user['tipo'] ?? '', ['admin', 'master'])
                ]);
            }
            
            $this->mapping['users'][$user['id']] = $new_id;
            $migrated_count++;
        }
        
        $this->migrated['users'] = $migrated_count;
        echo "   ✅ {$migrated_count} usuários migrados\n\n";
    }
    
    private function migrateVehicles(): void
    {
        echo "🚗 Migrando veículos...\n";
        
        $stmt = $this->legacyDb->query("
            SELECT v.*, u.id as user_exists
            FROM veiculos v
            INNER JOIN usuarios u ON v.id_usuario = u.id
            WHERE v.imei IS NOT NULL AND v.imei != ''
            ORDER BY v.id
        ");
        $vehicles = $stmt->fetchAll();
        
        $migrated_count = 0;
        
        foreach ($vehicles as $vehicle) {
            if (!isset($this->mapping['users'][$vehicle['id_usuario']])) {
                echo "   ⚠️  Pulando veículo {$vehicle['nome']} - usuário não encontrado\n";
                continue;
            }
            
            $new_id = Uuid::uuid4()->toString();
            $user_id = $this->mapping['users'][$vehicle['id_usuario']];
            
            echo "   🚗 {$vehicle['nome']} ({$vehicle['imei']})\n";
            
            if (!$this->dryRun) {
                $stmt = $this->modernDb->prepare("
                    INSERT INTO modern_vehicles 
                    (id, legacy_id, user_id, name, imei, plate, model, color, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $new_id,
                    $vehicle['id'],
                    $user_id,
                    $vehicle['nome'],
                    $vehicle['imei'],
                    $vehicle['placa'] ?? null,
                    $vehicle['modelo'] ?? null,
                    $vehicle['cor'] ?? null,
                    (bool)($vehicle['ativo'] ?? 1)
                ]);
            }
            
            $this->mapping['vehicles'][$vehicle['id']] = $new_id;
            $migrated_count++;
        }
        
        $this->migrated['vehicles'] = $migrated_count;
        echo "   ✅ {$migrated_count} veículos migrados\n\n";
    }
    
    private function migrateGeofences(): void
    {
        echo "🔒 Migrando cercas virtuais...\n";
        
        // Verificar se tabela existe
        $stmt = $this->legacyDb->query("SHOW TABLES LIKE 'cercas'");
        if (!$stmt->fetch()) {
            echo "   ⚠️  Tabela 'cercas' não encontrada - pulando migração\n\n";
            $this->migrated['geofences'] = 0;
            return;
        }
        
        $stmt = $this->legacyDb->query("
            SELECT c.*, u.id as user_exists
            FROM cercas c
            INNER JOIN usuarios u ON c.id_usuario = u.id
            ORDER BY c.id
        ");
        $geofences = $stmt->fetchAll();
        
        $migrated_count = 0;
        
        foreach ($geofences as $fence) {
            if (!isset($this->mapping['users'][$fence['id_usuario']])) {
                echo "   ⚠️  Pulando cerca {$fence['nome']} - usuário não encontrado\n";
                continue;
            }
            
            $new_id = Uuid::uuid4()->toString();
            $user_id = $this->mapping['users'][$fence['id_usuario']];
            
            echo "   🔒 {$fence['nome']}\n";
            
            if (!$this->dryRun) {
                // Converter coordenadas legacy para formato JSON moderno
                $coordinates = [
                    'center' => [
                        'lat' => (float)$fence['latitude'],
                        'lng' => (float)$fence['longitude']
                    ]
                ];
                
                $stmt = $this->modernDb->prepare("
                    INSERT INTO modern_geofences 
                    (id, legacy_id, user_id, name, type, coordinates, radius, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $new_id,
                    $fence['id'],
                    $user_id,
                    $fence['nome'],
                    'circle', // Assumindo cercas circulares por padrão
                    json_encode($coordinates),
                    (int)($fence['raio'] ?? 1000),
                    (bool)($fence['ativo'] ?? 1)
                ]);
            }
            
            $this->mapping['geofences'][$fence['id']] = $new_id;
            $migrated_count++;
        }
        
        $this->migrated['geofences'] = $migrated_count;
        echo "   ✅ {$migrated_count} cercas virtuais migradas\n\n";
    }
    
    private function migratePositions(): void
    {
        echo "📍 Migrando posições GPS...\n";
        
        // Contar total de posições
        $stmt = $this->legacyDb->query("SELECT COUNT(*) as total FROM posicoes");
        $total = $stmt->fetch()['total'];
        
        echo "   📊 Total de posições a migrar: {$total}\n";
        
        if ($total > 100000) {
            echo "   ⚡ Migração será feita em lotes devido ao volume\n";
        }
        
        $batch_size = 10000;
        $offset = 0;
        $migrated_count = 0;
        
        do {
            $stmt = $this->legacyDb->prepare("
                SELECT p.*, v.id as vehicle_exists
                FROM posicoes p
                INNER JOIN veiculos v ON p.id_veiculo = v.id
                WHERE p.latitude != 0 AND p.longitude != 0
                  AND p.latitude IS NOT NULL AND p.longitude IS NOT NULL
                ORDER BY p.id
                LIMIT {$batch_size} OFFSET {$offset}
            ");
            $stmt->execute();
            $positions = $stmt->fetchAll();
            
            if (count($positions) > 0) {
                echo "   📍 Processando lote " . ($offset / $batch_size + 1) . " (" . count($positions) . " posições)\n";
                
                foreach ($positions as $position) {
                    if (!isset($this->mapping['vehicles'][$position['id_veiculo']])) {
                        continue; // Pular posição de veículo não migrado
                    }
                    
                    $new_id = Uuid::uuid4()->toString();
                    $vehicle_id = $this->mapping['vehicles'][$position['id_veiculo']];
                    
                    if (!$this->dryRun) {
                        $stmt = $this->modernDb->prepare("
                            INSERT INTO modern_positions 
                            (id, legacy_id, vehicle_id, latitude, longitude, altitude, speed, course, recorded_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        
                        $stmt->execute([
                            $new_id,
                            $position['id'],
                            $vehicle_id,
                            (float)$position['latitude'],
                            (float)$position['longitude'],
                            (int)($position['altitude'] ?? 0),
                            (int)($position['velocidade'] ?? 0),
                            (int)($position['direcao'] ?? 0),
                            $position['data_hora']
                        ]);
                    }
                    
                    $migrated_count++;
                }
            }
            
            $offset += $batch_size;
            
        } while (count($positions) == $batch_size);
        
        $this->migrated['positions'] = $migrated_count;
        echo "   ✅ {$migrated_count} posições GPS migradas\n\n";
    }
    
    private function migrateAlerts(): void
    {
        echo "🚨 Migrando alertas...\n";
        
        // Verificar se tabela existe
        $stmt = $this->legacyDb->query("SHOW TABLES LIKE 'alertas'");
        if (!$stmt->fetch()) {
            echo "   ⚠️  Tabela 'alertas' não encontrada - pulando migração\n\n";
            $this->migrated['alerts'] = 0;
            return;
        }
        
        $stmt = $this->legacyDb->query("
            SELECT a.*, v.id as vehicle_exists
            FROM alertas a
            INNER JOIN veiculos v ON a.id_veiculo = v.id
            ORDER BY a.id
        ");
        $alerts = $stmt->fetchAll();
        
        $migrated_count = 0;
        
        foreach ($alerts as $alert) {
            if (!isset($this->mapping['vehicles'][$alert['id_veiculo']])) {
                continue; // Pular alerta de veículo não migrado
            }
            
            $new_id = Uuid::uuid4()->toString();
            $vehicle_id = $this->mapping['vehicles'][$alert['id_veiculo']];
            
            if (!$this->dryRun) {
                $stmt = $this->modernDb->prepare("
                    INSERT INTO modern_alerts 
                    (id, legacy_id, vehicle_id, type, message, is_read, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $new_id,
                    $alert['id'],
                    $vehicle_id,
                    $alert['tipo'] ?? 'general',
                    $alert['mensagem'] ?? 'Alerta migrado do sistema legacy',
                    (bool)($alert['lido'] ?? 0),
                    $alert['data_hora'] ?? date('Y-m-d H:i:s')
                ]);
            }
            
            $migrated_count++;
        }
        
        $this->migrated['alerts'] = $migrated_count;
        echo "   ✅ {$migrated_count} alertas migrados\n\n";
    }
    
    private function migrateCommands(): void
    {
        echo "📡 Migrando comandos...\n";
        
        // Verificar se tabela existe
        $stmt = $this->legacyDb->query("SHOW TABLES LIKE 'comandos'");
        if (!$stmt->fetch()) {
            echo "   ⚠️  Tabela 'comandos' não encontrada - pulando migração\n\n";
            $this->migrated['commands'] = 0;
            return;
        }
        
        $stmt = $this->legacyDb->query("
            SELECT c.*, v.id as vehicle_exists
            FROM comandos c
            INNER JOIN veiculos v ON c.id_veiculo = v.id
            ORDER BY c.id
        ");
        $commands = $stmt->fetchAll();
        
        $migrated_count = 0;
        
        foreach ($commands as $command) {
            if (!isset($this->mapping['vehicles'][$command['id_veiculo']])) {
                continue; // Pular comando de veículo não migrado
            }
            
            $new_id = Uuid::uuid4()->toString();
            $vehicle_id = $this->mapping['vehicles'][$command['id_veiculo']];
            
            if (!$this->dryRun) {
                // Mapear status legacy para moderno
                $status_mapping = [
                    'enviado' => 'sent',
                    'pendente' => 'pending',
                    'confirmado' => 'acknowledged',
                    'erro' => 'failed'
                ];
                
                $status = $status_mapping[$command['status'] ?? 'pendente'] ?? 'pending';
                
                $stmt = $this->modernDb->prepare("
                    INSERT INTO modern_commands 
                    (id, legacy_id, vehicle_id, command_type, command_data, status, response, created_at, sent_at, acknowledged_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $command_data = [
                    'original_command' => $command['comando'] ?? '',
                    'parameters' => $command['parametros'] ?? null
                ];
                
                $stmt->execute([
                    $new_id,
                    $command['id'],
                    $vehicle_id,
                    $command['tipo'] ?? 'generic',
                    json_encode($command_data),
                    $status,
                    $command['resposta'] ?? null,
                    $command['data_criacao'] ?? date('Y-m-d H:i:s'),
                    $command['data_envio'] ?? null,
                    $command['data_confirmacao'] ?? null
                ]);
            }
            
            $migrated_count++;
        }
        
        $this->migrated['commands'] = $migrated_count;
        echo "   ✅ {$migrated_count} comandos migrados\n\n";
    }
    
    private function generateSummary(): void
    {
        echo str_repeat("=", 60) . "\n";
        echo "📋 RESUMO DA MIGRAÇÃO" . ($this->dryRun ? " (DRY-RUN)" : "") . "\n";
        echo str_repeat("=", 60) . "\n\n";
        
        $total_migrated = 0;
        
        foreach ($this->migrated as $entity => $count) {
            $icon = match($entity) {
                'users' => '👥',
                'vehicles' => '🚗',
                'positions' => '📍',
                'geofences' => '🔒',
                'alerts' => '🚨',
                'commands' => '📡',
                default => '📊'
            };
            
            echo "{$icon} " . ucfirst($entity) . ": {$count}\n";
            $total_migrated += $count;
        }
        
        echo "\n✨ Total de registros migrados: {$total_migrated}\n\n";
        
        if ($this->dryRun) {
            echo "🔍 Esta foi uma execução de teste (dry-run)\n";
            echo "💡 Execute sem --dry-run para aplicar a migração\n\n";
        } else {
            echo "🎉 Migração concluída com sucesso!\n";
            echo "🔄 Execute o script de validação para verificar a integridade\n\n";
        }
        
        // Salvar relatório da migração
        $report_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'dry_run' => $this->dryRun,
            'migrated' => $this->migrated,
            'total_migrated' => $total_migrated,
            'id_mappings_count' => array_map('count', $this->mapping)
        ];
        
        $report_file = __DIR__ . '/../../logs/migration-report-' . date('Y-m-d-H-i-s') . '.json';
        file_put_contents($report_file, json_encode($report_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        echo "💾 Relatório salvo em: {$report_file}\n";
    }
}

// Verificar argumentos da linha de comando
$dry_run = in_array('--dry-run', $argv);

if ($dry_run) {
    echo "🔍 Executando em modo DRY-RUN (apenas visualização)\n";
    echo "💡 Execute sem --dry-run para aplicar a migração\n\n";
}

// Executar migração
try {
    $migrator = new DataMigrator($dry_run);
    $migrator->migrate();
} catch (Exception $e) {
    echo "❌ Erro durante migração: " . $e->getMessage() . "\n";
    echo "🔍 Verifique se o banco de dados está configurado corretamente\n";
    exit(1);
}