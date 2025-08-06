<?php
/**
 * Script de Validação de Migração
 * 
 * Este script valida a integridade dos dados migrados,
 * comparando contagens e verificando consistência entre
 * o sistema legacy e o sistema moderno.
 * 
 * @package FastrackGPS
 * @subpackage Migration
 * @author FastrackGPS Team
 * @version 2.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class MigrationValidator
{
    private PDO $legacyDb;
    private PDO $modernDb;
    private array $validation = [];
    private array $issues = [];
    
    public function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';
        
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
        $this->legacyDb = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        // Para este exemplo, usando mesmo banco (em produção seria banco separado)
        $this->modernDb = clone $this->legacyDb;
    }
    
    public function validate(): void
    {
        echo "🔍 Iniciando validação da migração...\n\n";
        
        $this->validateTableStructure();
        $this->validateDataCounts();
        $this->validateDataIntegrity();
        $this->validateRelationships();
        $this->validateDataQuality();
        
        $this->generateReport();
    }
    
    private function validateTableStructure(): void
    {
        echo "🏗️  Validando estrutura das tabelas...\n";
        
        $expected_tables = [
            'modern_users',
            'modern_vehicles', 
            'modern_positions',
            'modern_geofences',
            'modern_alerts',
            'modern_commands'
        ];
        
        foreach ($expected_tables as $table) {
            $stmt = $this->modernDb->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->fetch()) {
                echo "   ✅ Tabela {$table} existe\n";
                $this->validation['tables'][$table] = true;
                
                // Verificar se tem dados
                $count_stmt = $this->modernDb->query("SELECT COUNT(*) as count FROM {$table}");
                $count = $count_stmt->fetch()['count'];
                $this->validation['table_counts'][$table] = $count;
                
                if ($count > 0) {
                    echo "      📊 {$count} registros\n";
                } else {
                    echo "      ⚠️  Tabela vazia\n";
                }
                
            } else {
                echo "   ❌ Tabela {$table} NÃO existe\n";
                $this->validation['tables'][$table] = false;
                $this->issues[] = "Tabela {$table} não foi criada";
            }
        }
        
        echo "   ✅ Validação de estrutura concluída\n\n";
    }
    
    private function validateDataCounts(): void
    {
        echo "📊 Validando contagens de dados...\n";
        
        $comparisons = [
            'users' => [
                'legacy' => "SELECT COUNT(*) as count FROM usuarios WHERE email IS NOT NULL AND email != ''",
                'modern' => "SELECT COUNT(*) as count FROM modern_users"
            ],
            'vehicles' => [
                'legacy' => "SELECT COUNT(*) as count FROM veiculos WHERE imei IS NOT NULL AND imei != ''",
                'modern' => "SELECT COUNT(*) as count FROM modern_vehicles"
            ],
            'positions' => [
                'legacy' => "SELECT COUNT(*) as count FROM posicoes WHERE latitude != 0 AND longitude != 0",
                'modern' => "SELECT COUNT(*) as count FROM modern_positions"
            ]
        ];
        
        // Adicionar tabelas opcionais se existirem
        $optional_tables = ['cercas', 'alertas', 'comandos'];
        foreach ($optional_tables as $table) {
            $stmt = $this->legacyDb->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->fetch()) {
                $modern_table = 'modern_' . ($table === 'cercas' ? 'geofences' : 
                                           ($table === 'alertas' ? 'alerts' : 'commands'));
                $comparisons[$table] = [
                    'legacy' => "SELECT COUNT(*) as count FROM {$table}",
                    'modern' => "SELECT COUNT(*) as count FROM {$modern_table}"
                ];
            }
        }
        
        foreach ($comparisons as $entity => $queries) {
            $legacy_count = $this->legacyDb->query($queries['legacy'])->fetch()['count'];
            $modern_count = $this->modernDb->query($queries['modern'])->fetch()['count'];
            
            $this->validation['counts'][$entity] = [
                'legacy' => $legacy_count,
                'modern' => $modern_count,
                'match' => $legacy_count == $modern_count
            ];
            
            $status = $legacy_count == $modern_count ? '✅' : '⚠️';
            echo "   {$status} {$entity}: Legacy({$legacy_count}) -> Moderno({$modern_count})\n";
            
            if ($legacy_count != $modern_count) {
                $diff = $legacy_count - $modern_count;
                if ($diff > 0) {
                    $this->issues[] = "{$entity}: {$diff} registros não foram migrados";
                } else {
                    $this->issues[] = "{$entity}: " . abs($diff) . " registros extras no sistema moderno";
                }
            }
        }
        
        echo "   ✅ Validação de contagens concluída\n\n";
    }
    
    private function validateDataIntegrity(): void
    {
        echo "🔐 Validando integridade dos dados...\n";
        
        // Validar UUIDs únicos
        $tables_with_uuid = ['modern_users', 'modern_vehicles', 'modern_positions', 'modern_geofences', 'modern_alerts', 'modern_commands'];
        
        foreach ($tables_with_uuid as $table) {
            $stmt = $this->modernDb->query("SHOW TABLES LIKE '{$table}'");
            if (!$stmt->fetch()) continue;
            
            // Verificar UUIDs únicos
            $stmt = $this->modernDb->query("
                SELECT COUNT(*) as total, COUNT(DISTINCT id) as unique_ids 
                FROM {$table}
            ");
            $result = $stmt->fetch();
            
            if ($result['total'] != $result['unique_ids']) {
                $duplicates = $result['total'] - $result['unique_ids'];
                echo "   ❌ {$table}: {$duplicates} UUIDs duplicados\n";
                $this->issues[] = "{$table} tem UUIDs duplicados";
            } else {
                echo "   ✅ {$table}: Todos UUIDs são únicos\n";
            }
            
            // Verificar formato UUID
            $stmt = $this->modernDb->query("
                SELECT COUNT(*) as invalid_uuids 
                FROM {$table} 
                WHERE id NOT REGEXP '^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$'
            ");
            $invalid = $stmt->fetch()['invalid_uuids'];
            
            if ($invalid > 0) {
                echo "   ❌ {$table}: {$invalid} UUIDs com formato inválido\n";
                $this->issues[] = "{$table} tem UUIDs com formato inválido";
            }
        }
        
        // Validar mapeamento de IDs legacy
        $tables_with_legacy_id = ['modern_users', 'modern_vehicles', 'modern_positions', 'modern_geofences', 'modern_alerts', 'modern_commands'];
        
        foreach ($tables_with_legacy_id as $table) {
            $stmt = $this->modernDb->query("SHOW TABLES LIKE '{$table}'");
            if (!$stmt->fetch()) continue;
            
            $stmt = $this->modernDb->query("
                SELECT COUNT(*) as without_legacy_id 
                FROM {$table} 
                WHERE legacy_id IS NULL
            ");
            $without_legacy = $stmt->fetch()['without_legacy_id'];
            
            if ($without_legacy > 0) {
                echo "   ⚠️  {$table}: {$without_legacy} registros sem legacy_id\n";
                // Isso pode ser normal para dados criados após a migração
            }
        }
        
        echo "   ✅ Validação de integridade concluída\n\n";
    }
    
    private function validateRelationships(): void
    {
        echo "🔗 Validando relacionamentos...\n";
        
        // Verificar se todos os veículos têm usuário válido
        $stmt = $this->modernDb->query("
            SELECT COUNT(*) as orphan_vehicles
            FROM modern_vehicles v
            LEFT JOIN modern_users u ON v.user_id = u.id
            WHERE u.id IS NULL
        ");
        $orphan_vehicles = $stmt->fetch()['orphan_vehicles'];
        
        if ($orphan_vehicles > 0) {
            echo "   ❌ {$orphan_vehicles} veículos órfãos (sem usuário)\n";
            $this->issues[] = "Existem veículos sem usuário válido";
        } else {
            echo "   ✅ Todos os veículos têm usuário válido\n";
        }
        
        // Verificar se todas as posições têm veículo válido
        $stmt = $this->modernDb->query("
            SELECT COUNT(*) as orphan_positions
            FROM modern_positions p
            LEFT JOIN modern_vehicles v ON p.vehicle_id = v.id
            WHERE v.id IS NULL
            LIMIT 1000
        ");
        $orphan_positions = $stmt->fetch()['orphan_positions'];
        
        if ($orphan_positions > 0) {
            echo "   ❌ {$orphan_positions}+ posições órfãs (sem veículo)\n";
            $this->issues[] = "Existem posições sem veículo válido";
        } else {
            echo "   ✅ Todas as posições têm veículo válido\n";
        }
        
        // Verificar se todos os alertas têm veículo válido
        $stmt = $this->modernDb->query("SHOW TABLES LIKE 'modern_alerts'");
        if ($stmt->fetch()) {
            $stmt = $this->modernDb->query("
                SELECT COUNT(*) as orphan_alerts
                FROM modern_alerts a
                LEFT JOIN modern_vehicles v ON a.vehicle_id = v.id
                WHERE v.id IS NULL
            ");
            $orphan_alerts = $stmt->fetch()['orphan_alerts'];
            
            if ($orphan_alerts > 0) {
                echo "   ❌ {$orphan_alerts} alertas órfãos (sem veículo)\n";
                $this->issues[] = "Existem alertas sem veículo válido";
            } else {
                echo "   ✅ Todos os alertas têm veículo válido\n";
            }
        }
        
        // Verificar se todos os comandos têm veículo válido
        $stmt = $this->modernDb->query("SHOW TABLES LIKE 'modern_commands'");
        if ($stmt->fetch()) {
            $stmt = $this->modernDb->query("
                SELECT COUNT(*) as orphan_commands
                FROM modern_commands c
                LEFT JOIN modern_vehicles v ON c.vehicle_id = v.id
                WHERE v.id IS NULL
            ");
            $orphan_commands = $stmt->fetch()['orphan_commands'];
            
            if ($orphan_commands > 0) {
                echo "   ❌ {$orphan_commands} comandos órfãos (sem veículo)\n";
                $this->issues[] = "Existem comandos sem veículo válido";
            } else {
                echo "   ✅ Todos os comandos têm veículo válido\n";
            }
        }
        
        echo "   ✅ Validação de relacionamentos concluída\n\n";
    }
    
    private function validateDataQuality(): void
    {
        echo "✨ Validando qualidade dos dados...\n";
        
        // Verificar emails únicos em usuários
        $stmt = $this->modernDb->query("
            SELECT email, COUNT(*) as count 
            FROM modern_users 
            GROUP BY email 
            HAVING count > 1
        ");
        $duplicate_emails = $stmt->fetchAll();
        
        if (count($duplicate_emails) > 0) {
            echo "   ❌ " . count($duplicate_emails) . " emails duplicados em usuários\n";
            $this->issues[] = "Existem emails duplicados no sistema moderno";
        } else {
            echo "   ✅ Todos os emails são únicos\n";
        }
        
        // Verificar IMEIs únicos em veículos
        $stmt = $this->modernDb->query("
            SELECT imei, COUNT(*) as count 
            FROM modern_vehicles 
            GROUP BY imei 
            HAVING count > 1
        ");
        $duplicate_imeis = $stmt->fetchAll();
        
        if (count($duplicate_imeis) > 0) {
            echo "   ❌ " . count($duplicate_imeis) . " IMEIs duplicados em veículos\n";
            $this->issues[] = "Existem IMEIs duplicados no sistema moderno";
        } else {
            echo "   ✅ Todos os IMEIs são únicos\n";
        }
        
        // Verificar coordenadas válidas em posições
        $stmt = $this->modernDb->query("
            SELECT COUNT(*) as invalid_coords
            FROM modern_positions 
            WHERE latitude = 0 OR longitude = 0 
               OR latitude < -90 OR latitude > 90
               OR longitude < -180 OR longitude > 180
        ");
        $invalid_coords = $stmt->fetch()['invalid_coords'];
        
        if ($invalid_coords > 0) {
            echo "   ❌ {$invalid_coords} posições com coordenadas inválidas\n";
            $this->issues[] = "Existem posições com coordenadas inválidas";
        } else {
            echo "   ✅ Todas as coordenadas são válidas\n";
        }
        
        // Verificar datas futuras
        $stmt = $this->modernDb->query("
            SELECT COUNT(*) as future_dates
            FROM modern_positions 
            WHERE recorded_at > NOW()
        ");
        $future_dates = $stmt->fetch()['future_dates'];
        
        if ($future_dates > 0) {
            echo "   ⚠️  {$future_dates} posições com data futura\n";
            // Isso pode ser aceitável dependendo do fuso horário
        }
        
        echo "   ✅ Validação de qualidade concluída\n\n";
    }
    
    private function generateReport(): void
    {
        echo str_repeat("=", 60) . "\n";
        echo "📋 RELATÓRIO DE VALIDAÇÃO DA MIGRAÇÃO\n";
        echo str_repeat("=", 60) . "\n\n";
        
        // Resumo das tabelas
        echo "🏗️  ESTRUTURA DAS TABELAS:\n";
        foreach ($this->validation['tables'] ?? [] as $table => $exists) {
            $status = $exists ? '✅' : '❌';
            $count = $this->validation['table_counts'][$table] ?? 0;
            echo "   {$status} {$table}: {$count} registros\n";
        }
        echo "\n";
        
        // Resumo das contagens
        echo "📊 COMPARAÇÃO DE CONTAGENS:\n";
        foreach ($this->validation['counts'] ?? [] as $entity => $counts) {
            $status = $counts['match'] ? '✅' : '⚠️';
            echo "   {$status} {$entity}: Legacy({$counts['legacy']}) -> Moderno({$counts['modern']})\n";
        }
        echo "\n";
        
        // Problemas encontrados
        if (!empty($this->issues)) {
            echo "🚨 PROBLEMAS ENCONTRADOS:\n";
            foreach ($this->issues as $issue) {
                echo "   ❌ {$issue}\n";
            }
            echo "\n";
            
            echo "💡 AÇÕES RECOMENDADAS:\n";
            echo "   🔧 Corrigir os problemas identificados acima\n";
            echo "   🔄 Re-executar migração para tabelas com discrepâncias\n";
            echo "   📝 Verificar logs de migração para mais detalhes\n\n";
        } else {
            echo "✅ NENHUM PROBLEMA ENCONTRADO\n";
            echo "🎉 Migração validada com sucesso!\n\n";
        }
        
        // Estatísticas gerais
        $total_modern_records = array_sum($this->validation['table_counts'] ?? []);
        echo "📈 ESTATÍSTICAS GERAIS:\n";
        echo "   📊 Total de registros migrados: {$total_modern_records}\n";
        echo "   🏗️  Tabelas criadas: " . count(array_filter($this->validation['tables'] ?? [])) . "\n";
        echo "   🔗 Relacionamentos validados: " . (count($this->issues) === 0 ? 'OK' : 'COM PROBLEMAS') . "\n";
        echo "   ✨ Qualidade dos dados: " . (count($this->issues) === 0 ? 'EXCELENTE' : 'REQUER ATENÇÃO') . "\n\n";
        
        // Próximos passos
        echo "🎯 PRÓXIMOS PASSOS:\n";
        if (count($this->issues) === 0) {
            echo "   ✅ Sistema moderno pronto para uso\n";
            echo "   🔄 Configurar sincronização contínua se necessário\n";
            echo "   📊 Monitorar performance do sistema moderno\n";
            echo "   🗄️  Considerar arquivamento do sistema legacy\n";
        } else {
            echo "   🔧 Resolver problemas identificados\n";
            echo "   🔄 Re-executar validação após correções\n";
            echo "   📝 Documentar problemas não resolvidos\n";
        }
        echo "\n";
        
        // Salvar relatório
        $report_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'validation' => $this->validation,
            'issues' => $this->issues,
            'total_issues' => count($this->issues),
            'validation_passed' => count($this->issues) === 0
        ];
        
        $report_file = __DIR__ . '/../../logs/validation-report-' . date('Y-m-d-H-i-s') . '.json';
        file_put_contents($report_file, json_encode($report_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        echo "💾 Relatório detalhado salvo em: {$report_file}\n";
        
        if (count($this->issues) === 0) {
            echo "🎉 VALIDAÇÃO CONCLUÍDA COM SUCESSO!\n";
            exit(0);
        } else {
            echo "⚠️  VALIDAÇÃO CONCLUÍDA COM PROBLEMAS\n";
            exit(1);
        }
    }
}

// Executar validação
try {
    $validator = new MigrationValidator();
    $validator->validate();
} catch (Exception $e) {
    echo "❌ Erro durante validação: " . $e->getMessage() . "\n";
    echo "🔍 Verifique se o banco de dados está configurado corretamente\n";
    exit(1);
}