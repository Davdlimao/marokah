<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class SenhaTemporariaMail extends Mailable
{
    public string $nome;
    public string $senha;

    public function __construct(string $nome, string $senha)
    {
        $this->nome = $nome;
        $this->senha = $senha;
    }

    public function build()
    {
        return $this->subject('Acesso provisório')
            ->view('emails.usuarios.senha-temporaria');
    }
}
