@php
    /** @var \App\Models\Plano $p */
    $label = fn ($s) => $periodLabels[strtoupper((string) $s)] ?? '—';
    $money = function ($v) {
        if ($v === null || $v === '') return '—';
        return 'R$ ' . number_format((float) $v, 2, ',', '.');
    };
@endphp
