<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConviteController;
use App\Http\Controllers\SenhaController;

    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/convites/{convite}/{token}', [ConviteController::class, 'mostrar'])
        ->name('convites.mostrar'); // retirado middleware('signed')

    Route::post('/convites/{convite}/{token}', [ConviteController::class, 'concluir'])
        ->name('convites.concluir'); // idem

    Route::middleware(['web','auth'])    // precisa estar logado
    ->prefix('marokah')              // mantém dentro do mesmo prefixo do painel
    ->group(function () {
        Route::get('alterar-senha',  [SenhaController::class, 'show'])
            ->name('senha.alterar');
        Route::post('alterar-senha', [SenhaController::class, 'update'])
            ->name('senha.alterar.salvar');
    });