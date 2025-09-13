<?php

namespace App\Enums;

enum PlanStatus: string
{
    case ATIVO = 'ATIVO';
    case INATIVO = 'INATIVO';

    public function label(): string
    {
        return $this === self::ATIVO ? 'ATIVO' : 'INATIVO';
    }

    public function color(): string
    {
        return $this === self::ATIVO ? 'success' : 'danger';
    }

    /** Converte qualquer entrada (bool/num/string/enum) para PlanStatus */
    public static function coerce(mixed $v): self
    {
        if ($v instanceof self) return $v;

        // normaliza strings
        if (is_string($v)) {
            $s = mb_strtoupper(trim($v));
            return $s === 'ATIVO' ? self::ATIVO : self::INATIVO;
        }

        // bool / num
        if (is_bool($v)) return $v ? self::ATIVO : self::INATIVO;
        if (is_numeric($v)) return ((int) $v) === 1 ? self::ATIVO : self::INATIVO;

        return self::INATIVO;
    }
}
