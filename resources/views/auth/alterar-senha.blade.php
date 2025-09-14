<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Alterar senha</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100">
<div class="max-w-md mx-auto mt-16 bg-white rounded shadow p-6">
    <h1 class="text-xl font-semibold mb-4">Defina uma nova senha</h1>

    @if (session('status'))
        <div class="mb-3 text-green-700 bg-green-100 rounded p-2">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-3 text-red-700 bg-red-100 rounded p-2">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('senha.alterar.salvar') }}">
        @csrf
        <label class="block text-sm font-medium text-gray-700 mb-1">Nova senha</label>
        <input type="password" name="password" class="w-full border rounded px-3 py-2 mb-3" required>

        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar senha</label>
        <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2 mb-6" required>

        <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white rounded px-3 py-2">
            Salvar e entrar
        </button>
    </form>
</div>
</body>
</html>
