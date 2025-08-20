<?php

namespace App\Services;

use App\Models\Cep;
use Illuminate\Support\Facades\Http;

class CepResolver
{
    /**
     * Retorna ['cep','state','city','neighborhood','street'] ou null.
     * - Normaliza para 8 dígitos
     * - Lê cache (tabela cep)
     * - Se não houver, consulta ViaCEP, grava e retorna
     */
    public function resolve(?string $rawCep): ?array
    {
        if (! $rawCep) return null;

        $cep = preg_replace('/\D/', '', $rawCep);
        if (strlen($cep) !== 8) return null;

        if ($cached = Cep::where('cep', $cep)->first()) {
            return $cached->only(['cep','state','city','neighborhood','street']);
        }

        $r = Http::timeout(6)->get("https://viacep.com.br/ws/{$cep}/json/");

        if (! $r->ok() || $r->json('erro')) {
            return null;
        }

        $data = [
            'cep'          => $cep,
            'state'        => $r->json('uf'),
            'city'         => $r->json('localidade'),
            'neighborhood' => $r->json('bairro'),
            'street'       => $r->json('logradouro'),
        ];

        Cep::updateOrCreate(['cep' => $cep], $data);

        return $data;
    }
}
