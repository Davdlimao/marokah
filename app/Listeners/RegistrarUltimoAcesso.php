<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class RegistrarUltimoAcesso
{
    public function handle(Login $event): void
    {
        $user = \App\Models\User::find($event->user->id);
        if ($user) {
            $user->last_login_at = now();
            $user->last_login_ip = request()->ip();
            $user->save();
        }
    }
}
