-- =====================================================
-- FastrackGPS - Schema do Sistema Moderno
-- Versão: 2.0.0
-- Arquivo: schema-modern.sql
-- =====================================================

-- Configurações da sessão
SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

-- =====================================================
-- TABELA: modern_users
-- Descrição: Usuários do sistema moderno
-- =====================================================

DROP TABLE IF EXISTS `modern_users`;
CREATE TABLE `modern_users` (
  `id` VARCHAR(36) NOT NULL COMMENT 'UUID do usuário',
  `legacy_id` INT(11) NULL DEFAULT NULL COMMENT 'ID do sistema legacy para migração',
  `name` VARCHAR(255) NOT NULL COMMENT 'Nome completo do usuário',
  `email` VARCHAR(255) NOT NULL COMMENT 'Email único do usuário',
  `password_hash` VARCHAR(255) NOT NULL COMMENT 'Hash da senha (bcrypt/argon2)',
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Status ativo/inativo',
  `is_admin` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Permissão de administrador',
  `phone` VARCHAR(20) NULL COMMENT 'Telefone de contato',
  `company` VARCHAR(255) NULL COMMENT 'Empresa do usuário',
  `language` VARCHAR(5) NOT NULL DEFAULT 'pt_BR' COMMENT 'Idioma preferido',
  `timezone` VARCHAR(50) NOT NULL DEFAULT 'America/Sao_Paulo' COMMENT 'Fuso horário',
  `last_login_at` TIMESTAMP NULL COMMENT 'Data do último login',
  `email_verified_at` TIMESTAMP NULL COMMENT 'Data de verificação do email',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data da última atualização',
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_modern_users_email` (`email`),
  KEY `idx_modern_users_legacy_id` (`legacy_id`),
  KEY `idx_modern_users_active` (`is_active`),
  KEY `idx_modern_users_admin` (`is_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Usuários do sistema moderno';

-- =====================================================
-- TABELA: modern_vehicles
-- Descrição: Veículos/equipamentos rastreados
-- =====================================================

DROP TABLE IF EXISTS `modern_vehicles`;
CREATE TABLE `modern_vehicles` (
  `id` VARCHAR(36) NOT NULL COMMENT 'UUID do veículo',
  `legacy_id` INT(11) NULL DEFAULT NULL COMMENT 'ID do sistema legacy para migração',
  `user_id` VARCHAR(36) NOT NULL COMMENT 'UUID do usuário proprietário',
  `name` VARCHAR(255) NOT NULL COMMENT 'Nome/apelido do veículo',
  `imei` VARCHAR(20) NOT NULL COMMENT 'IMEI único do dispositivo GPS',
  `plate` VARCHAR(20) NULL COMMENT 'Placa do veículo',
  `model` VARCHAR(100) NULL COMMENT 'Modelo do veículo',
  `brand` VARCHAR(100) NULL COMMENT 'Marca do veículo',
  `year` YEAR NULL COMMENT 'Ano do veículo',
  `color` VARCHAR(50) NULL COMMENT 'Cor do veículo',
  `category` ENUM('car', 'truck', 'motorcycle', 'bicycle', 'boat', 'equipment', 'person', 'other') NOT NULL DEFAULT 'car' COMMENT 'Categoria do veículo',
  `device_type` VARCHAR(50) NULL COMMENT 'Tipo do dispositivo GPS',
  `icon_id` INT(11) NOT NULL DEFAULT 1 COMMENT 'ID do ícone no mapa',
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Status ativo/inativo',
  `odometer` INT(11) NOT NULL DEFAULT 0 COMMENT 'Odômetro em metros',
  `fuel_capacity` DECIMAL(8,2) NULL COMMENT 'Capacidade do tanque em litros',
  `notes` TEXT NULL COMMENT 'Observações sobre o veículo',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data da última atualização',
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_modern_vehicles_imei` (`imei`),
  KEY `fk_modern_vehicles_user` (`user_id`),
  KEY `idx_modern_vehicles_legacy_id` (`legacy_id`),
  KEY `idx_modern_vehicles_active` (`is_active`),
  KEY `idx_modern_vehicles_category` (`category`),
  
  CONSTRAINT `fk_modern_vehicles_user` FOREIGN KEY (`user_id`) REFERENCES `modern_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Veículos/equipamentos rastreados';

-- =====================================================
-- TABELA: modern_positions
-- Descrição: Posições GPS dos veículos
-- =====================================================

DROP TABLE IF EXISTS `modern_positions`;
CREATE TABLE `modern_positions` (
  `id` VARCHAR(36) NOT NULL COMMENT 'UUID da posição',
  `legacy_id` INT(11) NULL DEFAULT NULL COMMENT 'ID do sistema legacy para migração',
  `vehicle_id` VARCHAR(36) NOT NULL COMMENT 'UUID do veículo',
  `latitude` DECIMAL(10,8) NOT NULL COMMENT 'Latitude em graus decimais',
  `longitude` DECIMAL(11,8) NOT NULL COMMENT 'Longitude em graus decimais',
  `altitude` INT(11) NULL COMMENT 'Altitude em metros',
  `speed` DECIMAL(6,2) NOT NULL DEFAULT 0.00 COMMENT 'Velocidade em km/h',
  `course` DECIMAL(5,2) NULL COMMENT 'Direção em graus (0-360)',
  `satellites` TINYINT(3) NULL COMMENT 'Número de satélites GPS',
  `hdop` DECIMAL(4,2) NULL COMMENT 'Diluição horizontal da precisão',
  `battery_level` TINYINT(3) NULL COMMENT 'Nível da bateria (0-100%)',
  `gsm_signal` TINYINT(3) NULL COMMENT 'Força do sinal GSM (0-100%)',
  `ignition` BOOLEAN NULL COMMENT 'Status da ignição',
  `motion` BOOLEAN NULL COMMENT 'Status de movimento',
  `address` TEXT NULL COMMENT 'Endereço geocodificado',
  `attributes` JSON NULL COMMENT 'Atributos extras do dispositivo',
  `recorded_at` TIMESTAMP NOT NULL COMMENT 'Data/hora da posição (GPS)',
  `received_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data/hora do recebimento',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de inserção no banco',
  
  PRIMARY KEY (`id`),
  KEY `fk_modern_positions_vehicle` (`vehicle_id`),
  KEY `idx_modern_positions_recorded` (`recorded_at`),
  KEY `idx_modern_positions_vehicle_recorded` (`vehicle_id`, `recorded_at`),
  KEY `idx_modern_positions_coordinates` (`latitude`, `longitude`),
  KEY `idx_modern_positions_legacy_id` (`legacy_id`),
  
  CONSTRAINT `fk_modern_positions_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `modern_vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Posições GPS dos veículos'
PARTITION BY RANGE (YEAR(recorded_at)) (
  PARTITION p2023 VALUES LESS THAN (2024),
  PARTITION p2024 VALUES LESS THAN (2025),
  PARTITION p2025 VALUES LESS THAN (2026),
  PARTITION p_future VALUES LESS THAN MAXVALUE
);

-- =====================================================
-- TABELA: modern_geofences
-- Descrição: Cercas virtuais (geofences)
-- =====================================================

DROP TABLE IF EXISTS `modern_geofences`;
CREATE TABLE `modern_geofences` (
  `id` VARCHAR(36) NOT NULL COMMENT 'UUID da cerca',
  `legacy_id` INT(11) NULL DEFAULT NULL COMMENT 'ID do sistema legacy para migração',
  `user_id` VARCHAR(36) NOT NULL COMMENT 'UUID do usuário proprietário',
  `name` VARCHAR(255) NOT NULL COMMENT 'Nome da cerca virtual',
  `description` TEXT NULL COMMENT 'Descrição da cerca',
  `type` ENUM('circle', 'polygon', 'route') NOT NULL DEFAULT 'circle' COMMENT 'Tipo da cerca',
  `coordinates` JSON NOT NULL COMMENT 'Coordenadas da cerca (formato GeoJSON)',
  `radius` INT(11) NULL COMMENT 'Raio em metros (para cercas circulares)',
  `color` VARCHAR(7) NOT NULL DEFAULT '#FF0000' COMMENT 'Cor da cerca no mapa (hex)',
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Status ativo/inativo',
  `alert_enter` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Gerar alerta ao entrar',
  `alert_exit` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Gerar alerta ao sair',
  `schedule` JSON NULL COMMENT 'Horários de funcionamento',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data da última atualização',
  
  PRIMARY KEY (`id`),
  KEY `fk_modern_geofences_user` (`user_id`),
  KEY `idx_modern_geofences_legacy_id` (`legacy_id`),
  KEY `idx_modern_geofences_active` (`is_active`),
  KEY `idx_modern_geofences_type` (`type`),
  
  CONSTRAINT `fk_modern_geofences_user` FOREIGN KEY (`user_id`) REFERENCES `modern_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cercas virtuais (geofences)';

-- =====================================================
-- TABELA: modern_geofence_vehicles
-- Descrição: Associação entre cercas e veículos
-- =====================================================

DROP TABLE IF EXISTS `modern_geofence_vehicles`;
CREATE TABLE `modern_geofence_vehicles` (
  `id` VARCHAR(36) NOT NULL COMMENT 'UUID da associação',
  `geofence_id` VARCHAR(36) NOT NULL COMMENT 'UUID da cerca',
  `vehicle_id` VARCHAR(36) NOT NULL COMMENT 'UUID do veículo',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_geofence_vehicle` (`geofence_id`, `vehicle_id`),
  KEY `fk_modern_geofence_vehicles_geofence` (`geofence_id`),
  KEY `fk_modern_geofence_vehicles_vehicle` (`vehicle_id`),
  
  CONSTRAINT `fk_modern_geofence_vehicles_geofence` FOREIGN KEY (`geofence_id`) REFERENCES `modern_geofences` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_modern_geofence_vehicles_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `modern_vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Associação entre cercas e veículos';

-- =====================================================
-- TABELA: modern_alerts
-- Descrição: Sistema de alertas
-- =====================================================

DROP TABLE IF EXISTS `modern_alerts`;
CREATE TABLE `modern_alerts` (
  `id` VARCHAR(36) NOT NULL COMMENT 'UUID do alerta',
  `legacy_id` INT(11) NULL DEFAULT NULL COMMENT 'ID do sistema legacy para migração',
  `vehicle_id` VARCHAR(36) NOT NULL COMMENT 'UUID do veículo',
  `geofence_id` VARCHAR(36) NULL COMMENT 'UUID da cerca (se aplicável)',
  `position_id` VARCHAR(36) NULL COMMENT 'UUID da posição que gerou o alerta',
  `type` VARCHAR(50) NOT NULL COMMENT 'Tipo do alerta',
  `severity` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium' COMMENT 'Severidade do alerta',
  `title` VARCHAR(255) NOT NULL COMMENT 'Título do alerta',
  `message` TEXT NOT NULL COMMENT 'Mensagem detalhada do alerta',
  `data` JSON NULL COMMENT 'Dados adicais do alerta',
  `is_read` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Status de leitura',
  `acknowledged_by` VARCHAR(36) NULL COMMENT 'UUID do usuário que reconheceu',
  `acknowledged_at` TIMESTAMP NULL COMMENT 'Data do reconhecimento',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  
  PRIMARY KEY (`id`),
  KEY `fk_modern_alerts_vehicle` (`vehicle_id`),
  KEY `fk_modern_alerts_geofence` (`geofence_id`),
  KEY `fk_modern_alerts_position` (`position_id`),
  KEY `fk_modern_alerts_acknowledged_by` (`acknowledged_by`),
  KEY `idx_modern_alerts_type` (`type`),
  KEY `idx_modern_alerts_severity` (`severity`),
  KEY `idx_modern_alerts_read` (`is_read`),
  KEY `idx_modern_alerts_created` (`created_at`),
  KEY `idx_modern_alerts_legacy_id` (`legacy_id`),
  
  CONSTRAINT `fk_modern_alerts_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `modern_vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_modern_alerts_geofence` FOREIGN KEY (`geofence_id`) REFERENCES `modern_geofences` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_modern_alerts_position` FOREIGN KEY (`position_id`) REFERENCES `modern_positions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_modern_alerts_acknowledged_by` FOREIGN KEY (`acknowledged_by`) REFERENCES `modern_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sistema de alertas';

-- =====================================================
-- TABELA: modern_commands
-- Descrição: Comandos enviados aos dispositivos GPS
-- =====================================================

DROP TABLE IF EXISTS `modern_commands`;
CREATE TABLE `modern_commands` (
  `id` VARCHAR(36) NOT NULL COMMENT 'UUID do comando',
  `legacy_id` INT(11) NULL DEFAULT NULL COMMENT 'ID do sistema legacy para migração',
  `vehicle_id` VARCHAR(36) NOT NULL COMMENT 'UUID do veículo',
  `user_id` VARCHAR(36) NOT NULL COMMENT 'UUID do usuário que enviou',
  `command_type` VARCHAR(50) NOT NULL COMMENT 'Tipo do comando',
  `command_text` TEXT NOT NULL COMMENT 'Texto do comando enviado',
  `command_data` JSON NULL COMMENT 'Parâmetros do comando',
  `status` ENUM('pending', 'sent', 'acknowledged', 'failed', 'timeout') NOT NULL DEFAULT 'pending' COMMENT 'Status do comando',
  `priority` ENUM('low', 'normal', 'high', 'urgent') NOT NULL DEFAULT 'normal' COMMENT 'Prioridade do comando',
  `response` TEXT NULL COMMENT 'Resposta do dispositivo',
  `error_message` TEXT NULL COMMENT 'Mensagem de erro (se houver)',
  `attempts` TINYINT(3) NOT NULL DEFAULT 0 COMMENT 'Tentativas de envio',
  `max_attempts` TINYINT(3) NOT NULL DEFAULT 3 COMMENT 'Máximo de tentativas',
  `timeout_seconds` INT(11) NOT NULL DEFAULT 300 COMMENT 'Timeout em segundos',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `sent_at` TIMESTAMP NULL COMMENT 'Data de envio',
  `acknowledged_at` TIMESTAMP NULL COMMENT 'Data da confirmação',
  `expires_at` TIMESTAMP NULL COMMENT 'Data de expiração',
  
  PRIMARY KEY (`id`),
  KEY `fk_modern_commands_vehicle` (`vehicle_id`),
  KEY `fk_modern_commands_user` (`user_id`),
  KEY `idx_modern_commands_type` (`command_type`),
  KEY `idx_modern_commands_status` (`status`),
  KEY `idx_modern_commands_priority` (`priority`),
  KEY `idx_modern_commands_created` (`created_at`),
  KEY `idx_modern_commands_legacy_id` (`legacy_id`),
  
  CONSTRAINT `fk_modern_commands_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `modern_vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_modern_commands_user` FOREIGN KEY (`user_id`) REFERENCES `modern_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Comandos enviados aos dispositivos GPS';

-- =====================================================
-- TABELA: modern_reports
-- Descrição: Relatórios gerados pelo sistema
-- =====================================================

DROP TABLE IF EXISTS `modern_reports`;
CREATE TABLE `modern_reports` (
  `id` VARCHAR(36) NOT NULL COMMENT 'UUID do relatório',
  `user_id` VARCHAR(36) NOT NULL COMMENT 'UUID do usuário que gerou',
  `name` VARCHAR(255) NOT NULL COMMENT 'Nome do relatório',
  `type` VARCHAR(50) NOT NULL COMMENT 'Tipo do relatório',
  `parameters` JSON NOT NULL COMMENT 'Parâmetros do relatório',
  `format` ENUM('pdf', 'excel', 'csv', 'json') NOT NULL DEFAULT 'pdf' COMMENT 'Formato de saída',
  `status` ENUM('pending', 'processing', 'completed', 'failed') NOT NULL DEFAULT 'pending' COMMENT 'Status do relatório',
  `file_path` VARCHAR(500) NULL COMMENT 'Caminho do arquivo gerado',
  `file_size` BIGINT NULL COMMENT 'Tamanho do arquivo em bytes',
  `download_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Número de downloads',
  `expires_at` TIMESTAMP NULL COMMENT 'Data de expiração',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  `completed_at` TIMESTAMP NULL COMMENT 'Data de conclusão',
  
  PRIMARY KEY (`id`),
  KEY `fk_modern_reports_user` (`user_id`),
  KEY `idx_modern_reports_type` (`type`),
  KEY `idx_modern_reports_status` (`status`),
  KEY `idx_modern_reports_created` (`created_at`),
  
  CONSTRAINT `fk_modern_reports_user` FOREIGN KEY (`user_id`) REFERENCES `modern_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relatórios gerados pelo sistema';

-- =====================================================
-- TABELA: modern_audit_logs
-- Descrição: Logs de auditoria do sistema
-- =====================================================

DROP TABLE IF EXISTS `modern_audit_logs`;
CREATE TABLE `modern_audit_logs` (
  `id` VARCHAR(36) NOT NULL COMMENT 'UUID do log',
  `user_id` VARCHAR(36) NULL COMMENT 'UUID do usuário (se aplicável)',
  `entity_type` VARCHAR(50) NOT NULL COMMENT 'Tipo da entidade afetada',
  `entity_id` VARCHAR(36) NOT NULL COMMENT 'UUID da entidade afetada',
  `action` VARCHAR(50) NOT NULL COMMENT 'Ação realizada',
  `old_values` JSON NULL COMMENT 'Valores anteriores',
  `new_values` JSON NULL COMMENT 'Novos valores',
  `ip_address` VARCHAR(45) NULL COMMENT 'Endereço IP',
  `user_agent` TEXT NULL COMMENT 'User Agent do navegador',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data da ação',
  
  PRIMARY KEY (`id`),
  KEY `fk_modern_audit_logs_user` (`user_id`),
  KEY `idx_modern_audit_logs_entity` (`entity_type`, `entity_id`),
  KEY `idx_modern_audit_logs_action` (`action`),
  KEY `idx_modern_audit_logs_created` (`created_at`),
  
  CONSTRAINT `fk_modern_audit_logs_user` FOREIGN KEY (`user_id`) REFERENCES `modern_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Logs de auditoria do sistema'
PARTITION BY RANGE (YEAR(created_at)) (
  PARTITION p2023 VALUES LESS THAN (2024),
  PARTITION p2024 VALUES LESS THAN (2025),
  PARTITION p2025 VALUES LESS THAN (2026),
  PARTITION p_future VALUES LESS THAN MAXVALUE
);

-- =====================================================
-- VIEWS E PROCEDURES
-- =====================================================

-- View para últimas posições dos veículos
DROP VIEW IF EXISTS `view_latest_positions`;
CREATE VIEW `view_latest_positions` AS
SELECT 
    v.id AS vehicle_id,
    v.name AS vehicle_name,
    v.plate AS vehicle_plate,
    v.imei AS vehicle_imei,
    u.name AS owner_name,
    p.id AS position_id,
    p.latitude,
    p.longitude,
    p.altitude,
    p.speed,
    p.course,
    p.address,
    p.recorded_at,
    CASE 
        WHEN p.recorded_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE) THEN 'online'
        WHEN p.recorded_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 'idle'
        ELSE 'offline'
    END AS status
FROM modern_vehicles v
LEFT JOIN modern_users u ON v.user_id = u.id
LEFT JOIN modern_positions p ON v.id = p.vehicle_id
WHERE p.id IS NULL OR p.id = (
    SELECT p2.id 
    FROM modern_positions p2 
    WHERE p2.vehicle_id = v.id 
    ORDER BY p2.recorded_at DESC 
    LIMIT 1
)
AND v.is_active = TRUE;

-- View para estatísticas do usuário
DROP VIEW IF EXISTS `view_user_statistics`;
CREATE VIEW `view_user_statistics` AS
SELECT 
    u.id AS user_id,
    u.name AS user_name,
    COUNT(DISTINCT v.id) AS total_vehicles,
    COUNT(DISTINCT CASE WHEN v.is_active THEN v.id END) AS active_vehicles,
    COUNT(DISTINCT g.id) AS total_geofences,
    COUNT(DISTINCT CASE WHEN a.is_read = FALSE THEN a.id END) AS unread_alerts,
    MAX(p.recorded_at) AS last_position_time
FROM modern_users u
LEFT JOIN modern_vehicles v ON u.id = v.user_id
LEFT JOIN modern_geofences g ON u.id = g.user_id
LEFT JOIN modern_alerts a ON v.id = a.vehicle_id
LEFT JOIN modern_positions p ON v.id = p.vehicle_id
WHERE u.is_active = TRUE
GROUP BY u.id, u.name;

-- =====================================================
-- ÍNDICES ADICIONAIS PARA PERFORMANCE
-- =====================================================

-- Índices compostos para consultas frequentes
CREATE INDEX idx_positions_vehicle_time_speed ON modern_positions (vehicle_id, recorded_at, speed);
CREATE INDEX idx_alerts_vehicle_unread ON modern_alerts (vehicle_id, is_read, created_at);
CREATE INDEX idx_commands_vehicle_status ON modern_commands (vehicle_id, status, created_at);

-- =====================================================
-- TRIGGERS PARA AUDITORIA
-- =====================================================

-- Trigger para auditoria de usuários
DELIMITER $$
CREATE TRIGGER tr_modern_users_audit_update
AFTER UPDATE ON modern_users
FOR EACH ROW
BEGIN
    INSERT INTO modern_audit_logs (id, user_id, entity_type, entity_id, action, old_values, new_values)
    VALUES (
        UUID(),
        NEW.id,
        'user',
        NEW.id,
        'update',
        JSON_OBJECT(
            'name', OLD.name,
            'email', OLD.email,
            'is_active', OLD.is_active,
            'is_admin', OLD.is_admin
        ),
        JSON_OBJECT(
            'name', NEW.name,
            'email', NEW.email,
            'is_active', NEW.is_active,
            'is_admin', NEW.is_admin
        )
    );
END$$

-- Trigger para auditoria de veículos
CREATE TRIGGER tr_modern_vehicles_audit_update
AFTER UPDATE ON modern_vehicles
FOR EACH ROW
BEGIN
    INSERT INTO modern_audit_logs (id, user_id, entity_type, entity_id, action, old_values, new_values)
    VALUES (
        UUID(),
        NEW.user_id,
        'vehicle',
        NEW.id,
        'update',
        JSON_OBJECT(
            'name', OLD.name,
            'imei', OLD.imei,
            'is_active', OLD.is_active
        ),
        JSON_OBJECT(
            'name', NEW.name,
            'imei', NEW.imei,
            'is_active', NEW.is_active
        )
    );
END$$

DELIMITER ;

-- =====================================================
-- CONFIGURAÇÕES FINAIS
-- =====================================================

-- Reabilitar verificações de chave estrangeira
SET foreign_key_checks = 1;

-- Inserir dados iniciais (usuário admin)
INSERT IGNORE INTO modern_users (id, name, email, password_hash, is_admin) VALUES 
(UUID(), 'Administrador', 'admin@fastrackgps.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);

-- Comentários finais
-- Este schema foi projetado para alta performance e escalabilidade
-- Utiliza UUIDs para identificadores únicos
-- Implementa particionamento para tabelas com grande volume
-- Inclui sistema completo de auditoria
-- Suporta migração do sistema legacy através do campo legacy_id