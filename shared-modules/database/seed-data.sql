-- =====================================================
-- FastrackGPS - Dados Iniciais do Sistema Moderno
-- Versão: 2.0.0
-- Arquivo: seed-data.sql
-- =====================================================

-- Configurações da sessão
SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

-- =====================================================
-- USUÁRIOS INICIAIS
-- =====================================================

-- Usuário Administrador Master
INSERT IGNORE INTO modern_users (
    id, name, email, password_hash, is_active, is_admin, 
    phone, company, language, timezone, created_at
) VALUES (
    '550e8400-e29b-41d4-a716-446655440000',
    'Administrador Master',
    'admin@fastrackgps.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    TRUE,
    TRUE,
    '+55 11 99999-9999',
    'FastrackGPS',
    'pt_BR',
    'America/Sao_Paulo',
    NOW()
);

-- Usuário de Demonstração
INSERT IGNORE INTO modern_users (
    id, name, email, password_hash, is_active, is_admin,
    phone, company, language, timezone, created_at
) VALUES (
    '550e8400-e29b-41d4-a716-446655440001', 
    'Usuário Demonstração',
    'demo@fastrackgps.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    TRUE,
    FALSE,
    '+55 11 88888-8888',
    'Empresa Demo',
    'pt_BR',
    'America/Sao_Paulo',
    NOW()
);

-- Usuário de Teste
INSERT IGNORE INTO modern_users (
    id, name, email, password_hash, is_active, is_admin,
    phone, company, language, timezone, created_at
) VALUES (
    '550e8400-e29b-41d4-a716-446655440002',
    'Usuário Teste',
    'teste@fastrackgps.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    TRUE,
    FALSE,
    '+55 11 77777-7777',
    'Empresa Teste Ltda',
    'pt_BR',
    'America/Sao_Paulo',
    NOW()
);

-- =====================================================
-- VEÍCULOS DE DEMONSTRAÇÃO
-- =====================================================

-- Veículo 1 - Carro
INSERT IGNORE INTO modern_vehicles (
    id, user_id, name, imei, plate, model, brand, year, color, category,
    device_type, icon_id, is_active, odometer, fuel_capacity, notes, created_at
) VALUES (
    '660e8400-e29b-41d4-a716-446655440000',
    '550e8400-e29b-41d4-a716-446655440001', -- Usuario Demo
    'Civic LX',
    '123456789012345',
    'ABC-1234',
    'Civic LX',
    'Honda',
    2022,
    'Branco',
    'car',
    'GT06N',
    1,
    TRUE,
    25000000, -- 25.000 km em metros
    50.00,
    'Veículo de demonstração - Honda Civic LX 2022',
    NOW()
);

-- Veículo 2 - Caminhão
INSERT IGNORE INTO modern_vehicles (
    id, user_id, name, imei, plate, model, brand, year, color, category,
    device_type, icon_id, is_active, odometer, fuel_capacity, notes, created_at
) VALUES (
    '660e8400-e29b-41d4-a716-446655440001',
    '550e8400-e29b-41d4-a716-446655440001', -- Usuario Demo
    'Atego 1419',
    '123456789012346',
    'DEF-5678',
    'Atego 1419',
    'Mercedes-Benz',
    2021,
    'Azul',
    'truck',
    'TK102B',
    2,
    TRUE,
    120000000, -- 120.000 km em metros
    200.00,
    'Caminhão de carga para demonstração',
    NOW()
);

-- Veículo 3 - Motocicleta
INSERT IGNORE INTO modern_vehicles (
    id, user_id, name, imei, plate, model, brand, year, color, category,
    device_type, icon_id, is_active, odometer, fuel_capacity, notes, created_at
) VALUES (
    '660e8400-e29b-41d4-a716-446655440002',
    '550e8400-e29b-41d4-a716-446655440002', -- Usuario Teste
    'Falcon 400',
    '123456789012347',
    'GHI-9012',
    'Falcon NX 400',
    'Honda',
    2020,
    'Vermelha',
    'motorcycle',
    'ST901',
    10,
    TRUE,
    45000000, -- 45.000 km em metros
    18.50,
    'Motocicleta para entrega rápida',
    NOW()
);

-- =====================================================
-- POSIÇÕES DE DEMONSTRAÇÃO
-- =====================================================

-- Posições para o Civic (São Paulo - Centro)
INSERT INTO modern_positions (
    id, vehicle_id, latitude, longitude, altitude, speed, course,
    satellites, hdop, battery_level, gsm_signal, ignition, motion,
    address, recorded_at, received_at
) VALUES 
(
    UUID(),
    '660e8400-e29b-41d4-a716-446655440000',
    -23.5505200,
    -46.6333094,
    750,
    35.50,
    120.00,
    8,
    1.2,
    85,
    90,
    TRUE,
    TRUE,
    'Av. Paulista, 1000 - Bela Vista, São Paulo - SP',
    DATE_SUB(NOW(), INTERVAL 5 MINUTE),
    DATE_SUB(NOW(), INTERVAL 4 MINUTE)
),
(
    UUID(),
    '660e8400-e29b-41d4-a716-446655440000',
    -23.5489300,
    -46.6388200,
    755,
    42.20,
    115.50,
    9,
    0.8,
    84,
    88,
    TRUE,
    TRUE,
    'Av. Paulista, 500 - Bela Vista, São Paulo - SP',
    DATE_SUB(NOW(), INTERVAL 10 MINUTE),
    DATE_SUB(NOW(), INTERVAL 9 MINUTE)
);

-- Posições para o Caminhão (Via Dutra)
INSERT INTO modern_positions (
    id, vehicle_id, latitude, longitude, altitude, speed, course,
    satellites, hdop, battery_level, gsm_signal, ignition, motion,
    address, recorded_at, received_at
) VALUES 
(
    UUID(),
    '660e8400-e29b-41d4-a716-446655440001',
    -23.2237600,
    -45.9009700,
    650,
    80.00,
    45.00,
    7,
    1.5,
    92,
    85,
    TRUE,
    TRUE,
    'Rod. Pres. Dutra, km 165 - São José dos Campos - SP',
    DATE_SUB(NOW(), INTERVAL 2 MINUTE),
    DATE_SUB(NOW(), INTERVAL 1 MINUTE)
);

-- Posição para a Motocicleta (estacionada)
INSERT INTO modern_positions (
    id, vehicle_id, latitude, longitude, altitude, speed, course,
    satellites, hdop, battery_level, gsm_signal, ignition, motion,
    address, recorded_at, received_at
) VALUES 
(
    UUID(),
    '660e8400-e29b-41d4-a716-446655440002',
    -23.5629900,
    -46.6544600,
    740,
    0.00,
    0.00,
    6,
    2.1,
    76,
    92,
    FALSE,
    FALSE,
    'R. Augusta, 2000 - Jardins, São Paulo - SP',
    DATE_SUB(NOW(), INTERVAL 15 MINUTE),
    DATE_SUB(NOW(), INTERVAL 14 MINUTE)
);

-- =====================================================
-- CERCAS VIRTUAIS DE DEMONSTRAÇÃO
-- =====================================================

-- Cerca Circular - Centro de São Paulo
INSERT INTO modern_geofences (
    id, user_id, name, description, type, coordinates, radius,
    color, is_active, alert_enter, alert_exit, created_at
) VALUES (
    '770e8400-e29b-41d4-a716-446655440000',
    '550e8400-e29b-41d4-a716-446655440001', -- Usuario Demo
    'Centro de São Paulo',
    'Cerca virtual para monitoramento no centro da cidade',
    'circle',
    JSON_OBJECT(
        'center', JSON_OBJECT('lat', -23.5505200, 'lng', -46.6333094)
    ),
    2000, -- 2km de raio
    '#FF0000',
    TRUE,
    TRUE,
    TRUE,
    NOW()
);

-- Cerca Poligonal - Distrito Industrial
INSERT INTO modern_geofences (
    id, user_id, name, description, type, coordinates, radius,
    color, is_active, alert_enter, alert_exit, created_at
) VALUES (
    '770e8400-e29b-41d4-a716-446655440001',
    '550e8400-e29b-41d4-a716-446655440001', -- Usuario Demo
    'Distrito Industrial',
    'Área restrita do distrito industrial',
    'polygon',
    JSON_OBJECT(
        'coordinates', JSON_ARRAY(
            JSON_ARRAY(-23.520000, -46.620000),
            JSON_ARRAY(-23.520000, -46.610000),
            JSON_ARRAY(-23.530000, -46.610000),
            JSON_ARRAY(-23.530000, -46.620000),
            JSON_ARRAY(-23.520000, -46.620000)
        )
    ),
    NULL,
    '#00FF00',
    TRUE,
    FALSE,
    TRUE,
    NOW()
);

-- =====================================================
-- ASSOCIAÇÕES CERCA-VEÍCULO
-- =====================================================

INSERT INTO modern_geofence_vehicles (id, geofence_id, vehicle_id) VALUES
(UUID(), '770e8400-e29b-41d4-a716-446655440000', '660e8400-e29b-41d4-a716-446655440000'),
(UUID(), '770e8400-e29b-41d4-a716-446655440001', '660e8400-e29b-41d4-a716-446655440001');

-- =====================================================
-- ALERTAS DE DEMONSTRAÇÃO
-- =====================================================

-- Alerta de Velocidade
INSERT INTO modern_alerts (
    id, vehicle_id, type, severity, title, message, data,
    is_read, created_at
) VALUES (
    UUID(),
    '660e8400-e29b-41d4-a716-446655440000',
    'speed_limit',
    'high',
    'Excesso de Velocidade',
    'Veículo Civic LX ultrapassou o limite de velocidade de 60 km/h',
    JSON_OBJECT(
        'speed', 85.5,
        'limit', 60,
        'location', 'Av. Paulista, São Paulo - SP'
    ),
    FALSE,
    DATE_SUB(NOW(), INTERVAL 30 MINUTE)
);

-- Alerta de Geofence
INSERT INTO modern_alerts (
    id, vehicle_id, geofence_id, type, severity, title, message, data,
    is_read, created_at
) VALUES (
    UUID(),
    '660e8400-e29b-41d4-a716-446655440001',
    '770e8400-e29b-41d4-a716-446655440001',
    'geofence_exit',
    'medium',
    'Saída de Área',
    'Caminhão Atego 1419 saiu da cerca virtual Distrito Industrial',
    JSON_OBJECT(
        'geofence_name', 'Distrito Industrial',
        'exit_time', DATE_SUB(NOW(), INTERVAL 45 MINUTE),
        'location', 'Distrito Industrial, São Paulo - SP'
    ),
    FALSE,
    DATE_SUB(NOW(), INTERVAL 45 MINUTE)
);

-- Alerta de Bateria Baixa
INSERT INTO modern_alerts (
    id, vehicle_id, type, severity, title, message, data,
    is_read, created_at
) VALUES (
    UUID(),
    '660e8400-e29b-41d4-a716-446655440002',
    'low_battery',
    'critical',
    'Bateria Baixa',
    'Dispositivo da motocicleta Falcon 400 com bateria em 15%',
    JSON_OBJECT(
        'battery_level', 15,
        'threshold', 20,
        'device_type', 'ST901'
    ),
    FALSE,
    DATE_SUB(NOW(), INTERVAL 1 HOUR)
);

-- =====================================================
-- COMANDOS DE DEMONSTRAÇÃO
-- =====================================================

-- Comando de Bloqueio
INSERT INTO modern_commands (
    id, vehicle_id, user_id, command_type, command_text, command_data,
    status, priority, response, sent_at, acknowledged_at, created_at
) VALUES (
    UUID(),
    '660e8400-e29b-41d4-a716-446655440000',
    '550e8400-e29b-41d4-a716-446655440001',
    'engine_cut',
    'AT+GTRTO=gv300,2,0,,,,FFFF$',
    JSON_OBJECT(
        'action', 'cut_engine',
        'duration', 0,
        'reason', 'Teste de bloqueio remoto'
    ),
    'acknowledged',
    'high',
    'OK: Engine cut command executed',
    DATE_SUB(NOW(), INTERVAL 2 HOUR),
    DATE_SUB(NOW(), INTERVAL 2 HOUR) + INTERVAL 30 SECOND,
    DATE_SUB(NOW(), INTERVAL 2 HOUR) - INTERVAL 5 MINUTE
);

-- Comando de Desbloqueio
INSERT INTO modern_commands (
    id, vehicle_id, user_id, command_type, command_text, command_data,
    status, priority, response, sent_at, acknowledged_at, created_at
) VALUES (
    UUID(),
    '660e8400-e29b-41d4-a716-446655440000',
    '550e8400-e29b-41d4-a716-446655440001',
    'engine_resume',
    'AT+GTRTO=gv300,2,1,,,,FFFF$',
    JSON_OBJECT(
        'action', 'resume_engine',
        'reason', 'Teste de desbloqueio remoto'
    ),
    'acknowledged',
    'high',
    'OK: Engine resume command executed',
    DATE_SUB(NOW(), INTERVAL 1 HOUR) - INTERVAL 30 MINUTE,
    DATE_SUB(NOW(), INTERVAL 1 HOUR) - INTERVAL 29 MINUTE,
    DATE_SUB(NOW(), INTERVAL 1 HOUR) - INTERVAL 35 MINUTE
);

-- Comando Pendente
INSERT INTO modern_commands (
    id, vehicle_id, user_id, command_type, command_text, command_data,
    status, priority, attempts, max_attempts, timeout_seconds, created_at, expires_at
) VALUES (
    UUID(),
    '660e8400-e29b-41d4-a716-446655440002',
    '550e8400-e29b-41d4-a716-446655440002',
    'request_position',
    'AT+GTQSS=st901,1,0,0,1,1,FFFF$',
    JSON_OBJECT(
        'action', 'request_current_position',
        'reason', 'Solicitação manual de posição'
    ),
    'pending',
    'normal',
    0,
    3,
    300,
    NOW() - INTERVAL 5 MINUTE,
    NOW() + INTERVAL 1 HOUR
);

-- =====================================================
-- DADOS DE AUDITORIA INICIAIS
-- =====================================================

-- Log de criação do usuário demo
INSERT INTO modern_audit_logs (
    id, user_id, entity_type, entity_id, action, new_values, ip_address, created_at
) VALUES (
    UUID(),
    '550e8400-e29b-41d4-a716-446655440000', -- Admin
    'user',
    '550e8400-e29b-41d4-a716-446655440001', -- Usuario Demo
    'create',
    JSON_OBJECT(
        'name', 'Usuário Demonstração',
        'email', 'demo@fastrackgps.com',
        'is_active', TRUE,
        'is_admin', FALSE
    ),
    '127.0.0.1',
    NOW() - INTERVAL 1 DAY
);

-- =====================================================
-- CONFIGURAÇÕES ADICIONAIS
-- =====================================================

-- Reabilitar verificações de chave estrangeira
SET foreign_key_checks = 1;

-- Atualizar estatísticas das tabelas
ANALYZE TABLE modern_users, modern_vehicles, modern_positions, 
             modern_geofences, modern_alerts, modern_commands;

-- Verificar integridade referencial
SELECT 'Verificação de integridade concluída' AS status;

-- Estatísticas dos dados inseridos
SELECT 
    'Usuários' AS tabela, COUNT(*) AS registros FROM modern_users
UNION ALL SELECT 
    'Veículos', COUNT(*) FROM modern_vehicles
UNION ALL SELECT 
    'Posições', COUNT(*) FROM modern_positions
UNION ALL SELECT 
    'Cercas Virtuais', COUNT(*) FROM modern_geofences
UNION ALL SELECT 
    'Alertas', COUNT(*) FROM modern_alerts
UNION ALL SELECT 
    'Comandos', COUNT(*) FROM modern_commands
UNION ALL SELECT 
    'Logs de Auditoria', COUNT(*) FROM modern_audit_logs;