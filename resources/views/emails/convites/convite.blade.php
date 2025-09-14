@php
    $nome = $convite->nome ?: 'Olá';
    $expira = $convite->expira_em ? \Illuminate\Support\Carbon::parse($convite->expira_em)->format('d/m/Y H:i') : null;
@endphp

@component('mail::message')
# {{ $nome }},

Você foi convidado(a) a criar uma conta no **{{ config('mail.from.name') ?? config('app.name') }}**.

@component('mail::button', ['url' => $url])
Aceitar convite
@endcomponent

@if($expira)
> Este link expira em **{{ $expira }}**.
@endif

Se o botão acima não funcionar, copie e cole esta URL no seu navegador:

{{ $url }}

Obrigado,  
{{ config('mail.from.name') ?? config('app.name') }}
@endcomponent
