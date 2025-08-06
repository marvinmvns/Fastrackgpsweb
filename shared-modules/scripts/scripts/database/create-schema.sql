-- FastrackGPS Database Schema
-- Modern MySQL 8.0+ Compatible
-- Created: 2025

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = '+00:00';

-- Database charset and collation
ALTER DATABASE fastrackgps CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ==================================================
-- USERS AND AUTHENTICATION
-- ==================================================

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `document` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(2) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `role` enum('admin','user','operator') DEFAULT 'user',
  `is_active` boolean DEFAULT true,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuarios_email_unique` (`email`),
  KEY `idx_usuarios_role` (`role`),
  KEY `idx_usuarios_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- VEHICLES
-- ==================================================

DROP TABLE IF EXISTS `bem`;
CREATE TABLE `bem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `imei` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `identificacao` varchar(50) DEFAULT NULL,
  `cliente` int(11) NOT NULL,
  `numero_chip` varchar(20) DEFAULT NULL,
  `operadora_chip` enum('vivo','claro','tim','oi','other') DEFAULT 'vivo',
  `numero_chip2` varchar(20) DEFAULT NULL,
  `operadora_chip2` enum('vivo','claro','tim','oi','other') DEFAULT NULL,
  `cor_grafico` varchar(7) DEFAULT '#FF0000',
  `activated` enum('S','N') DEFAULT 'S',
  `modo_operacao` enum('normal','eco','performance','custom') DEFAULT 'normal',
  `status_sinal` enum('online','offline','maintenance','blocked') DEFAULT 'offline',
  `last_latitude` decimal(10,8) DEFAULT NULL,
  `last_longitude` decimal(11,8) DEFAULT NULL,
  `last_position_time` timestamp NULL DEFAULT NULL,
  `odometer` decimal(10,2) DEFAULT 0.00,
  `fuel_level` tinyint(3) DEFAULT NULL,
  `battery_level` tinyint(3) DEFAULT NULL,
  `engine_status` boolean DEFAULT false,
  `speed_limit` smallint(5) unsigned DEFAULT 80,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bem_imei_unique` (`imei`),
  KEY `idx_bem_cliente` (`cliente`),
  KEY `idx_bem_status` (`status_sinal`),
  KEY `idx_bem_activated` (`activated`),
  KEY `idx_bem_last_position` (`last_latitude`, `last_longitude`),
  CONSTRAINT `fk_bem_cliente` FOREIGN KEY (`cliente`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- GPS POSITIONS
-- ==================================================

DROP TABLE IF EXISTS `posicao_gps`;
CREATE TABLE `posicao_gps` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `vehicle_id` int(11) NOT NULL,
  `imei` varchar(20) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `altitude` decimal(8,2) DEFAULT NULL,
  `speed` decimal(5,2) DEFAULT 0.00,
  `course` decimal(5,2) DEFAULT NULL,
  `satellites` tinyint(2) DEFAULT NULL,
  `gps_signal_indicator` enum('A','V') DEFAULT 'V',
  `address` text DEFAULT NULL,
  `battery_level` tinyint(3) DEFAULT NULL,
  `gsm_signal` tinyint(3) DEFAULT NULL,
  `extended_info` json DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_posicao_imei` (`imei`),
  KEY `idx_posicao_vehicle` (`vehicle_id`),
  KEY `idx_posicao_timestamp` (`timestamp`),
  KEY `idx_posicao_coordinates` (`latitude`, `longitude`),
  KEY `idx_posicao_vehicle_timestamp` (`vehicle_id`, `timestamp`),
  CONSTRAINT `fk_posicao_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `bem` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Partition by month for better performance
ALTER TABLE `posicao_gps` 
PARTITION BY RANGE (YEAR(timestamp) * 100 + MONTH(timestamp)) (
    PARTITION p202501 VALUES LESS THAN (202502),
    PARTITION p202502 VALUES LESS THAN (202503),
    PARTITION p202503 VALUES LESS THAN (202504),
    PARTITION p202504 VALUES LESS THAN (202505),
    PARTITION p202505 VALUES LESS THAN (202506),
    PARTITION p202506 VALUES LESS THAN (202507),
    PARTITION p202507 VALUES LESS THAN (202508),
    PARTITION p202508 VALUES LESS THAN (202509),
    PARTITION p202509 VALUES LESS THAN (202510),
    PARTITION p202510 VALUES LESS THAN (202511),
    PARTITION p202511 VALUES LESS THAN (202512),
    PARTITION p202512 VALUES LESS THAN (202601),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);

-- ==================================================
-- ALERTS
-- ==================================================

DROP TABLE IF EXISTS `alerta`;
CREATE TABLE `alerta` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `vehicle_id` int(11) NOT NULL,
  `type` enum('speeding','geofence_enter','geofence_exit','panic_button','low_battery','offline','maintenance','custom') NOT NULL,
  `severity` enum('critical','high','medium','low') DEFAULT 'medium',
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `data` json DEFAULT NULL,
  `is_acknowledged` boolean DEFAULT false,
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `acknowledged_by` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_alerta_vehicle` (`vehicle_id`),
  KEY `idx_alerta_type` (`type`),
  KEY `idx_alerta_severity` (`severity`),
  KEY `idx_alerta_acknowledged` (`is_acknowledged`),
  KEY `idx_alerta_created` (`created_at`),
  CONSTRAINT `fk_alerta_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `bem` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_alerta_acknowledged_by` FOREIGN KEY (`acknowledged_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- GEOFENCES
-- ==================================================

DROP TABLE IF EXISTS `cerca_virtual`;
CREATE TABLE `cerca_virtual` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('circle','polygon') DEFAULT 'circle',
  `coordinates` json NOT NULL,
  `radius` decimal(8,2) DEFAULT NULL,
  `color` varchar(7) DEFAULT '#FF0000',
  `is_active` boolean DEFAULT true,
  `alert_on_enter` boolean DEFAULT true,
  `alert_on_exit` boolean DEFAULT true,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cerca_client` (`client_id`),
  KEY `idx_cerca_active` (`is_active`),
  KEY `idx_cerca_type` (`type`),
  CONSTRAINT `fk_cerca_client` FOREIGN KEY (`client_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- VEHICLE-GEOFENCE ASSOCIATIONS
-- ==================================================

DROP TABLE IF EXISTS `veiculo_cerca_virtual`;
CREATE TABLE `veiculo_cerca_virtual` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vehicle_id` int(11) NOT NULL,
  `geofence_id` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_vehicle_geofence` (`vehicle_id`, `geofence_id`),
  KEY `idx_vcv_vehicle` (`vehicle_id`),
  KEY `idx_vcv_geofence` (`geofence_id`),
  CONSTRAINT `fk_vcv_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `bem` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vcv_geofence` FOREIGN KEY (`geofence_id`) REFERENCES `cerca_virtual` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- GPS COMMANDS
-- ==================================================

DROP TABLE IF EXISTS `comando_gps`;
CREATE TABLE `comando_gps` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `vehicle_id` int(11) NOT NULL,
  `imei` varchar(20) NOT NULL,
  `type` enum('reboot','reset','stop_engine','start_engine','set_interval','request_position','set_speed_limit','custom') NOT NULL,
  `command` text NOT NULL,
  `parameters` json DEFAULT NULL,
  `status` enum('pending','sent','confirmed','failed','expired') DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `response` text DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `retry_count` tinyint(2) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_comando_vehicle` (`vehicle_id`),
  KEY `idx_comando_imei` (`imei`),
  KEY `idx_comando_status` (`status`),
  KEY `idx_comando_expires` (`expires_at`),
  KEY `idx_comando_type` (`type`),
  CONSTRAINT `fk_comando_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `bem` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- PAYMENTS
-- ==================================================

DROP TABLE IF EXISTS `pagamento`;
CREATE TABLE `pagamento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_vencimento` date NOT NULL,
  `data_pagamento` date DEFAULT NULL,
  `metodo_pagamento` enum('cash','bank_transfer','credit_card','debit_card','pix','bank_slip','check') DEFAULT 'bank_transfer',
  `status` enum('pending','paid','cancelled','overdue') DEFAULT 'pending',
  `descricao` varchar(200) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `numero_referencia` varchar(50) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pagamento_cliente` (`cliente_id`),
  KEY `idx_pagamento_status` (`status`),
  KEY `idx_pagamento_vencimento` (`data_vencimento`),
  KEY `idx_pagamento_pagamento` (`data_pagamento`),
  CONSTRAINT `fk_pagamento_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- SYSTEM LOGS
-- ==================================================

DROP TABLE IF EXISTS `system_logs`;
CREATE TABLE `system_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `level` enum('emergency','alert','critical','error','warning','notice','info','debug') NOT NULL,
  `message` text NOT NULL,
  `context` json DEFAULT NULL,
  `channel` varchar(50) DEFAULT 'app',
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_logs_level` (`level`),
  KEY `idx_logs_channel` (`channel`),
  KEY `idx_logs_user` (`user_id`),
  KEY `idx_logs_created` (`created_at`),
  CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- SESSIONS
-- ==================================================

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sessions_user` (`user_id`),
  KEY `idx_sessions_last_activity` (`last_activity`),
  CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- CACHE TABLE (for application caching)
-- ==================================================

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  `expiration` timestamp NOT NULL,
  PRIMARY KEY (`key`),
  KEY `idx_cache_expiration` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- JOB QUEUE (for background tasks)
-- ==================================================

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL DEFAULT 'default',
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `reserved_at` timestamp NULL DEFAULT NULL,
  `available_at` timestamp NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_jobs_queue` (`queue`),
  KEY `idx_jobs_reserved_at` (`reserved_at`),
  KEY `idx_jobs_available_at` (`available_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- FAILED JOBS
-- ==================================================

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- VIEWS FOR REPORTING
-- ==================================================

-- Vehicle summary view
CREATE OR REPLACE VIEW `view_vehicle_summary` AS
SELECT 
    v.id,
    v.imei,
    v.name,
    v.identificacao,
    v.status_sinal,
    v.last_latitude,
    v.last_longitude,
    v.last_position_time,
    u.name as owner_name,
    u.email as owner_email,
    COUNT(DISTINCT a.id) as alert_count,
    COUNT(DISTINCT p.id) as position_count_today
FROM bem v
JOIN usuarios u ON v.cliente = u.id
LEFT JOIN alerta a ON v.id = a.vehicle_id AND a.created_at >= CURDATE()
LEFT JOIN posicao_gps p ON v.id = p.vehicle_id AND p.timestamp >= CURDATE()
GROUP BY v.id, v.imei, v.name, v.identificacao, v.status_sinal, v.last_latitude, v.last_longitude, v.last_position_time, u.name, u.email;

-- Alert summary view
CREATE OR REPLACE VIEW `view_alert_summary` AS
SELECT 
    DATE(a.created_at) as alert_date,
    a.type,
    a.severity,
    COUNT(*) as alert_count,
    COUNT(CASE WHEN a.is_acknowledged = 0 THEN 1 END) as unacknowledged_count
FROM alerta a
WHERE a.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY DATE(a.created_at), a.type, a.severity
ORDER BY alert_date DESC, a.severity;

-- ==================================================
-- STORED PROCEDURES
-- ==================================================

DELIMITER $$

-- Procedure to clean old GPS positions
CREATE PROCEDURE `sp_clean_old_positions`(IN days_to_keep INT)
BEGIN
    DECLARE deleted_count INT DEFAULT 0;
    
    DELETE FROM posicao_gps 
    WHERE timestamp < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
    
    GET DIAGNOSTICS deleted_count = ROW_COUNT;
    
    INSERT INTO system_logs (level, message, context, channel) 
    VALUES ('info', 'Old GPS positions cleaned', JSON_OBJECT('deleted_count', deleted_count, 'days_kept', days_to_keep), 'maintenance');
END$$

-- Procedure to process expired commands
CREATE PROCEDURE `sp_process_expired_commands`()
BEGIN
    DECLARE expired_count INT DEFAULT 0;
    
    UPDATE comando_gps 
    SET status = 'expired', updated_at = CURRENT_TIMESTAMP 
    WHERE status IN ('pending', 'sent') AND expires_at < NOW();
    
    GET DIAGNOSTICS expired_count = ROW_COUNT;
    
    INSERT INTO system_logs (level, message, context, channel) 
    VALUES ('info', 'Expired commands processed', JSON_OBJECT('expired_count', expired_count), 'gps_commands');
END$$

-- Procedure to generate vehicle statistics
CREATE PROCEDURE `sp_vehicle_statistics`(IN client_id INT, IN date_from DATE, IN date_to DATE)
BEGIN
    SELECT 
        v.id,
        v.name,
        v.imei,
        COUNT(DISTINCT p.id) as position_count,
        COALESCE(SUM(
            CASE WHEN LAG(p.latitude) OVER (PARTITION BY p.vehicle_id ORDER BY p.timestamp) IS NOT NULL
            THEN ST_Distance_Sphere(
                POINT(LAG(p.longitude) OVER (PARTITION BY p.vehicle_id ORDER BY p.timestamp), LAG(p.latitude) OVER (PARTITION BY p.vehicle_id ORDER BY p.timestamp)),
                POINT(p.longitude, p.latitude)
            ) / 1000
            ELSE 0 END
        ), 0) as total_distance_km,
        MAX(p.speed) as max_speed,
        AVG(p.speed) as avg_speed,
        COUNT(DISTINCT a.id) as alert_count
    FROM bem v
    LEFT JOIN posicao_gps p ON v.id = p.vehicle_id AND DATE(p.timestamp) BETWEEN date_from AND date_to
    LEFT JOIN alerta a ON v.id = a.vehicle_id AND DATE(a.created_at) BETWEEN date_from AND date_to
    WHERE v.cliente = client_id OR client_id IS NULL
    GROUP BY v.id, v.name, v.imei
    ORDER BY v.name;
END$$

DELIMITER ;

-- ==================================================
-- TRIGGERS
-- ==================================================

DELIMITER $$

-- Trigger to update vehicle last position
CREATE TRIGGER `tr_update_vehicle_position` 
AFTER INSERT ON `posicao_gps`
FOR EACH ROW
BEGIN
    UPDATE bem 
    SET 
        last_latitude = NEW.latitude,
        last_longitude = NEW.longitude,
        last_position_time = NEW.timestamp,
        status_sinal = CASE 
            WHEN NEW.timestamp > DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 'online'
            ELSE 'offline'
        END,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = NEW.vehicle_id;
END$$

-- Trigger to log important changes
CREATE TRIGGER `tr_log_vehicle_changes` 
AFTER UPDATE ON `bem`
FOR EACH ROW
BEGIN
    IF OLD.status_sinal != NEW.status_sinal THEN
        INSERT INTO system_logs (level, message, context, channel) 
        VALUES ('info', 'Vehicle status changed', 
                JSON_OBJECT('vehicle_id', NEW.id, 'imei', NEW.imei, 'old_status', OLD.status_sinal, 'new_status', NEW.status_sinal), 
                'vehicle_status');
    END IF;
END$$

DELIMITER ;

-- ==================================================
-- INDEXES FOR PERFORMANCE
-- ==================================================

-- Additional indexes for better performance
CREATE INDEX `idx_posicao_gps_composite` ON `posicao_gps` (`vehicle_id`, `timestamp` DESC, `latitude`, `longitude`);
CREATE INDEX `idx_alerta_composite` ON `alerta` (`vehicle_id`, `created_at` DESC, `is_acknowledged`);
CREATE INDEX `idx_comando_composite` ON `comando_gps` (`imei`, `status`, `expires_at`);

-- ==================================================
-- INITIAL DATA
-- ==================================================

-- Create default admin user
INSERT INTO `usuarios` (`name`, `email`, `password`, `role`, `is_active`, `created_at`) 
VALUES ('Administrador', 'admin@fastrackgps.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- Create sample user
INSERT INTO `usuarios` (`name`, `email`, `password`, `role`, `is_active`, `created_at`) 
VALUES ('Cliente Teste', 'cliente@teste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- ==================================================
-- FINISH
-- ==================================================

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

-- Create database info table
CREATE TABLE `database_info` (
  `key` varchar(50) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `database_info` (`key`, `value`) VALUES
('schema_version', '2.0.0'),
('created_at', NOW()),
('last_migration', NOW());

-- Grant privileges to application user (will be run separately)
-- GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE ON fastrackgps.* TO 'fastrackgps'@'localhost';
-- FLUSH PRIVILEGES;