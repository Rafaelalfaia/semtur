<?php

use App\Services\InstagramTokenStore;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('instagram:refresh', function () {
    $this->info('Atualizando token do Instagram...');
    $token = InstagramTokenStore::getToken();

    if (! $token) {
        $this->error('Nenhum token encontrado. Defina o primeiro em IG_ACCESS_TOKEN no .env');
        return 1;
    }

    $resp = Http::timeout(12)->get('https://graph.instagram.com/refresh_access_token', [
        'grant_type' => 'ig_refresh_token',
        'access_token' => $token,
    ]);

    if (! $resp->ok()) {
        $this->error('Falha HTTP '.$resp->status());
        $this->line($resp->body());
        return 1;
    }

    $new = $resp->json('access_token');
    $exp = (int) $resp->json('expires_in', 0);

    if (! $new) {
        $this->error('Resposta sem access_token.');
        return 1;
    }

    InstagramTokenStore::put($new, $exp);
    $this->info('Token atualizado e salvo em storage/app/ig_token.json');

    return 0;
})->purpose('Atualiza o long-lived token do Instagram Basic Display');

Schedule::command('instagram:refresh')
    ->monthlyOn(1, '03:00')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('media:cleanup --report=media-cleanup/latest.json')
    ->weeklyOn(0, '03:30')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('media:cleanup --prune-temp --prune-optimized --prune-reports --days='.(int) env('MEDIA_CLEANUP_SAFE_DAYS', 15).' --report=media-cleanup/latest.json')
    ->dailyAt('03:45')
    ->withoutOverlapping()
    ->onOneServer();

if (filter_var(env('MEDIA_CLEANUP_AUTO_QUARANTINE', false), FILTER_VALIDATE_BOOL)) {
    Schedule::command('media:cleanup --quarantine --report=media-cleanup/latest.json')
        ->weeklyOn(0, env('MEDIA_CLEANUP_AUTO_QUARANTINE_AT', '04:10'))
        ->withoutOverlapping()
        ->onOneServer();
}

if (filter_var(env('MEDIA_CLEANUP_AUTO_PURGE', false), FILTER_VALIDATE_BOOL)) {
    Schedule::command('media:cleanup --purge --days='.(int) env('MEDIA_CLEANUP_PURGE_DAYS', 15).' --report=media-cleanup/latest.json')
        ->dailyAt(env('MEDIA_CLEANUP_AUTO_PURGE_AT', '04:25'))
        ->withoutOverlapping()
        ->onOneServer();
}


if (filter_var(env('BACKUP_AUTO_ENABLED', false), FILTER_VALIDATE_BOOL)) {
    Schedule::command('backup:run')
        ->dailyAt(env('BACKUP_TIME', '02:30'))
        ->withoutOverlapping()
        ->onOneServer();
}
