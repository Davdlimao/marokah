<?php

namespace App\Enums;

enum PlanPeriod: string {
    case MENSAL='MENSAL'; case TRIMESTRAL='TRIMESTRAL'; case SEMESTRAL='SEMESTRAL'; case ANUAL='ANUAL';
    public function label(): string { return match($this){ self::MENSAL=>'Mensal', self::TRIMESTRAL=>'Trimestral', self::SEMESTRAL=>'Semestral', self::ANUAL=>'Anual' }; }
    public function color(): string { return match($this){ self::MENSAL=>'primary', self::TRIMESTRAL=>'info', self::SEMESTRAL=>'warning', self::ANUAL=>'success' }; }
}
