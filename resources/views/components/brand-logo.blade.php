@php($s = app(\App\Settings\AparenciaSettings::class))

@php($light = $s->logo_header)       {{-- logo para fundo claro (tema claro) --}}
@php($dark  = $s->logo_header_dark)  {{-- logo para fundo escuro (tema escuro) --}}

@if($light || $dark)
    <div class="flex items-center">
        @if($light)
            <img
                src="{{ asset('storage/'.$light) }}"
                alt="Logo"
                class="brand-light h-7 block"
                loading="lazy"
            >
        @endif

        @if($dark)
            <img
                src="{{ asset('storage/'.$dark) }}"
                alt="Logo (dark)"
                class="brand-dark h-7 block"
                loading="lazy"
            >
        @endif
    </div>
@else
    {{-- Sem logos: o Provider usa brandName como fallback --}}
@endif
