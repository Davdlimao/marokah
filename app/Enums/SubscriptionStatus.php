<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case ATIVA = 'ATIVA';
    case PAUSADA = 'PAUSADA';
    case INADIMPLENTE = 'INADIMPLENTE';
    case CANCELADA = 'CANCELADA';

    public function label(): string
    {
        return match ($this) {
            self::ATIVA => 'Ativa',
            self::PAUSADA => 'Pausada',
            self::INADIMPLENTE => 'Inadimplente',
            self::CANCELADA => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ATIVA => 'success',
            self::PAUSADA => 'warning',
            self::INADIMPLENTE => 'danger',
            self::CANCELADA => 'gray',
        };
    }
}
