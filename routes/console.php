<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Http;
use App\Services\InstagramTokenStore;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Comando: instagram:refresh
Artisan::command('instagram:refresh', function () {
    $this->info('Atualizando token do Instagram...');
    $token = InstagramTokenStore::getToken();

    if (!$token) {
        $this->error('Nenhum token encontrado. Defina o primeiro em IG_ACCESS_TOKEN no .env');
        return 1;
    }

    $resp = Http::timeout(12)->get('https://graph.instagram.com/refresh_access_token', [
        'grant_type'   => 'ig_refresh_token',
        'access_token' => $token,
    ]);

    if (!$resp->ok()) {
        $this->error('Falha HTTP '.$resp->status());
        $this->line($resp->body());
        return 1;
    }

    $new = $resp->json('access_token');
    $exp = (int) $resp->json('expires_in', 0);

    if (!$new) {
        $this->error('Resposta sem access_token.');
        return 1;
    }

    \App\Services\InstagramTokenStore::put($new, $exp);
    $this->info('Token atualizado e salvo em storage/app/ig_token.json');

    return 0;
})->purpose('Atualiza o long-lived token do Instagram Basic Display');

// Schedule (roda no 1º dia do mês às 03:00)
Schedule::command('instagram:refresh')
    ->monthlyOn(1, '03:00')
    ->withoutOverlapping()
    ->onOneServer();
