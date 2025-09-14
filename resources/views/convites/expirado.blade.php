@php
    $site = app(\App\Settings\ConfiguracoesGerais::class)->nome_do_site ?? config('app.name', 'Marokah');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Convite expirado — {{ $site }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        html,body{height:100%} body{margin:0; font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, Arial; background:#0f1115; color:#e5e7eb; display:grid; place-items:center}
        .box{max-width:560px; text-align:center; padding:2rem; border-radius:14px; background:#161a22}
        a{color:#22c55e; text-decoration:none}
        p{color:#9aa4b2}
    </style>
</head>
<body>
<div class="box">
    <h1>Convite expirado</h1>
    <p>Este link de convite não é mais válido. Peça um novo convite ao administrador do sistema.</p>
    <p><a href="/marokah">Voltar ao painel</a></p>
</div>
</body>
</html>
