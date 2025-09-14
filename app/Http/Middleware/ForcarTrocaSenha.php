<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForcarTrocaSenha
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // rotas que não devem redirecionar
        if ($request->routeIs([
            'senha.alterar',
            'senha.alterar.atualizar',
            'filament.marokah.auth.login',
            'filament.marokah.auth.logout',
        ])) {
            return $next($request);
        }

        if ($user->must_change_password) {
            return redirect()->route('senha.alterar');
        }

        return $next($request);
    }
}
