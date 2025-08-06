<?php

declare(strict_types=1);

namespace FastrackGps\Vehicle\ValueObject;

enum Carrier: string
{
    case NONE = '';
    case TIM = 'TI';
    case CLARO = 'CL';
    case VIVO = 'VI';
    case OI = 'OI';
    case CTBC = 'CT';
    case BRASIL_TELECOM = 'BT';
    case AMAZONIA = 'AM';

    public static function fromString(string $carrier): self
    {
        return match (strtoupper($carrier)) {
            'TI', 'TIM' => self::TIM,
            'CL', 'CLARO' => self::CLARO,
            'VI', 'VIVO' => self::VIVO,
            'OI' => self::OI,
            'CT', 'CTBC' => self::CTBC,
            'BT', 'BRASIL_TELECOM' => self::BRASIL_TELECOM,
            'AM', 'AMAZONIA' => self::AMAZONIA,
            default => self::NONE,
        };
    }

    public function getDisplayName(): string
    {
        return match ($this) {
            self::NONE => '--Selecione--',
            self::TIM => 'Tim',
            self::CLARO => 'Claro',
            self::VIVO => 'Vivo',
            self::OI => 'Oi',
            self::CTBC => 'CTBC',
            self::BRASIL_TELECOM => 'Brasil Telecom',
            self::AMAZONIA => 'Amazonia Celular',
        };
    }

    public static function getAllOptions(): array
    {
        return array_map(
            fn(self $carrier) => [
                'value' => $carrier->value,
                'label' => $carrier->getDisplayName(),
            ],
            self::cases()
        );
    }
}