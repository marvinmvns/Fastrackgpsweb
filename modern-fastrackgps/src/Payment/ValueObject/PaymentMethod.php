<?php

declare(strict_types=1);

namespace FastrackGps\Payment\ValueObject;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';
    case CREDIT_CARD = 'credit_card';
    case DEBIT_CARD = 'debit_card';
    case PIX = 'pix';
    case BANK_SLIP = 'bank_slip';
    case CHECK = 'check';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::CASH => 'Dinheiro',
            self::BANK_TRANSFER => 'Transferência Bancária',
            self::CREDIT_CARD => 'Cartão de Crédito',
            self::DEBIT_CARD => 'Cartão de Débito',
            self::PIX => 'PIX',
            self::BANK_SLIP => 'Boleto Bancário',
            self::CHECK => 'Cheque',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::CASH => 'money-coin.png',
            self::BANK_TRANSFER => 'bank-transfer.png',
            self::CREDIT_CARD => 'credit-card.png',
            self::DEBIT_CARD => 'debit-card.png',
            self::PIX => 'pix.png',
            self::BANK_SLIP => 'bank-slip.png',
            self::CHECK => 'check.png',
        };
    }

    public function isInstant(): bool
    {
        return match ($this) {
            self::CASH, self::DEBIT_CARD, self::PIX => true,
            default => false,
        };
    }

    public function requiresConfirmation(): bool
    {
        return match ($this) {
            self::BANK_TRANSFER, self::BANK_SLIP, self::CHECK => true,
            default => false,
        };
    }

    public static function getAllOptions(): array
    {
        return array_map(
            fn(self $method) => [
                'value' => $method->value,
                'label' => $method->getDisplayName(),
                'icon' => $method->getIcon(),
                'instant' => $method->isInstant(),
            ],
            self::cases()
        );
    }
}