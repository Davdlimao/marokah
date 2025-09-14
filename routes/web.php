<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConviteController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/convites/{convite}/{token}', [ConviteController::class, 'mostrar'])
    ->name('convites.mostrar'); // retirado middleware('signed')

Route::post('/convites/{convite}/{token}', [ConviteController::class, 'concluir'])
    ->name('convites.concluir'); // idem