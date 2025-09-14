<?php

namespace App\Http\Controllers;

use App\Models\Convite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AceitarConviteController extends Controller
{
    public function mostrar(Convite $convite, string $token)
    {
        $this->garantirConviteValido($convite, $token);
        return view('auth.aceitar-convite', compact('convite'));
    }

    public function aceitar(Request $request, Convite $convite, string $token)
    {
        $this->garantirConviteValido($convite, $token);

        $dados = $request->validate([
            'nome'      => ['required','string','max:255'],
            'password'  => ['required','confirmed', Password::default()],
        ]);

        $usuario = User::create([
            'name'              => $dados['nome'],
            'email'             => $convite->email,
            'password'          => Hash::make($dados['password']),
            'email_verified_at' => now(),
        ]);

        // se usa spatie/permission:
        if ($papeis = $convite->papeis) {
            try { $usuario->syncRoles($papeis); } catch (\Throwable) {}
        }

        $convite->forceFill(['usado_em' => now()])->save();

        auth()->login($usuario);
        return redirect()->intended('/marokah');
    }

    protected function garantirConviteValido(Convite $convite, string $token): void
    {
        abort_if($convite->usado_em, 410);
        abort_unless(is_null($convite->expira_em) || $convite->expira_em->isFuture(), 410);
        abort_unless(hash_equals($convite->token_hash, hash('sha256', $token)), 403);
    }
}
