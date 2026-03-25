@extends('console.layout')

@section('title', 'Edicoes - '.$rota->titulo)
@section('page.title', 'Edicoes da Rota do Cacau')
@section('topbar.description', 'Gerencie as edicoes do cadastro principal e acesse os submodulos de fotos, videos e patrocinadores.')

@section('topbar.nav')
  <a href="{{ route('coordenador.rota-do-cacau.index') }}" class="ui-console-topbar-tab">Rota do Cacau</a>
  <span class="ui-console-topbar-tab is-active">Edicoes</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header
    title="Edicoes"
    subtitle="Cadastre anos, capas, publicacao e acesse os conteudos complementares de cada edicao."
  >
    <x-slot:actions>
      <div class="flex flex-wrap gap-2">
        @can('rota_do_cacau.edicoes.create')
          <a href="{{ route('coordenador.rota-do-cacau.edicoes.create', $rota) }}" class="ui-btn-primary">Nova edicao</a>
        @endcan
        <a href="{{ route('coordenador.rota-do-cacau.edit', $rota) }}" class="ui-btn-secondary">Voltar ao cadastro</a>
      </div>
    </x-slot:actions>
  </x-dashboard.page-header>

  <div class="mt-2 text-sm text-[var(--ui-text-soft)]">/rota-do-cacau/{{ $rota->slug }}</div>

  <x-dashboard.section-card title="Lista de edicoes" subtitle="Cada edicao concentra sua propria galeria, videos e patrocinadores." class="ui-coord-dashboard-panel mt-5">
    <div class="ui-table-shell">
      <table class="min-w-full text-sm">
        <thead class="ui-table-head">
          <tr>
            <th class="px-3 py-3 text-left">Ano</th>
            <th class="px-3 py-3 text-left">Titulo</th>
            <th class="px-3 py-3 text-left">Status</th>
            <th class="px-3 py-3 text-left">Publicado em</th>
            <th class="px-3 py-3 text-left">Capa</th>
            <th class="px-3 py-3 text-left">Fotos</th>
            <th class="px-3 py-3 text-left">Videos</th>
            <th class="px-3 py-3 text-left">Patrocinadores</th>
            <th class="px-3 py-3 text-right">Acoes</th>
          </tr>
        </thead>
        <tbody>
          @forelse($edicoes as $edicao)
            <tr class="ui-table-row">
              <td class="px-3 py-3 font-semibold text-[var(--ui-text-title)]">{{ $edicao->ano }}</td>
              <td class="px-3 py-3">
                <div class="font-medium text-[var(--ui-text-title)]">{{ $edicao->titulo }}</div>
                <div class="mt-1 text-xs text-[var(--ui-text-soft)]">/{{ $edicao->slug }}</div>
              </td>
              <td class="px-3 py-3">
                @if($edicao->status === 'publicado')
                  <span class="ui-badge ui-badge-success">Publicado</span>
                @elseif($edicao->status === 'arquivado')
                  <span class="ui-badge ui-badge-warning">Arquivado</span>
                @else
                  <span class="ui-badge ui-badge-neutral">Rascunho</span>
                @endif
              </td>
              <td class="px-3 py-3 text-[var(--ui-text-soft)]">{{ optional($edicao->published_at)->format('d/m/Y H:i') ?: 'Nao definido' }}</td>
              <td class="px-3 py-3">
                @if($edicao->capa_url)
                  <img src="{{ $edicao->capa_url }}" alt="" class="h-14 w-20 rounded-2xl object-cover border border-[var(--ui-border)]">
                @else
                  <span class="text-[var(--ui-text-soft)]">Sem capa</span>
                @endif
              </td>
              <td class="px-3 py-3">
                @can('rota_do_cacau.edicoes.fotos.view')
                  <a href="{{ route('coordenador.rota-do-cacau.edicoes.fotos.index', [$rota, $edicao]) }}" class="inline-flex items-center gap-2 font-medium text-[var(--ui-accent)] hover:underline">
                    <span>{{ $edicao->fotos_count }}</span>
                    <span class="text-xs text-[var(--ui-text-soft)]">Gerenciar</span>
                  </a>
                @else
                  <span class="text-[var(--ui-text-soft)]">{{ $edicao->fotos_count }}</span>
                @endcan
              </td>
              <td class="px-3 py-3">
                @can('rota_do_cacau.edicoes.videos.view')
                  <a href="{{ route('coordenador.rota-do-cacau.edicoes.videos.index', [$rota, $edicao]) }}" class="inline-flex items-center gap-2 font-medium text-[var(--ui-accent)] hover:underline">
                    <span>{{ $edicao->videos_count }}</span>
                    <span class="text-xs text-[var(--ui-text-soft)]">Gerenciar</span>
                  </a>
                @else
                  <span class="text-[var(--ui-text-soft)]">{{ $edicao->videos_count }}</span>
                @endcan
              </td>
              <td class="px-3 py-3">
                @can('rota_do_cacau.edicoes.patrocinadores.view')
                  <a href="{{ route('coordenador.rota-do-cacau.edicoes.patrocinadores.index', [$rota, $edicao]) }}" class="inline-flex items-center gap-2 font-medium text-[var(--ui-accent)] hover:underline">
                    <span>{{ $edicao->patrocinadores_count }}</span>
                    <span class="text-xs text-[var(--ui-text-soft)]">Gerenciar</span>
                  </a>
                @else
                  <span class="text-[var(--ui-text-soft)]">{{ $edicao->patrocinadores_count }}</span>
                @endcan
              </td>
              <td class="px-3 py-3">
                @canany([
                  'rota_do_cacau.edicoes.update',
                  'rota_do_cacau.edicoes.delete',
                  'rota_do_cacau.edicoes.fotos.view',
                  'rota_do_cacau.edicoes.videos.view',
                  'rota_do_cacau.edicoes.patrocinadores.view'
                ])
                  <div class="flex min-w-[16rem] flex-wrap items-center justify-end gap-2">
                    @can('rota_do_cacau.edicoes.update')
                      <a href="{{ route('coordenador.rota-do-cacau.edicoes.edit', [$rota, $edicao]) }}" class="ui-btn-secondary">Editar</a>
                    @endcan
                    @can('rota_do_cacau.edicoes.fotos.view')
                      <a href="{{ route('coordenador.rota-do-cacau.edicoes.fotos.index', [$rota, $edicao]) }}" class="ui-btn-secondary">Fotos</a>
                    @endcan
                    @can('rota_do_cacau.edicoes.videos.view')
                      <a href="{{ route('coordenador.rota-do-cacau.edicoes.videos.index', [$rota, $edicao]) }}" class="ui-btn-secondary">Videos</a>
                    @endcan
                    @can('rota_do_cacau.edicoes.patrocinadores.view')
                      <a href="{{ route('coordenador.rota-do-cacau.edicoes.patrocinadores.index', [$rota, $edicao]) }}" class="ui-btn-secondary">Patrocinadores</a>
                    @endcan
                    @can('rota_do_cacau.edicoes.delete')
                      <form method="POST" action="{{ route('coordenador.rota-do-cacau.edicoes.destroy', [$rota, $edicao]) }}" onsubmit="return confirm('Mover esta edicao para a lixeira?');">
                        @csrf
                        @method('DELETE')
                        <button class="ui-btn-danger">Excluir</button>
                      </form>
                    @endcan
                  </div>
                @else
                  <span class="text-sm text-[var(--ui-text-soft)]">Sem acoes disponiveis para este perfil.</span>
                @endcanany
              </td>
            </tr>
          @empty
            <tr class="ui-table-row">
              <td colspan="9" class="px-3 py-10 text-center text-[var(--ui-text-soft)]">Nenhuma edicao cadastrada.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $edicoes->links() }}</div>
  </x-dashboard.section-card>
</div>
@endsection
