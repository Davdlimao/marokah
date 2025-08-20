<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CnpjLookup
{
    /**
     * @return array{razao_social?:string,nome_fantasia?:string,cep?:string,logradouro?:string,bairro?:string,municipio?:string,uf?:string}|null
     */
    public function fetch(string $cnpj): ?array
    {
        $cnpj = preg_replace('/\D+/', '', $cnpj);
        if (strlen($cnpj) !== 14) return null;

        try {
            $res = Http::timeout(6)->acceptJson()->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");
            if (!$res->successful()) return null;

            $data = $res->json();

            return [
                'razao_social'   => $data['razao_social']   ?? null,
                'nome_fantasia'  => $data['nome_fantasia']  ?? null,
                'cep'            => $data['cep']            ?? null,
                'logradouro'     => $data['logradouro']     ?? null,
                'bairro'         => $data['bairro']         ?? null,
                'municipio'      => $data['municipio']      ?? null,
                'uf'             => $data['uf']             ?? null,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
