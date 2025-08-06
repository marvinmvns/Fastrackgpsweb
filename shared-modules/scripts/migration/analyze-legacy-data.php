<?php
/**
 * Script de Análise de Dados Legacy
 * 
 * Este script analisa os dados do sistema legacy para preparar a migração
 * para o sistema moderno. Gera relatórios de inconsistências e problemas
 * que precisam ser resolvidos antes da migração.
 * 
 * @package FastrackGPS
 * @subpackage Migration
 * @author FastrackGPS Team
 * @version 2.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class LegacyDataAnalyzer
{
    private PDO $connection;
    private array $analysis = [];
    private array $issues = [];
    
    public function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';
        
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
        $this->connection = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    
    public function analyze(): void
    {
        echo "🔍 Iniciando análise dos dados legacy...\n\n";
        
        $this->analyzeUsers();
        $this->analyzeVehicles();
        $this->analyzePositions();
        $this->analyzeAlerts();
        $this->analyzeGeofences();
        $this->analyzeCommands();
        $this->analyzeDatabaseIntegrity();
        
        $this->generateReport();
    }
    
    private function analyzeUsers(): void
    {
        echo "📊 Analisando usuários...\n";
        
        // Contar usuários total
        $stmt = $this->connection->query("SELECT COUNT(*) as total FROM usuarios");
        $total = $stmt->fetch()['total'];
        $this->analysis['users']['total'] = $total;
        
        // Usuários ativos/inativos
        $stmt = $this->connection->query("
            SELECT 
                SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN ativo = 0 THEN 1 ELSE 0 END) as inactive
            FROM usuarios
        ");
        $status = $stmt->fetch();
        $this->analysis['users']['active'] = $status['active'] ?? 0;
        $this->analysis['users']['inactive'] = $status['inactive'] ?? 0;
        
        // Verificar usuários com dados inválidos
        $stmt = $this->connection->query("
            SELECT COUNT(*) as invalid 
            FROM usuarios 
            WHERE email IS NULL OR email = '' OR nome IS NULL OR nome = ''
        ");
        $invalid = $stmt->fetch()['invalid'];
        if ($invalid > 0) {
            $this->issues[] = "⚠️  {$invalid} usuários com dados obrigatórios em branco";
        }
        
        // Verificar emails duplicados
        $stmt = $this->connection->query("
            SELECT email, COUNT(*) as count 
            FROM usuarios 
            WHERE email IS NOT NULL AND email != ''
            GROUP BY email 
            HAVING count > 1
        ");
        $duplicates = $stmt->fetchAll();
        if (count($duplicates) > 0) {
            $this->issues[] = "⚠️  " . count($duplicates) . " emails duplicados encontrados";
        }
        
        echo "   ✅ {$total} usuários analisados\n";
    }
    
    private function analyzeVehicles(): void
    {
        echo "📊 Analisando veículos...\n";
        
        // Contar veículos total
        $stmt = $this->connection->query("SELECT COUNT(*) as total FROM veiculos");
        $total = $stmt->fetch()['total'];
        $this->analysis['vehicles']['total'] = $total;
        
        // Veículos por usuário
        $stmt = $this->connection->query("
            SELECT AVG(vehicle_count) as avg_per_user
            FROM (
                SELECT COUNT(*) as vehicle_count 
                FROM veiculos 
                GROUP BY id_usuario
            ) as counts
        ");
        $avg = $stmt->fetch()['avg_per_user'];
        $this->analysis['vehicles']['avg_per_user'] = round($avg, 2);
        
        // Verificar veículos sem IMEI
        $stmt = $this->connection->query("
            SELECT COUNT(*) as without_imei 
            FROM veiculos 
            WHERE imei IS NULL OR imei = ''
        ");
        $without_imei = $stmt->fetch()['without_imei'];
        if ($without_imei > 0) {
            $this->issues[] = "⚠️  {$without_imei} veículos sem IMEI";
        }
        
        // Verificar IMEIs duplicados
        $stmt = $this->connection->query("
            SELECT imei, COUNT(*) as count 
            FROM veiculos 
            WHERE imei IS NOT NULL AND imei != ''
            GROUP BY imei 
            HAVING count > 1
        ");
        $duplicate_imeis = $stmt->fetchAll();
        if (count($duplicate_imeis) > 0) {
            $this->issues[] = "⚠️  " . count($duplicate_imeis) . " IMEIs duplicados encontrados";
        }
        
        echo "   ✅ {$total} veículos analisados\n";
    }
    
    private function analyzePositions(): void
    {
        echo "📊 Analisando posições GPS...\n";
        
        // Contar posições total
        $stmt = $this->connection->query("SELECT COUNT(*) as total FROM posicoes");
        $total = $stmt->fetch()['total'];
        $this->analysis['positions']['total'] = $total;
        
        // Posições dos últimos 30 dias
        $stmt = $this->connection->query("
            SELECT COUNT(*) as recent 
            FROM posicoes 
            WHERE data_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $recent = $stmt->fetch()['recent'];
        $this->analysis['positions']['last_30_days'] = $recent;
        
        // Verificar posições inválidas
        $stmt = $this->connection->query("
            SELECT COUNT(*) as invalid 
            FROM posicoes 
            WHERE latitude = 0 OR longitude = 0 OR latitude IS NULL OR longitude IS NULL
        ");
        $invalid = $stmt->fetch()['invalid'];
        if ($invalid > 0) {
            $this->issues[] = "⚠️  {$invalid} posições com coordenadas inválidas";
        }
        
        // Verificar posições muito antigas
        $stmt = $this->connection->query("
            SELECT COUNT(*) as old_positions 
            FROM posicoes 
            WHERE data_hora < DATE_SUB(NOW(), INTERVAL 1 YEAR)
        ");
        $old = $stmt->fetch()['old_positions'];
        $this->analysis['positions']['older_than_year'] = $old;
        
        echo "   ✅ {$total} posições analisadas\n";
    }
    
    private function analyzeAlerts(): void
    {
        echo "📊 Analisando alertas...\n";
        
        // Verificar se tabela de alertas existe
        $stmt = $this->connection->query("SHOW TABLES LIKE 'alertas'");
        if ($stmt->fetch()) {
            $stmt = $this->connection->query("SELECT COUNT(*) as total FROM alertas");
            $total = $stmt->fetch()['total'];
            $this->analysis['alerts']['total'] = $total;
            
            // Alertas por tipo
            $stmt = $this->connection->query("
                SELECT tipo, COUNT(*) as count 
                FROM alertas 
                GROUP BY tipo
            ");
            $types = $stmt->fetchAll();
            $this->analysis['alerts']['by_type'] = $types;
            
            echo "   ✅ {$total} alertas analisados\n";
        } else {
            $this->analysis['alerts']['total'] = 0;
            $this->issues[] = "⚠️  Tabela 'alertas' não encontrada";
            echo "   ⚠️  Tabela de alertas não encontrada\n";
        }
    }
    
    private function analyzeGeofences(): void
    {
        echo "📊 Analisando cercas virtuais...\n";
        
        // Verificar se tabela de cercas existe
        $stmt = $this->connection->query("SHOW TABLES LIKE 'cercas'");
        if ($stmt->fetch()) {
            $stmt = $this->connection->query("SELECT COUNT(*) as total FROM cercas");
            $total = $stmt->fetch()['total'];
            $this->analysis['geofences']['total'] = $total;
            
            // Cercas ativas/inativas
            $stmt = $this->connection->query("
                SELECT 
                    SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN ativo = 0 THEN 1 ELSE 0 END) as inactive
                FROM cercas
            ");
            $status = $stmt->fetch();
            $this->analysis['geofences']['active'] = $status['active'] ?? 0;
            $this->analysis['geofences']['inactive'] = $status['inactive'] ?? 0;
            
            echo "   ✅ {$total} cercas virtuais analisadas\n";
        } else {
            $this->analysis['geofences']['total'] = 0;
            $this->issues[] = "⚠️  Tabela 'cercas' não encontrada";
            echo "   ⚠️  Tabela de cercas não encontrada\n";
        }
    }
    
    private function analyzeCommands(): void
    {
        echo "📊 Analisando comandos...\n";
        
        // Verificar se tabela de comandos existe
        $stmt = $this->connection->query("SHOW TABLES LIKE 'comandos'");
        if ($stmt->fetch()) {
            $stmt = $this->connection->query("SELECT COUNT(*) as total FROM comandos");
            $total = $stmt->fetch()['total'];
            $this->analysis['commands']['total'] = $total;
            
            // Comandos por status
            $stmt = $this->connection->query("
                SELECT status, COUNT(*) as count 
                FROM comandos 
                GROUP BY status
            ");
            $statuses = $stmt->fetchAll();
            $this->analysis['commands']['by_status'] = $statuses;
            
            echo "   ✅ {$total} comandos analisados\n";
        } else {
            $this->analysis['commands']['total'] = 0;
            $this->issues[] = "⚠️  Tabela 'comandos' não encontrada";
            echo "   ⚠️  Tabela de comandos não encontrada\n";
        }
    }
    
    private function analyzeDatabaseIntegrity(): void
    {
        echo "📊 Verificando integridade do banco...\n";
        
        // Verificar referências órfãs de veículos
        $stmt = $this->connection->query("
            SELECT COUNT(*) as orphan_vehicles
            FROM veiculos v
            LEFT JOIN usuarios u ON v.id_usuario = u.id
            WHERE u.id IS NULL
        ");
        $orphan_vehicles = $stmt->fetch()['orphan_vehicles'];
        if ($orphan_vehicles > 0) {
            $this->issues[] = "⚠️  {$orphan_vehicles} veículos órfãos (sem usuário válido)";
        }
        
        // Verificar referências órfãs de posições
        $stmt = $this->connection->query("
            SELECT COUNT(*) as orphan_positions
            FROM posicoes p
            LEFT JOIN veiculos v ON p.id_veiculo = v.id
            WHERE v.id IS NULL
            LIMIT 1000
        ");
        $orphan_positions = $stmt->fetch()['orphan_positions'];
        if ($orphan_positions > 0) {
            $this->issues[] = "⚠️  {$orphan_positions}+ posições órfãs (sem veículo válido)";
        }
        
        echo "   ✅ Integridade verificada\n";
    }
    
    private function generateReport(): void
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📋 RELATÓRIO DE ANÁLISE DOS DADOS LEGACY\n";
        echo str_repeat("=", 60) . "\n\n";
        
        // Resumo geral
        echo "📊 RESUMO GERAL:\n";
        echo "   👥 Usuários: " . ($this->analysis['users']['total'] ?? 0) . 
             " ({$this->analysis['users']['active']} ativos, {$this->analysis['users']['inactive']} inativos)\n";
        echo "   🚗 Veículos: " . ($this->analysis['vehicles']['total'] ?? 0) . 
             " (média de {$this->analysis['vehicles']['avg_per_user']} por usuário)\n";
        echo "   📍 Posições: " . ($this->analysis['positions']['total'] ?? 0) . 
             " ({$this->analysis['positions']['last_30_days']} nos últimos 30 dias)\n";
        echo "   🚨 Alertas: " . ($this->analysis['alerts']['total'] ?? 0) . "\n";
        echo "   🔒 Cercas: " . ($this->analysis['geofences']['total'] ?? 0) . "\n";
        echo "   📡 Comandos: " . ($this->analysis['commands']['total'] ?? 0) . "\n\n";
        
        // Issues encontrados
        if (!empty($this->issues)) {
            echo "🚨 PROBLEMAS ENCONTRADOS:\n";
            foreach ($this->issues as $issue) {
                echo "   {$issue}\n";
            }
            echo "\n";
        }
        
        // Recomendações
        echo "💡 RECOMENDAÇÕES:\n";
        
        if (count($this->issues) > 0) {
            echo "   🔧 Execute o script de limpeza antes da migração\n";
            echo "   📝 Resolva os problemas identificados acima\n";
        }
        
        if (($this->analysis['positions']['older_than_year'] ?? 0) > 0) {
            echo "   🗄️  Considere arquivar posições antigas (> 1 ano)\n";
        }
        
        if (($this->analysis['positions']['total'] ?? 0) > 1000000) {
            echo "   ⚡ Migração de posições será feita em lotes (pode demorar)\n";
        }
        
        echo "   ✅ Sistema pronto para migração após resolver os problemas\n\n";
        
        // Salvar relatório em arquivo
        $report_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'analysis' => $this->analysis,
            'issues' => $this->issues
        ];
        
        $report_file = __DIR__ . '/../../logs/legacy-analysis-' . date('Y-m-d-H-i-s') . '.json';
        file_put_contents($report_file, json_encode($report_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        echo "💾 Relatório salvo em: {$report_file}\n";
        echo "🎯 Execute: php clean-legacy-data.php para resolver problemas automaticamente\n\n";
    }
}

// Executar análise
try {
    $analyzer = new LegacyDataAnalyzer();
    $analyzer->analyze();
} catch (Exception $e) {
    echo "❌ Erro durante análise: " . $e->getMessage() . "\n";
    echo "🔍 Verifique se o banco de dados está configurado corretamente\n";
    exit(1);
}