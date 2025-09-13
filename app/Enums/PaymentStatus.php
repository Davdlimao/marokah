<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDENTE = 'PENDENTE';
    case CONFIRMADO = 'CONFIRMADO';
    case FALHOU = 'FALHOU';
    case ESTORNADO = 'ESTORNADO';

    public function label(): string
    {
        return match ($this) {
            self::PENDENTE => 'Pendente',
            self::CONFIRMADO => 'Confirmado',
            self::FALHOU => 'Falhou',
            self::ESTORNADO => 'Estornado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDENTE => 'warning',
            self::CONFIRMADO => 'success',
            self::FALHOU => 'danger',
            self::ESTORNADO => 'gray',
        };
    }
}
