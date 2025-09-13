<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case PIX = 'PIX';
    case BOLETO = 'BOLETO';
    case CARTAO = 'CARTAO';
    case TRANSF = 'TRANSF';
    case DINHEIRO = 'DINHEIRO';

    public function label(): string
    {
        return match ($this) {
            self::PIX => 'PIX',
            self::BOLETO => 'Boleto',
            self::CARTAO => 'Cartão',
            self::TRANSF => 'Transferência',
            self::DINHEIRO => 'Dinheiro',
        };
    }
}
