<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case ABERTA = 'ABERTA';
    case PAGA = 'PAGA';
    case ATRASADA = 'ATRASADA';
    case CANCELADA = 'CANCELADA';

    public function label(): string
    {
        return match ($this) {
            self::ABERTA => 'Aberta',
            self::PAGA => 'Paga',
            self::ATRASADA => 'Atrasada',
            self::CANCELADA => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ABERTA => 'info',
            self::PAGA => 'success',
            self::ATRASADA => 'danger',
            self::CANCELADA => 'gray',
        };
    }
}
