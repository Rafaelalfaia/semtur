@extends('console.layout')

@section('title','Banners — Console')
@section('page.title','Banners')

@section('content')m
@php
  $u = auth()->user();
  $canManage = $u->can('banners.manage');
@endphp

  <div class="max-w-6xl mx-auto">
    {{-- actions / feedback --}}
    <div class="mb-4 flex items-center justify-between">
      <div class="text-sm text-slate-400">
        Gerencie os destaques exibidos na Home. Tamanho sugerido: <span class="text-slate-200">345×135</span>, raio 20px.
      </div>

      @can('banners.manage')
        <a href="{{ route('coordenador.banners.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 px-3 py-2 text-sm font-medium text-white">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z"/></svg>
          Novo banner
        </a>
      @endcan
    </div>

    @if(session('ok'))
      <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-emerald-300">
        {{ session('ok') }}
      </div>
    @endif

    {{-- table --}}
    <div class="overflow-hidden rounded-xl border border-white/5 bg-[#0F1412]">
      <table class="w-full text-sm">
        <thead class="bg-white/5 text-slate-300">
          <tr>
            <th class="px-4 py-3 text-left w-[110px]">Preview</th>
            <th class="px-4 py-3 text-left">Título</th>
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-left">Ordem</th>
            <th class="px-4 py-3 text-left">Atualizado</th>
            @if($canManage)
              <th class="px-4 py-3 text-right w-[160px]">Ações</th>
            @endif
          </tr>
        </thead>
        <tbody>
        @forelse($banners as $b)
          <tr class="border-t border-white/5">
            <td class="px-4 py-3">
              @if($b->imagem_url)
                <img src="{{ $b->imagem_url }}" alt="" class="h-[42px] w-[108px] object-cover rounded-md">
              @else
                <div class="h-[42px] w-[108px] rounded-md bg-white/5"></div>
              @endif
            </td>

            <td class="px-4 py-3">
              <div class="font-medium text-slate-100">{{ $b->titulo ?? '—' }}</div>
              @if($b->subtitulo)
                <div class="text-xs text-slate-400 line-clamp-1">{{ $b->subtitulo }}</div>
              @endif
            </td>

            <td class="px-4 py-3">
              @php
                $map = [
                  'publicado' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
                  'rascunho'  => 'bg-slate-500/15 text-slate-300 border-slate-500/30',
                  'arquivado' => 'bg-amber-500/15 text-amber-300 border-amber-500/30',
                ];
              @endphp
              <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs {{ $map[$b->status] ?? 'bg-white/10 text-slate-300 border-white/20' }}">
                {{ ucfirst($b->status) }}
              </span>
            </td>

            <td class="px-4 py-3">{{ $b->ordem }}</td>

            <td class="px-4 py-3 text-slate-400">
              {{ optional($b->updated_at)->format('d/m/Y H:i') ?? '—' }}
            </td>

            @if($canManage)
              <td class="px-4 py-3 text-right">
                <a href="{{ route('coordenador.banners.edit',$b) }}" class="text-emerald-400 hover:text-emerald-300">Editar</a>
                <form action="{{ route('coordenador.banners.destroy',$b) }}" method="post" class="inline"
                      onsubmit="return confirm('Remover o banner &quot;{{ $b->titulo }}&quot;?')">
                  @csrf @method('DELETE')
                  <button class="ml-3 text-red-400 hover:text-red-300">Excluir</button>
                </form>
              </td>
            @endif
          </tr>
        @empty
          <tr>
            <td colspan="{{ 5 + (int)$canManage }}" class="px-4 py-8 text-center text-slate-400">
              Nenhum banner. @can('banners.manage') Clique em <span class="text-slate-200 font-medium">Novo banner</span> para criar. @endcan
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      {{ $banners->links() }}
    </div>
  </div>
@endsection
