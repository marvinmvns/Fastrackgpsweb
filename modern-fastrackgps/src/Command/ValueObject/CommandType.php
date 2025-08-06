<?php

declare(strict_types=1);

namespace FastrackGps\Command\ValueObject;

use FastrackGps\Core\Exception\ValidationException;

enum CommandType: string
{
    case BLOCK_ENGINE_AUDIBLE = 'block_audible';
    case BLOCK_ENGINE_SILENT = 'block_silent';
    case UNBLOCK_ENGINE = 'unblock';
    case ACTIVATE_PANIC = 'panic';
    case ACTIVATE_OUTPUT_1 = 'output1';
    case ACTIVATE_OUTPUT_2 = 'output2';
    case ACTIVATE_OUTPUT_3 = 'output3';
    case RESET_GPS = 'reset_gps';
    case RESET_GSM = 'reset_gsm';
    case CLEAR_MEMORY = 'clear_memory';
    case RESET_PASSWORD = 'reset_password';
    case ACTIVATE_SMS_MODE = 'sms_mode';
    case REQUEST_POSITION = 'request_position';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::BLOCK_ENGINE_AUDIBLE => 'Ativar Bloqueio Audível',
            self::BLOCK_ENGINE_SILENT => 'Ativar Bloqueio Silencioso',
            self::UNBLOCK_ENGINE => 'Desativar Bloqueio',
            self::ACTIVATE_PANIC => 'Ativar Pânico (Sirene)',
            self::ACTIVATE_OUTPUT_1 => 'Ativar Saída 1',
            self::ACTIVATE_OUTPUT_2 => 'Ativar Saída 2',
            self::ACTIVATE_OUTPUT_3 => 'Ativar Saída 3',
            self::RESET_GPS => 'Reset GPS',
            self::RESET_GSM => 'Reset GSM',
            self::CLEAR_MEMORY => 'Clear Memória Externa HT24',
            self::RESET_PASSWORD => 'Reset Senha (Senha Fábrica 1234)',
            self::ACTIVATE_SMS_MODE => 'Ativar Modo SMS',
            self::REQUEST_POSITION => 'Solicitar Posição',
        };
    }

    public function buildCommand(array $parameters = []): string
    {
        return match ($this) {
            self::BLOCK_ENGINE_AUDIBLE => $this->buildBlockCommand(true),
            self::BLOCK_ENGINE_SILENT => $this->buildBlockCommand(false),
            self::UNBLOCK_ENGINE => $this->buildUnblockCommand(),
            self::ACTIVATE_PANIC => $this->buildPanicCommand(),
            self::ACTIVATE_OUTPUT_1 => $this->buildOutputCommand(1),
            self::ACTIVATE_OUTPUT_2 => $this->buildOutputCommand(2),
            self::ACTIVATE_OUTPUT_3 => $this->buildOutputCommand(3),
            self::RESET_GPS => 'AT+GTRST=gv65,1,,,,FFFF$',
            self::RESET_GSM => 'AT+GTRST=gv65,2,,,,FFFF$',
            self::CLEAR_MEMORY => 'AT+GTCLR=gv65,,,,FFFF$',
            self::RESET_PASSWORD => 'AT+GTPWD=gv65,1234,,,,FFFF$',
            self::ACTIVATE_SMS_MODE => $this->buildSmsCommand(),
            self::REQUEST_POSITION => 'AT+GTQSS=gv65,,,,FFFF$',
        };
    }

    public function requiresPassword(): bool
    {
        return match ($this) {
            self::BLOCK_ENGINE_AUDIBLE,
            self::BLOCK_ENGINE_SILENT,
            self::UNBLOCK_ENGINE,
            self::RESET_PASSWORD => true,
            default => false,
        };
    }

    public function isDestructive(): bool
    {
        return match ($this) {
            self::BLOCK_ENGINE_AUDIBLE,
            self::BLOCK_ENGINE_SILENT,
            self::RESET_GPS,
            self::RESET_GSM,
            self::CLEAR_MEMORY,
            self::ACTIVATE_SMS_MODE => true,
            default => false,
        };
    }

    public function getConfirmationMessage(): string
    {
        return match ($this) {
            self::BLOCK_ENGINE_AUDIBLE => 'Deseja realmente bloquear o veículo com sirene?',
            self::BLOCK_ENGINE_SILENT => 'Deseja realmente bloquear o veículo silenciosamente?',
            self::ACTIVATE_SMS_MODE => 'Deseja realmente ativar o modo SMS? O veículo não poderá ser monitorado em tempo real.',
            self::RESET_GPS => 'Deseja realmente resetar o GPS? O dispositivo pode ficar offline temporariamente.',
            self::RESET_GSM => 'Deseja realmente resetar o GSM? O dispositivo pode ficar offline temporariamente.',
            self::CLEAR_MEMORY => 'Deseja realmente limpar a memória do dispositivo?',
            default => 'Deseja realmente executar este comando?',
        };
    }

    private function buildBlockCommand(bool $audible): string
    {
        $type = $audible ? '1' : '0';
        return "AT+GTOUT=gv65,1,{$type},1,0,0,0,1,1234,,,,FFFF$";
    }

    private function buildUnblockCommand(): string
    {
        return 'AT+GTOUT=gv65,1,0,0,0,0,0,1,1234,,,,FFFF$';
    }

    private function buildPanicCommand(): string
    {
        return 'AT+GTOUT=gv65,1,1,1,1,0,0,1,1234,,,,FFFF$';
    }

    private function buildOutputCommand(int $output): string
    {
        if ($output < 1 || $output > 3) {
            throw ValidationException::invalidFormat('output', 'valid output number (1-3)');
        }

        $params = ['0', '0', '0']; // Reset all outputs
        $params[$output - 1] = '1'; // Activate selected output

        return "AT+GTOUT=gv65,1,0,{$params[0]},{$params[1]},{$params[2]},0,1,1234,,,,FFFF$";
    }

    private function buildSmsCommand(): string
    {
        return 'AT+GTSMS=gv65,1,,,,FFFF$';
    }

    public static function getAllForUser(): array
    {
        return array_map(
            fn(self $type) => [
                'value' => $type->value,
                'label' => $type->getDisplayName(),
                'destructive' => $type->isDestructive(),
                'confirmation' => $type->getConfirmationMessage(),
            ],
            self::cases()
        );
    }
}