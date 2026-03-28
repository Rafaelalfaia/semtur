<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCoordenadorAreaPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user, 403);

        // Só Admin tem bypass total
        if ($user->hasRole('Admin')) {
            return $next($request);
        }

        // Dashboard e perfil continuam acessíveis
        if ($this->isAlwaysAllowed($request)) {
            return $next($request);
        }

        $permission = $this->resolvePermission($request);

        // deny by default
        if (!$permission || !$user->can($permission)) {
            abort(403);
        }

        $this->guardStatusTransition($request, $user);

        return $next($request);
    }

    private function isAlwaysAllowed(Request $request): bool
    {
        return $request->routeIs(
            'coordenador.dashboard',
            'coordenador.config.*'
        );
    }

    private function resolvePermission(Request $request): ?string
    {
        // Categorias
        if ($request->routeIs('coordenador.categorias.index')) return 'categorias.view';
        if ($request->routeIs('coordenador.categorias.create', 'coordenador.categorias.store')) return 'categorias.create';
        if ($request->routeIs('coordenador.categorias.edit', 'coordenador.categorias.update', 'coordenador.categorias.icone.remover')) return 'categorias.update';
        if ($request->routeIs('coordenador.categorias.destroy')) return 'categorias.delete';
        if ($request->routeIs('coordenador.categorias.publicar')) return 'categorias.publicar';
        if ($request->routeIs('coordenador.categorias.arquivar')) return 'categorias.arquivar';
        if ($request->routeIs('coordenador.categorias.rascunho')) return 'categorias.rascunho';

        // Empresas
        if ($request->routeIs('coordenador.empresas.index')) return 'empresas.view';
        if ($request->routeIs('coordenador.empresas.create', 'coordenador.empresas.store')) return 'empresas.create';
        if ($request->routeIs(
            'coordenador.empresas.edit',
            'coordenador.empresas.update',
            'coordenador.empresas.capa.remover',
            'coordenador.empresas.perfil.remover',
            'coordenador.empresas.removerCapa',
            'coordenador.empresas.removerPerfil'
        )) return 'empresas.update';
        if ($request->routeIs('coordenador.empresas.destroy')) return 'empresas.delete';
        if ($request->routeIs('coordenador.empresas.publicar')) return 'empresas.publicar';
        if ($request->routeIs('coordenador.empresas.arquivar')) return 'empresas.arquivar';
        if ($request->routeIs('coordenador.empresas.rascunho')) return 'empresas.rascunho';

        // recomendações de empresa = privilégio editorial forte
        if ($request->routeIs(
            'coordenador.empresas.recomendar',
            'coordenador.empresas.recomendar.remover',
            'coordenador.empresas.recomendar.ordem',
            'coordenador.empresas.recomendar.ordenar'
        )) return 'empresas.publicar';

        // Pontos turísticos
        if ($request->routeIs('coordenador.pontos.index')) return 'pontos.view';
        if ($request->routeIs('coordenador.pontos.create', 'coordenador.pontos.store')) return 'pontos.create';
        if ($request->routeIs(
            'coordenador.pontos.edit',
            'coordenador.pontos.update',
            'coordenador.pontos.capa.remover',
            'coordenador.pontos.removerCapa',
            'coordenador.pontos.midias.imagens.add',
            'coordenador.pontos.midias.video.link',
            'coordenador.pontos.midias.video.file',
            'coordenador.pontos.midias.destroy'
        )) return 'pontos.update';
        if ($request->routeIs('coordenador.pontos.destroy')) return 'pontos.delete';
        if ($request->routeIs('coordenador.pontos.publicar')) return 'pontos.publicar';
        if ($request->routeIs('coordenador.pontos.arquivar')) return 'pontos.arquivar';
        if ($request->routeIs('coordenador.pontos.rascunho')) return 'pontos.rascunho';

        // recomendações de ponto = privilégio editorial forte
        if ($request->routeIs(
            'coordenador.pontos.recomendar',
            'coordenador.pontos.recomendar.remover',
            'coordenador.pontos.recomendar.ordem',
            'coordenador.pontos.recomendar.ordenar'
        )) return 'pontos.publicar';

        // Banners
        if ($request->routeIs('coordenador.banners.index')) return 'banners.view';
        if ($request->routeIs(
            'coordenador.banners.create',
            'coordenador.banners.store',
            'coordenador.banners.edit',
            'coordenador.banners.update',
            'coordenador.banners.destroy'
        )) return 'banners.manage';

        // Banners destaque
        if ($request->routeIs('coordenador.banners-destaque.index')) return 'banners_destaque.view';
        if ($request->routeIs(
            'coordenador.banners-destaque.create',
            'coordenador.banners-destaque.store',
            'coordenador.banners-destaque.edit',
            'coordenador.banners-destaque.update',
            'coordenador.banners-destaque.destroy'
        )) return 'banners_destaque.manage';
        if ($request->routeIs('coordenador.banners-destaque.toggle')) return 'banners_destaque.toggle';
        if ($request->routeIs('coordenador.banners-destaque.reordenar')) return 'banners_destaque.reordenar';

        // Avisos
        if ($request->routeIs('coordenador.avisos.index')) return 'avisos.view';
        if ($request->routeIs(
            'coordenador.avisos.create',
            'coordenador.avisos.store',
            'coordenador.avisos.edit',
            'coordenador.avisos.update',
            'coordenador.avisos.destroy',
            'coordenador.avisos.imagem.remover'
        )) return 'avisos.manage';
        if ($request->routeIs('coordenador.avisos.publicar')) return 'avisos.publicar';
        if ($request->routeIs('coordenador.avisos.arquivar')) return 'avisos.arquivar';

        // Eventos
        if ($request->routeIs('coordenador.eventos.index')) return 'eventos.view';
        if ($request->routeIs(
            'coordenador.eventos.create',
            'coordenador.eventos.store',
            'coordenador.eventos.edit',
            'coordenador.eventos.update',
            'coordenador.eventos.destroy'
        )) return 'eventos.manage';

        // Edições
        if ($request->routeIs(
            'coordenador.eventos.edicoes.index',
            'coordenador.eventos.edicoes.create',
            'coordenador.eventos.edicoes.store',
            'coordenador.edicoes.edit',
            'coordenador.edicoes.update',
            'coordenador.edicoes.destroy'
        )) return 'eventos.edicoes.manage';

        // Atrativos
        if ($request->routeIs(
            'coordenador.edicoes.atrativos.index',
            'coordenador.edicoes.atrativos.create',
            'coordenador.edicoes.atrativos.store',
            'coordenador.atrativos.edit',
            'coordenador.atrativos.update',
            'coordenador.atrativos.destroy'
        )) return 'eventos.atrativos.manage';
        if ($request->routeIs('coordenador.edicoes.atrativos.reordenar')) return 'eventos.atrativos.reordenar';

        // Mídias de evento
        if ($request->routeIs(
            'coordenador.edicoes.midias.index',
            'coordenador.edicoes.midias.store',
            'coordenador.midias.destroy'
        )) return 'eventos.midias.manage';
        if ($request->routeIs('coordenador.edicoes.midias.reordenar')) return 'eventos.midias.reordenar';

        // Espaços culturais
        if ($request->routeIs('coordenador.espacos-culturais.index')) return 'espacos_culturais.view';
        if ($request->routeIs('coordenador.espacos-culturais.create', 'coordenador.espacos-culturais.store')) return 'espacos_culturais.create';
        if ($request->routeIs('coordenador.espacos-culturais.edit', 'coordenador.espacos-culturais.update')) return 'espacos_culturais.update';
        if ($request->routeIs('coordenador.espacos-culturais.destroy')) return 'espacos_culturais.delete';

        if ($request->routeIs(
            'coordenador.espacos-culturais.agendamentos.index',
            'coordenador.espacos-culturais.agendamentos.show'
        )) return 'espacos_culturais.view';

        if ($request->routeIs(
            'coordenador.espacos-culturais.agendamentos.confirmar',
            'coordenador.espacos-culturais.agendamentos.cancelar',
            'coordenador.espacos-culturais.agendamentos.concluir',
            'coordenador.espacos-culturais.agendamentos.atribuir-tecnico',
            'coordenador.espacos-culturais.agendamentos.observacao-interna'
        )) return 'espacos_culturais.update';


        if ($request->routeIs('coordenador.onde_ficar.edit')) return 'onde_ficar.view';
        if ($request->routeIs('coordenador.onde_ficar.update')) return 'onde_ficar.update';

        if ($request->routeIs('coordenador.onde_comer.edit')) return 'onde_comer.view';
        if ($request->routeIs('coordenador.onde_comer.update')) return 'onde_comer.update';

        // Roteiros
        if ($request->routeIs('coordenador.roteiros.index')) return 'roteiros.view';
        if ($request->routeIs('coordenador.roteiros.create', 'coordenador.roteiros.store')) return 'roteiros.create';
        if ($request->routeIs('coordenador.roteiros.edit', 'coordenador.roteiros.update')) return 'roteiros.update';
        if ($request->routeIs('coordenador.roteiros.destroy')) return 'roteiros.delete';
        if ($request->routeIs('coordenador.roteiros.publicar')) return 'roteiros.publicar';
        if ($request->routeIs('coordenador.roteiros.arquivar')) return 'roteiros.arquivar';
        if ($request->routeIs('coordenador.roteiros.rascunho')) return 'roteiros.rascunho';

        // Institucional / equipe / relatórios / técnicos
        if ($request->routeIs('coordenador.secretaria.*')) return 'secretaria.edit';
        if ($request->routeIs('coordenador.equipe.*')) return 'equipe.manage';
        if ($request->routeIs('coordenador.coord.relatorios.*')) return 'relatorios.view';
        if ($request->routeIs('coordenador.tecnicos.*')) return 'tecnicos.manage';

        // Guias e Revistas
        if ($request->routeIs('coordenador.guias.index')) return 'guias.view';
        if ($request->routeIs('coordenador.guias.create', 'coordenador.guias.store')) return 'guias.create';
        if ($request->routeIs('coordenador.guias.edit', 'coordenador.guias.update')) return 'guias.update';
        if ($request->routeIs('coordenador.guias.destroy')) return 'guias.delete';
        if ($request->routeIs('coordenador.guias.publicar')) return 'guias.publicar';
        if ($request->routeIs('coordenador.guias.arquivar')) return 'guias.arquivar';
        if ($request->routeIs('coordenador.guias.rascunho')) return 'guias.rascunho';

        //Vídeos
        if ($request->routeIs('coordenador.videos.index')) return 'videos.view';
        if ($request->routeIs('coordenador.videos.create', 'coordenador.videos.store')) return 'videos.create';
        if ($request->routeIs('coordenador.videos.edit', 'coordenador.videos.update')) return 'videos.update';
        if ($request->routeIs('coordenador.videos.destroy')) return 'videos.delete';
        if ($request->routeIs('coordenador.videos.publicar')) return 'videos.publicar';
        if ($request->routeIs('coordenador.videos.arquivar')) return 'videos.arquivar';
        if ($request->routeIs('coordenador.videos.rascunho')) return 'videos.rascunho';

        // Jogos Indígenas
        if ($request->routeIs('coordenador.jogos-indigenas.index')) return 'jogos_indigenas.view';
        if ($request->routeIs('coordenador.jogos-indigenas.create', 'coordenador.jogos-indigenas.store')) return 'jogos_indigenas.create';
        if ($request->routeIs('coordenador.jogos-indigenas.edit', 'coordenador.jogos-indigenas.update')) return 'jogos_indigenas.update';
        if ($request->routeIs('coordenador.jogos-indigenas.destroy')) return 'jogos_indigenas.delete';

        // Edições de Jogos Indígenas
        if ($request->routeIs('coordenador.jogos-indigenas.edicoes.index')) return 'jogos_indigenas.edicoes.view';
        if ($request->routeIs('coordenador.jogos-indigenas.edicoes.create', 'coordenador.jogos-indigenas.edicoes.store')) return 'jogos_indigenas.edicoes.create';
        if ($request->routeIs('coordenador.jogos-indigenas.edicoes.edit', 'coordenador.jogos-indigenas.edicoes.update')) return 'jogos_indigenas.edicoes.update';
        if ($request->routeIs('coordenador.jogos-indigenas.edicoes.destroy')) return 'jogos_indigenas.edicoes.delete';

        if ($request->routeIs('coordenador.jogos-indigenas.edicoes.fotos.index')) return 'jogos_indigenas.edicoes.fotos.view';
        if ($request->routeIs('coordenador.jogos-indigenas.edicoes.fotos.create', 'coordenador.jogos-indigenas.edicoes.fotos.store')) return 'jogos_indigenas.edicoes.fotos.create';
        if ($request->routeIs('coordenador.jogos-indigenas.edicoes.fotos.edit', 'coordenador.jogos-indigenas.edicoes.fotos.update')) return 'jogos_indigenas.edicoes.fotos.update';
        if ($request->routeIs('coordenador.jogos-indigenas.edicoes.fotos.destroy')) return 'jogos_indigenas.edicoes.fotos.delete';
        if ($request->routeIs('coordenador.jogos-indigenas.edicoes.videos.index')) return 'jogos_indigenas.edicoes.videos.view';
        if ($request->routeIs('coordenador.jogos-indigenas.edicoes.videos.create', 'coordenador.jogos-indigenas.edicoes.videos.store')) return 'jogos_indigenas.edicoes.videos.create';
        if ($request->routeIs('coordenador.jogos-indigenas.edicoes.videos.edit', 'coordenador.jogos-indigenas.edicoes.videos.update')) return 'jogos_indigenas.edicoes.videos.update';
        if ($request->routeIs('coordenador.jogos-indigenas.edicoes.videos.destroy')) return 'jogos_indigenas.edicoes.videos.delete';
        if ($request->routeIs('coordenador.jogos-indigenas.edicoes.patrocinadores.index')) return 'jogos_indigenas.edicoes.patrocinadores.view';
        if ($request->routeIs('coordenador.jogos-indigenas.edicoes.patrocinadores.create', 'coordenador.jogos-indigenas.edicoes.patrocinadores.store')) return 'jogos_indigenas.edicoes.patrocinadores.create';
        if ($request->routeIs('coordenador.jogos-indigenas.edicoes.patrocinadores.edit', 'coordenador.jogos-indigenas.edicoes.patrocinadores.update')) return 'jogos_indigenas.edicoes.patrocinadores.update';
        if ($request->routeIs('coordenador.jogos-indigenas.edicoes.patrocinadores.destroy')) return 'jogos_indigenas.edicoes.patrocinadores.delete';

        // Rota do Cacau
        if ($request->routeIs('coordenador.rota-do-cacau.index')) return 'rota_do_cacau.view';
        if ($request->routeIs('coordenador.rota-do-cacau.create', 'coordenador.rota-do-cacau.store')) return 'rota_do_cacau.create';
        if ($request->routeIs('coordenador.rota-do-cacau.edit', 'coordenador.rota-do-cacau.update')) return 'rota_do_cacau.update';
        if ($request->routeIs('coordenador.rota-do-cacau.destroy')) return 'rota_do_cacau.delete';

        if ($request->routeIs('coordenador.rota-do-cacau.edicoes.index')) return 'rota_do_cacau.edicoes.view';
        if ($request->routeIs('coordenador.rota-do-cacau.edicoes.create', 'coordenador.rota-do-cacau.edicoes.store')) return 'rota_do_cacau.edicoes.create';
        if ($request->routeIs('coordenador.rota-do-cacau.edicoes.edit', 'coordenador.rota-do-cacau.edicoes.update')) return 'rota_do_cacau.edicoes.update';
        if ($request->routeIs('coordenador.rota-do-cacau.edicoes.destroy')) return 'rota_do_cacau.edicoes.delete';

        if ($request->routeIs('coordenador.rota-do-cacau.edicoes.fotos.index')) return 'rota_do_cacau.edicoes.fotos.view';
        if ($request->routeIs('coordenador.rota-do-cacau.edicoes.fotos.create', 'coordenador.rota-do-cacau.edicoes.fotos.store')) return 'rota_do_cacau.edicoes.fotos.create';
        if ($request->routeIs('coordenador.rota-do-cacau.edicoes.fotos.edit', 'coordenador.rota-do-cacau.edicoes.fotos.update')) return 'rota_do_cacau.edicoes.fotos.update';
        if ($request->routeIs('coordenador.rota-do-cacau.edicoes.fotos.destroy')) return 'rota_do_cacau.edicoes.fotos.delete';
        if ($request->routeIs('coordenador.rota-do-cacau.edicoes.videos.index')) return 'rota_do_cacau.edicoes.videos.view';
        if ($request->routeIs('coordenador.rota-do-cacau.edicoes.videos.create', 'coordenador.rota-do-cacau.edicoes.videos.store')) return 'rota_do_cacau.edicoes.videos.create';
        if ($request->routeIs('coordenador.rota-do-cacau.edicoes.videos.edit', 'coordenador.rota-do-cacau.edicoes.videos.update')) return 'rota_do_cacau.edicoes.videos.update';
        if ($request->routeIs('coordenador.rota-do-cacau.edicoes.videos.destroy')) return 'rota_do_cacau.edicoes.videos.delete';
        if ($request->routeIs('coordenador.rota-do-cacau.edicoes.patrocinadores.index')) return 'rota_do_cacau.edicoes.patrocinadores.view';
        if ($request->routeIs('coordenador.rota-do-cacau.edicoes.patrocinadores.create', 'coordenador.rota-do-cacau.edicoes.patrocinadores.store')) return 'rota_do_cacau.edicoes.patrocinadores.create';
        if ($request->routeIs('coordenador.rota-do-cacau.edicoes.patrocinadores.edit', 'coordenador.rota-do-cacau.edicoes.patrocinadores.update')) return 'rota_do_cacau.edicoes.patrocinadores.update';
        if ($request->routeIs('coordenador.rota-do-cacau.edicoes.patrocinadores.destroy')) return 'rota_do_cacau.edicoes.patrocinadores.delete';


        // Temas
        if ($request->routeIs('coordenador.temas.index')) return 'themes.view';

        if ($request->routeIs(
            'coordenador.temas.preview-console',
            'coordenador.temas.preview-console.clear'
        )) return 'themes.preview';

        if ($request->routeIs(
            'coordenador.temas.activate-console',
            'coordenador.temas.restore-default-console'
        )) return 'themes.execute.console';

        if ($request->routeIs(
            'coordenador.temas.activate-site',
            'coordenador.temas.restore-default-site'
        )) return 'themes.execute.site';
        return null;
    }

    private function guardStatusTransition(Request $request, $user): void
    {
        if (!$request->has('status')) {
            return;
        }

        if ($request->routeIs('coordenador.roteiros.store', 'coordenador.roteiros.update')) {
            $this->ensureStatusAllowed(
                request: $request,
                user: $user,
                prefix: 'roteiros',
                currentStatus: optional($request->route('roteiro'))->status,
                defaultStatus: 'rascunho',
            );
            return;
        }

        if ($request->routeIs('coordenador.categorias.store', 'coordenador.categorias.update')) {
            $this->ensureStatusAllowed(
                request: $request,
                user: $user,
                prefix: 'categorias',
                currentStatus: optional($request->route('categoria'))->status,
                defaultStatus: 'rascunho',
            );
            return;
        }

        if ($request->routeIs('coordenador.empresas.store', 'coordenador.empresas.update')) {
            $this->ensureStatusAllowed(
                request: $request,
                user: $user,
                prefix: 'empresas',
                currentStatus: optional($request->route('empresa'))->status,
                defaultStatus: 'rascunho',
            );
            return;
        }

        if ($request->routeIs('coordenador.pontos.store', 'coordenador.pontos.update')) {
            $this->ensureStatusAllowed(
                request: $request,
                user: $user,
                prefix: 'pontos',
                currentStatus: optional($request->route('ponto'))->status,
                defaultStatus: 'rascunho',
            );
            return;
        }

        if ($request->routeIs('coordenador.avisos.store', 'coordenador.avisos.update')) {
            $this->ensureStatusAllowed(
                request: $request,
                user: $user,
                prefix: 'avisos',
                currentStatus: optional($request->route('aviso'))->status,
                defaultStatus: 'rascunho',
            );
            return;
        }

        if ($request->routeIs('coordenador.espacos-culturais.store', 'coordenador.espacos-culturais.update')) {
            $this->ensureStatusAllowed(
                request: $request,
                user: $user,
                prefix: 'espacos_culturais',
                currentStatus: optional($request->route('espaco'))->status,
                defaultStatus: 'rascunho',
            );
        }

        if ($request->routeIs('coordenador.onde_ficar.update')) {
            $this->ensureStatusAllowed(
                request: $request,
                user: $user,
                prefix: 'onde_ficar',
                currentStatus: optional(\App\Models\Conteudo\OndeFicarPagina::query()->first())->status,
                defaultStatus: 'rascunho',
            );
            return;
        }

        if ($request->routeIs('coordenador.onde_comer.update')) {
            $this->ensureStatusAllowed(
                request: $request,
                user: $user,
                prefix: 'onde_comer',
                currentStatus: optional(\App\Models\Conteudo\OndeComerPagina::query()->first())->status,
                defaultStatus: 'rascunho',
            );
            return;
        }

        if ($request->routeIs('coordenador.guias.store', 'coordenador.guias.update')) {
            $this->ensureStatusAllowed(
                request: $request,
                user: $user,
                prefix: 'guias',
                currentStatus: optional($request->route('guia'))->status,
                defaultStatus: 'rascunho',
            );
            return;
        }

        if ($request->routeIs('coordenador.videos.store', 'coordenador.videos.update')) {
            $this->ensureStatusAllowed(
                request: $request,
                user: $user,
                prefix: 'videos',
                currentStatus: optional($request->route('video'))->status,
                defaultStatus: 'rascunho',
            );
            return;
        }

        if ($request->routeIs('coordenador.jogos-indigenas.store', 'coordenador.jogos-indigenas.update')) {
            $this->ensureStatusAllowed(
                request: $request,
                user: $user,
                prefix: 'jogos_indigenas',
                currentStatus: optional($request->route('jogosIndigena'))->status,
                defaultStatus: 'rascunho',
            );
            return;
        }

        if ($request->routeIs('coordenador.jogos-indigenas.edicoes.store', 'coordenador.jogos-indigenas.edicoes.update')) {
            $this->ensureStatusAllowed(
                request: $request,
                user: $user,
                prefix: 'jogos_indigenas.edicoes',
                currentStatus: optional($request->route('edicao'))->status,
                defaultStatus: 'rascunho',
            );
            return;
        }

        if ($request->routeIs('coordenador.rota-do-cacau.store', 'coordenador.rota-do-cacau.update')) {
            $this->ensureStatusAllowed(
                request: $request,
                user: $user,
                prefix: 'rota_do_cacau',
                currentStatus: optional($request->route('rotaDoCacau'))->status,
                defaultStatus: 'rascunho',
            );
            return;
        }

        if ($request->routeIs('coordenador.rota-do-cacau.edicoes.store', 'coordenador.rota-do-cacau.edicoes.update')) {
            $this->ensureStatusAllowed(
                request: $request,
                user: $user,
                prefix: 'rota_do_cacau.edicoes',
                currentStatus: optional($request->route('edicao'))->status,
                defaultStatus: 'rascunho',
            );
            return;
        }

    }

    private function ensureStatusAllowed(
        Request $request,
        $user,
        string $prefix,
        ?string $currentStatus = null,
        string $defaultStatus = 'rascunho'
    ): void {
        $requested = (string) $request->input('status', '');

        if ($requested === '') {
            return;
        }

        $allowed = [];

        // mantém o status atual em updates, mesmo sem poder transicionar
        if ($currentStatus) {
            $allowed[] = $currentStatus;
        } else {
            $allowed[] = $defaultStatus;
        }

        if ($user->can("{$prefix}.publicar")) {
            $allowed[] = 'publicado';
        }

        if ($user->can("{$prefix}.arquivar")) {
            $allowed[] = 'arquivado';
        }

        if ($user->can("{$prefix}.rascunho")) {
            $allowed[] = 'rascunho';
        }

        $allowed = array_values(array_unique(array_filter($allowed)));

        if (!in_array($requested, $allowed, true)) {
            abort(403, 'Você não tem permissão para alterar o status deste conteúdo.');
        }
    }
}
