<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Aceitar convite • Marokah</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<style>
 body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:2rem;max-width:540px}
 .card{background:#fff;border:1px solid #e5e7eb;border-radius:1rem;padding:1.25rem}
 label{display:block;margin:.75rem 0 .25rem} input{width:100%;padding:.65rem;border:1px solid #d1d5db;border-radius:.5rem}
 button{margin-top:1rem;padding:.7rem 1rem;border:0;border-radius:.5rem;background:#16a34a;color:#fff;font-weight:600}
 .muted{color:#6b7280}
</style>
</head>
<body>
  <h1>Ativar acesso</h1>
  <p class="muted">Convite para <strong>{{ $convite->email }}</strong></p>

  <form method="POST" class="card" action="{{ route('convites.aceitar', [$convite->id, request()->route('token')]) }}">
    @csrf
    <label>Seu nome</label>
    <input name="nome" value="{{ old('nome', $convite->nome) }}" required>

    <label>Senha</label>
    <input type="password" name="password" required>

    <label>Confirmar senha</label>
    <input type="password" name="password_confirmation" required>
    @error('password') <div style="color:#b91c1c;margin-top:.25rem">{{ $message }}</div> @enderror

    <button type="submit">Criar acesso</button>
  </form>
</body>
</html>
