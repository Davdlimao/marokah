@php
    /** @var \App\Models\Convite $convite */
    $site = app(\App\Settings\ConfiguracoesGerais::class)->nome_do_site ?? config('app.name', 'Marokah');
    $ui   = app(\App\Settings\AparenciaSettings::class);
    $logo = $ui->logo_header ? asset('storage/'.$ui->logo_header) : null;
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Criar sua conta — {{ $site }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root{ --brand: {{ $ui->cor_primaria ?? '#16a34a' }}; }
        *{ box-sizing:border-box }
        html,body{ height:100% }
        body{
            margin:0; font-family: Inter,system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial;
            background:#f7f7f9; color:#111827;
        }
        .wrap{ min-height:100%; display:grid; place-items:center; padding:2rem }
        .card{ width:100%; max-width:560px; background:#fff; border-radius:14px; padding:28px; box-shadow:0 10px 30px rgba(0,0,0,.06) }
        .brand{ display:flex; align-items:center; gap:.75rem; margin-bottom:1.25rem }
        .brand img{ height:28px }
        .brand h1{ font-size:1.125rem; margin:0; font-weight:600 }
        h2{ margin:.25rem 0 1.25rem; font-size:1.125rem }
        label{ display:block; font-size:.9rem; margin:.75rem 0 .35rem; color:#374151 }
        input{
            width:100%; padding:.75rem .9rem; border:1px solid #e5e7eb; border-radius:10px; font-size:1rem; outline:none; background:#fff;
        }
        input[readonly]{ background:#f3f4f6; color:#6b7280 }
        .row{ display:grid; gap:.75rem; grid-template-columns:1fr 1fr }
        .btn{
            margin-top:1rem; width:100%; display:inline-flex; justify-content:center; align-items:center; gap:.5rem;
            padding:.9rem 1rem; background:var(--brand); color:#fff; border:none; border-radius:12px; font-weight:600; cursor:pointer
        }
        .muted{ font-size:.85rem; color:#6b7280; margin-top:.5rem }
        .errors{ background:#fef2f2; color:#991b1b; border:1px solid #fecaca; padding:.75rem .9rem; border-radius:10px; margin:.5rem 0 1rem }
        @media (prefers-color-scheme: dark){
            body{ background:#0f1115; color:#e5e7eb }
            .card{ background:#161a22; box-shadow:0 10px 30px rgba(0,0,0,.45) }
            input{ background:#0f1115; border-color:#2a3140; color:#e5e7eb }
            input[readonly]{ background:#11161e; color:#94a3b8 }
            label{ color:#cbd5e1 }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="brand">
            @if($logo)<img src="{{ $logo }}" alt="Logo">@endif
            <h1>{{ $site }}</h1>
        </div>

        <h2>Convite para criar sua conta</h2>

        @if ($errors->any())
            <div class="errors">
                <strong>Ops!</strong> Verifique os campos abaixo:
                <ul style="margin:.5rem 0 0 1rem">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('convites.concluir', [$convite->id, $token]) }}">
            @csrf

            <label>E-mail</label>
            <input type="email" value="{{ $convite->email }}" readonly>

            <label for="nome">Nome</label>
            <input id="nome" name="nome" type="text" value="{{ old('nome', $convite->nome ?? '') }}" required>

            <div class="row">
                <div>
                    <label for="senha">Senha</label>
                    <input id="senha" name="senha" type="password" required autocomplete="new-password">
                </div>
                <div>
                    <label for="senha_confirmation">Confirmar senha</label>
                    <input id="senha_confirmation" name="senha_confirmation" type="password" required autocomplete="new-password">
                </div>
            </div>

            <button class="btn" type="submit">Criar conta</button>
            <div class="muted">Este convite é pessoal e pode expirar.</div>
        </form>
    </div>
</div>
</body>
</html>
