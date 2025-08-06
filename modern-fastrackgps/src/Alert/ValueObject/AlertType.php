<?php

declare(strict_types=1);

namespace FastrackGps\Alert\ValueObject;

enum AlertType: string
{
    case PANIC = 'panic';
    case LOW_BATTERY = 'low_battery';
    case SPEEDING = 'speeding';
    case GEOFENCE_ENTER = 'geofence_enter';
    case GEOFENCE_EXIT = 'geofence_exit';
    case ENGINE_BLOCKED = 'engine_blocked';
    case ENGINE_UNBLOCKED = 'engine_unblocked';
    case DEVICE_OFFLINE = 'device_offline';
    case DEVICE_ONLINE = 'device_online';
    case COMMAND_SENT = 'command_sent';
    case COMMAND_FAILED = 'command_failed';
    case GPS_SIGNAL_LOST = 'gps_signal_lost';
    case GPS_SIGNAL_RESTORED = 'gps_signal_restored';
    case MAINTENANCE_DUE = 'maintenance_due';
    case UNAUTHORIZED_MOVEMENT = 'unauthorized_movement';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::PANIC => 'Botão de Pânico',
            self::LOW_BATTERY => 'Bateria Fraca',
            self::SPEEDING => 'Excesso de Velocidade',
            self::GEOFENCE_ENTER => 'Entrada em Cerca Virtual',
            self::GEOFENCE_EXIT => 'Saída de Cerca Virtual',
            self::ENGINE_BLOCKED => 'Motor Bloqueado',
            self::ENGINE_UNBLOCKED => 'Motor Desbloqueado',
            self::DEVICE_OFFLINE => 'Dispositivo Offline',
            self::DEVICE_ONLINE => 'Dispositivo Online',
            self::COMMAND_SENT => 'Comando Enviado',
            self::COMMAND_FAILED => 'Falha no Comando',
            self::GPS_SIGNAL_LOST => 'Sinal GPS Perdido',
            self::GPS_SIGNAL_RESTORED => 'Sinal GPS Restaurado',
            self::MAINTENANCE_DUE => 'Manutenção Programada',
            self::UNAUTHORIZED_MOVEMENT => 'Movimento Não Autorizado',
        };
    }

    public function getDefaultSeverity(): AlertSeverity
    {
        return match ($this) {
            self::PANIC,
            self::ENGINE_BLOCKED,
            self::UNAUTHORIZED_MOVEMENT => AlertSeverity::CRITICAL,
            
            self::LOW_BATTERY,
            self::SPEEDING,
            self::DEVICE_OFFLINE,
            self::GPS_SIGNAL_LOST,
            self::COMMAND_FAILED => AlertSeverity::HIGH,
            
            self::GEOFENCE_ENTER,
            self::GEOFENCE_EXIT,
            self::MAINTENANCE_DUE => AlertSeverity::MEDIUM,
            
            self::ENGINE_UNBLOCKED,
            self::DEVICE_ONLINE,
            self::COMMAND_SENT,
            self::GPS_SIGNAL_RESTORED => AlertSeverity::LOW,
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PANIC => 'help.png',
            self::LOW_BATTERY => 'battery-low.png',
            self::SPEEDING => 'velocidade.png',
            self::GEOFENCE_ENTER, self::GEOFENCE_EXIT => 'geofence.png',
            self::ENGINE_BLOCKED => 'bloqueado.png',
            self::ENGINE_UNBLOCKED => 'ativo.png',
            self::DEVICE_OFFLINE => 'status_desligado.png',
            self::DEVICE_ONLINE => 'status_rastreando.png',
            self::COMMAND_SENT => 'executando.gif',
            self::COMMAND_FAILED => 'erro.png',
            self::GPS_SIGNAL_LOST => 'status_sem_sinal.png',
            self::GPS_SIGNAL_RESTORED => 'sucesso.png',
            self::MAINTENANCE_DUE => 'maintenance.png',
            self::UNAUTHORIZED_MOVEMENT => 'alert.gif',
        };
    }

    public function requiresImmediateAction(): bool
    {
        return match ($this) {
            self::PANIC,
            self::ENGINE_BLOCKED,
            self::UNAUTHORIZED_MOVEMENT => true,
            default => false,
        };
    }
}