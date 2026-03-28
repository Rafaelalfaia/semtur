@extends('console.layout')

@section('title', 'Vídeos - '.$edicao->titulo)
@section('page.title', 'Vídeos da edição')
@section('topbar.description', 'Gerencie os links de vídeos da edição com base em Google Drive e embed seguro.')

@section('topbar.nav')
  <a href="{{ route('coordenador.jogos-indigenas.index') }}" class="ui-console-topbar-tab">Jogos Indígenas</a>
  <a href="{{ route('coordenador.jogos-indigenas.edicoes.index', $jogo) }}" class="ui-console-topbar-tab">Edições</a>
  <span class="ui-console-topbar-tab is-active">Vídeos</span>
@endsection

@section('content')
<div class="ui-console-page">
  @include('coordenador.partials.flash')

  <x-dashboard.page-header title="Vídeos da edição" subtitle="Cadastre vídeos por link do Google Drive com URL de preview opcional.">
    <x-slot:actions>
      <div class="flex flex-wrap gap-2">
        <a href="{{ route('coordenador.jogos-indigenas.edicoes.videos.create', [$jogo, $edicao]) }}" class="ui-btn-primary">Novo vídeo</a>
        <a href="{{ route('coordenador.jogos-indigenas.edicoes.edit', [$jogo, $edicao]) }}" class="ui-btn-secondary">Voltar à edição</a>
      </div>
    </x-slot:actions>
  </x-dashboard.page-header>

  <x-dashboard.section-card title="Lista de vídeos" subtitle="Os vídeos pertencem exclusivamente a esta edição." class="ui-coord-dashboard-panel mt-5">
    <div class="ui-table-shell">
      <table class="min-w-full text-sm">
        <thead class="ui-table-head">
          <tr>
            <th class="px-3 py-3 text-left">Título</th>
            <th class="px-3 py-3 text-left">Drive</th>
            <th class="px-3 py-3 text-left">Preview</th>
            <th class="px-3 py-3 text-left">Ordem</th>
            <th class="px-3 py-3 text-right">Ações</th>
          </tr>
        </thead>
        <tbody>
          @forelse($videos as $video)
            <tr class="ui-table-row">
              <td class="px-3 py-3">
                <div class="font-medium text-[var(--ui-text-title)]">{{ $video->titulo }}</div>
                <div class="mt-1 text-xs text-[var(--ui-text-soft)]">{{ $video->descricao ?: 'Sem descrição.' }}</div>
              </td>
              <td class="px-3 py-3">
                <a href="{{ $video->drive_url }}" target="_blank" rel="noreferrer" class="text-[var(--ui-accent)] hover:underline">Abrir link</a>
              </td>
              <td class="px-3 py-3">
                @if($video->embed_url_resolvida)
                  <a href="{{ $video->embed_url_resolvida }}" target="_blank" rel="noreferrer" class="text-[var(--ui-accent)] hover:underline">Preview</a>
                @else
                  <span class="text-[var(--ui-text-soft)]">Não resolvida</span>
                @endif
              </td>
              <td class="px-3 py-3 text-[var(--ui-text-soft)]">{{ $video->ordem }}</td>
              <td class="px-3 py-3">
                <div class="flex flex-wrap items-center justify-end gap-2">
                  <a href="{{ route('coordenador.jogos-indigenas.edicoes.videos.edit', [$jogo, $edicao, $video]) }}" class="ui-btn-secondary">Editar</a>
                  <form method="POST" action="{{ route('coordenador.jogos-indigenas.edicoes.videos.destroy', [$jogo, $edicao, $video]) }}" onsubmit="return confirm('Excluir este vídeo da edição?');">
                    @csrf
                    @method('DELETE')
                    <button class="ui-btn-danger">Excluir</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr class="ui-table-row">
              <td colspan="5" class="px-3 py-10 text-center text-[var(--ui-text-soft)]">Nenhum vídeo cadastrado.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="mt-4">{{ $videos->links() }}</div>
  </x-dashboard.section-card>
</div>
@endsection
