@extends('console.layout')

@section('title', 'Videos - '.$edicao->titulo)
@section('page.title', 'Videos da edicao')
@section('topbar.description', 'Gerencie os links de videos da edicao com base em Google Drive e embed seguro.')

@section('topbar.nav')
  <a href="{{ route('coordenador.jogos-indigenas.index') }}" class="ui-console-topbar-tab">Jogos Indigenas</a>
  <a href="{{ route('coordenador.jogos-indigenas.edicoes.index', $jogo) }}" class="ui-console-topbar-tab">Edicoes</a>
  <span class="ui-console-topbar-tab is-active">Videos</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header title="Videos da edicao" subtitle="Cadastre videos por link do Google Drive com URL de preview opcional.">
    <x-slot:actions>
      <div class="flex flex-wrap gap-2">
        <a href="{{ route('coordenador.jogos-indigenas.edicoes.videos.create', [$jogo, $edicao]) }}" class="ui-btn-primary">Novo video</a>
        <a href="{{ route('coordenador.jogos-indigenas.edicoes.edit', [$jogo, $edicao]) }}" class="ui-btn-secondary">Voltar a edicao</a>
      </div>
    </x-slot:actions>
  </x-dashboard.page-header>

  <x-dashboard.section-card title="Lista de videos" subtitle="Os videos pertencem exclusivamente a esta edicao." class="ui-coord-dashboard-panel mt-5">
    <div class="ui-table-shell">
      <table class="min-w-full text-sm">
        <thead class="ui-table-head">
          <tr>
            <th class="px-3 py-3 text-left">Titulo</th>
            <th class="px-3 py-3 text-left">Drive</th>
            <th class="px-3 py-3 text-left">Preview</th>
            <th class="px-3 py-3 text-left">Ordem</th>
            <th class="px-3 py-3 text-right">Acoes</th>
          </tr>
        </thead>
        <tbody>
          @forelse($videos as $video)
            <tr class="ui-table-row">
              <td class="px-3 py-3">
                <div class="font-medium text-[var(--ui-text-title)]">{{ $video->titulo }}</div>
                <div class="mt-1 text-xs text-[var(--ui-text-soft)]">{{ $video->descricao ?: 'Sem descricao.' }}</div>
              </td>
              <td class="px-3 py-3">
                <a href="{{ $video->drive_url }}" target="_blank" rel="noreferrer" class="text-[var(--ui-accent)] hover:underline">Abrir link</a>
              </td>
              <td class="px-3 py-3">
                @if($video->embed_url_resolvida)
                  <a href="{{ $video->embed_url_resolvida }}" target="_blank" rel="noreferrer" class="text-[var(--ui-accent)] hover:underline">Preview</a>
                @else
                  <span class="text-[var(--ui-text-soft)]">Nao resolvida</span>
                @endif
              </td>
              <td class="px-3 py-3 text-[var(--ui-text-soft)]">{{ $video->ordem }}</td>
              <td class="px-3 py-3">
                <div class="flex flex-wrap items-center justify-end gap-2">
                  <a href="{{ route('coordenador.jogos-indigenas.edicoes.videos.edit', [$jogo, $edicao, $video]) }}" class="ui-btn-secondary">Editar</a>
                  <form method="POST" action="{{ route('coordenador.jogos-indigenas.edicoes.videos.destroy', [$jogo, $edicao, $video]) }}" onsubmit="return confirm('Excluir este video da edicao?');">
                    @csrf
                    @method('DELETE')
                    <button class="ui-btn-danger">Excluir</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr class="ui-table-row">
              <td colspan="5" class="px-3 py-10 text-center text-[var(--ui-text-soft)]">Nenhum video cadastrado.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="mt-4">{{ $videos->links() }}</div>
  </x-dashboard.section-card>
</div>
@endsection
