<?php

namespace App\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmailRuntime
{
    /**
     * Aplica as configurações de e-mail no runtime.
     * Aceita:
     *  - password em texto (não recomendado)
     *  - password com prefixo "enc:" (conteúdo de encrypt())
     *  - password_encrypted (conteúdo de encrypt())
     */
    public static function apply(array $cfg): void
    {
        // ====== resolve senha ======
        $password = null;

        // prioridade 1: password_encrypted
        if (!empty($cfg['password_encrypted'])) {
            try {
                $password = decrypt($cfg['password_encrypted']);
            } catch (\Throwable $e) {
                $password = null;
            }
        }

        // prioridade 2: password (enc:... ou texto puro)
        if (!$password && !empty($cfg['password'])) {
            $p = (string) $cfg['password'];
            if (str_starts_with($p, 'enc:')) {
                try {
                    $password = decrypt(substr($p, 4));
                } catch (\Throwable $e) {
                    $password = null;
                }
            } else {
                $password = $p; // texto puro (desencorajado, mas suportado)
            }
        }

        // normalizações
        $driver     = $cfg['driver']      ?? 'smtp';
        $host       = $cfg['host']        ?? null;
        $port       = isset($cfg['port']) ? (int) $cfg['port'] : 587;
        $encryption = $cfg['encryption']  ?? 'tls';
        if ($encryption === 'none') {
            $encryption = null;
        }

        // remetente (muitos provedores exigem mesmo e-mail do username)
        if (!empty($cfg['from_address'])) {
            Config::set('mail.from.address', $cfg['from_address']);
            Config::set('mail.from.name',    $cfg['from_name'] ?? config('app.name'));
        }

        // driver
        Config::set('mail.default', $driver);

        // SMTP
        Config::set('mail.mailers.smtp', array_filter([
            'transport'  => 'smtp',
            'host'       => $host,
            'port'       => $port,
            'encryption' => $encryption,
            'username'   => $cfg['username'] ?? null,
            'password'   => $password,
            'timeout'    => null,
            'auth_mode'  => null,
        ], fn ($v) => $v !== null));

        // Reply-To / BCC globais (se quiser manter)
        if (!empty($cfg['reply_to'])) {
            app('mailer')->alwaysReplyTo($cfg['reply_to']);
        }
        if (!empty($cfg['bcc'])) {
            $bccs = collect(explode(',', $cfg['bcc']))->map(fn($e) => trim($e))->filter()->values();
            foreach ($bccs as $b) {
                app('mailer')->alwaysBcc($b);
            }
        }

        // (opcional) log leve p/ depurar (sem vazar senha)
         Log::info('SMTP runtime', [
             'default'   => config('mail.default'),
             'host'      => config('mail.mailers.smtp.host'),
             'port'      => config('mail.mailers.smtp.port'),
             'enc'       => config('mail.mailers.smtp.encryption'),
             'username'  => config('mail.mailers.smtp.username'),
             'from'      => config('mail.from.address').' ('.config('mail.from.name').')',
         ]);
    }

    /**
     * Busca do BD em `settings (group=email, name=default)` e aplica.
     */
    public static function applyFromDb(): void
    {
        $row = DB::table('settings')
            ->where('group', 'email')
            ->where('name', 'default')
            ->value('payload');

        $cfg = $row ? (array) json_decode($row, true) : [];
        self::apply($cfg);
    }
}
