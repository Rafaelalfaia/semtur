<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class WarmupHomeCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Aqui você só precisa chamar os mesmos métodos/queries
        // que a API/Controller usam com Cache::remember(...).
        // Ex.: tocar uma rota interna (Http::get) ou chamar um serviço
        // que rode as remembers. Vamos ligar isso na Etapa 3.
    }
}
