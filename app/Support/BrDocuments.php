<?php

namespace App\Support;

final class BrDocuments
{
    public static function onlyDigits(?string $value): string
    {
        return preg_replace('/\D+/', '', (string) $value);
    }

    public static function cpf(?string $value): bool
    {
        $cpf = self::onlyDigits($value);
        if (strlen($cpf) !== 11 || preg_match('/^(\\d)\\1{10}$/', $cpf)) return false;

        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) $d += $cpf[$c] * (($t + 1) - $c);
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$t] != $d) return false;
        }
        return true;
    }

    public static function cnpj(?string $value): bool
    {
        $cnpj = self::onlyDigits($value);

        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $nums = array_map('intval', str_split($cnpj));

        // 1º DV
        $w1 = [5,4,3,2,9,8,7,6,5,4,3,2];
        $s1 = 0;
        for ($i = 0; $i < 12; $i++) $s1 += $nums[$i] * $w1[$i];
        $d1 = ($s1 % 11) < 2 ? 0 : 11 - ($s1 % 11);

        // 2º DV
        $w2 = [6,5,4,3,2,9,8,7,6,5,4,3,2];
        $s2 = 0;
        for ($i = 0; $i < 13; $i++) $s2 += $nums[$i] * $w2[$i];
        $d2 = ($s2 % 11) < 2 ? 0 : 11 - ($s2 % 11);

        return $nums[12] === $d1 && $nums[13] === $d2;
    }
}
