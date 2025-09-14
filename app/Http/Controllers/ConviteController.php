<?php

namespace App\Http\Controllers;

use App\Models\Convite;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ConviteController extends Controller
{
    // GET /convites/{convite}/{token}
    public function mostrar(int $convite, string $token)
    {
        $c = Convite::query()->findOrFail($convite);

        // 1) token confere?
        $tokenHash = hash('sha256', $token);
        if (! hash_equals($c->token_hash, $tokenHash)) {
            abort(404); // não revela se existe
        }

        // 2) expirado?
        if ($c->expira_em && Carbon::now()->greaterThan(Carbon::parse($c->expira_em))) {
            return response()->view('convites.expirado'); // crie uma view simples se quiser
        }

        // 3) já usado?
        if ($c->usado_em) {
            return response()->view('convites.ja-usado');
        }

        // Exibe o formulário de criação (nome/email fixos, senha + confirmação)
        return view('convites.form', ['convite' => $c, 'token' => $token]);
    }

    // POST /convites/{convite}/{token}
    public function concluir(Request $request, int $convite, string $token)
    {
        $c = Convite::query()->lockForUpdate()->findOrFail($convite);

        // mesmas validações do GET
        $tokenHash = hash('sha256', $token);
        if (! hash_equals($c->token_hash, $tokenHash)) abort(404);
        if ($c->expira_em && now()->gt(Carbon::parse($c->expira_em))) {
            throw ValidationException::withMessages(['senha' => 'Este convite expirou.']);
        }
        if ($c->usado_em) {
            throw ValidationException::withMessages(['senha' => 'Este convite já foi utilizado.']);
        }

        // valida entrada
        $dados = $request->validate([
            'nome'                  => ['required', 'string', 'max:255'],
            'senha'                 => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // cria usuário (email do convite é soberano)
        $user = User::create([
            'name'     => $dados['nome'],
            'email'    => $c->email,
            'password' => Hash::make($dados['senha']),
        ]);

        // marque o convite como usado
        $c->forceFill([
            'usado_em' => now(),
        ])->save();

        // autentica e redireciona
        auth()->login($user);
        return redirect()->to('/marokah'); // ajuste o destino que desejar
    }
}
