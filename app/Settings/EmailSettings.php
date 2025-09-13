<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class EmailSettings extends Settings
{
    // Fixo: usaremos SMTP apenas
    public string  $driver        = 'smtp';

    // Remetente
    public ?string $from_address  = null;
    public ?string $from_name     = null;
    public ?string $reply_to      = null; // opcional
    public ?string $bcc           = null; // opcional (separe por vírgula)

    // SMTP
    public string  $host          = '';
    public int     $port          = 587;
    public ?string $encryption    = 'tls';   // 'tls' | 'ssl' | null
    public ?string $username      = null;
    public ?string $password      = null;    // será armazenado criptografado

    public static function group(): string
    {
        return 'email';
    }

    public static function defaults(): array
    {
        return [
            'driver'        => 'smtp',
            'from_address'  => null,
            'from_name'     => null,
            'reply_to'      => null,
            'bcc'           => null,

            'host'          => '',
            'port'          => 587,
            'encryption'    => 'tls',
            'username'      => null,
            'password'      => null,
        ];
    }
}
