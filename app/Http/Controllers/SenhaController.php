<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SenhaController extends Controller
{
    public function show(Request $request)
    {
        return view('auth.alterar-senha');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'password' => ['required','string','min:8','confirmed'],
        ]);

        $user = $request->user();

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
        ])->save();

        return redirect()->route('filament.marokah.pages.dashboard')
            ->with('status', 'Senha alterada com sucesso.');
    }
}
