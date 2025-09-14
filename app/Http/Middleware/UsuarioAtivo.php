<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UsuarioAtivo
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            auth()->logout();

            return redirect()
                ->route('filament.marokah.auth.login')
                ->withErrors(['email' => 'Seu acesso está bloqueado.']);
        }

        return $next($request);
    }
}
