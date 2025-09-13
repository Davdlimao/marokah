<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class EmailSettings extends Settings
{
    public string $driver = 'smtp';
    public string $host = '';
    public int $port = 587;
    public ?string $username = null; // opcional
    public ?string $password = null; // opcional
    public bool $tls = true;
    public ?string $from_address = null; // opcional
    public ?string $from_name = null; // opcional
    public ?string $reply_to = null; // novo campo opcional
    public ?string $bcc = null; // novo campo opcional

    public static function group(): string
    {
        return 'email';
    }

    public static function defaults(): array
    {
        return [
            'driver'       => 'smtp',
            'host'         => '',
            'port'         => 587,
            'username'     => null,
            'password'     => null,
            'tls'          => true,
            'from_address' => null,
            'from_name'    => null,
            'reply_to'     => null,
            'bcc'          => null,
        ];
    }
}
