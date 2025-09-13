<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Cliente;
use App\Models\Plano;
// use App\Models\Fatura;     // (adicione quando criar a policy)
// use App\Models\Assinatura; // (adicione quando criar a policy)

use App\Policies\UserPolicy;
use App\Policies\ClientePolicy;
use App\Policies\PlanoPolicy;
// use App\Policies\FaturaPolicy;
// use App\Policies\AssinaturaPolicy;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class    => UserPolicy::class,
        Cliente::class => ClientePolicy::class,
        Plano::class   => PlanoPolicy::class,
        // Fatura::class     => FaturaPolicy::class,
        // Assinatura::class => AssinaturaPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
