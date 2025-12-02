@extends('site.layouts.app')
@section('title','Descubra Altamira')
@section('meta.description','Guia turístico oficial de Altamira, Pará.')
@section('meta.image', $capaUrl ?? '/images/og-default.jpg')

@section('title', ($sec->nome ?? 'SEMTUR').' • Altamira')

@php
  $heroUrl  = $sec->foto_capa_url ?: ($sec->foto_url ?: asset('imagens/hero.jpg'));
  $logoUrl  = $sec->foto_url ?: asset('imagens/visitpreto.png');
  $tab      = request('tab','descricao'); // descricao | redes
  $redes    = is_array($sec->redes ?? null) ? $sec->redes : [];
@endphp

@section('site.content')
<div class="bg-white min-h-screen"> {{-- 👈 wrapper branco, igual ao show de pontos --}}

  {{-- HERO (Imagem + gradiente) --}}
  <section class="relative h-[415px] min-h-[320px] w-full overflow-hidden">
    <div class="absolute inset-0"
         style="background:
           linear-gradient(179.92deg, #00837B 0.07%, rgba(255,255,255,0) 58.56%),
           url('{{ $heroUrl }}') center/cover no-repeat;">
    </div>

    {{-- Top bar (voltar) --}}
    <div class="absolute left-0 right-0 top-0 h-[44px]"></div>
    <div class="absolute left-0 right-0 top-[44px] flex items-center gap-4 px-4">
      <a href="{{ url()->previous() }}"
         class="grid h-12 w-12 place-items-center rounded-full bg-white/20 backdrop-blur-sm shadow-md"
         aria-label="Voltar">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
          <path d="M15 18l-6-6 6-6"/>
        </svg>
      </a>
    </div>
  </section>

  {{-- CONTEÚDO (cartão branco arredondado) --}}
  <section class="relative -mt-10 mx-auto w-full max-w-[420px] md:max-w-[720px] lg:max-w-[960px] px-4 md:px-6">
    <div class="rounded-t-[30px] bg-white shadow-[0_4px_36px_rgba(0,0,0,0.09)] ring-1 ring-black/5">
      {{-- “home indicator” --}}
      <div class="flex justify-center pt-3">
        <div class="h-[5px] w-[134px] rounded-full bg-[#868B8B]"></div>
      </div>

      {{-- Cabeçalho --}}
      <div class="px-4 pt-3 pb-0">
        <div class="flex items-center justify-between gap-5">
          <div class="flex items-center gap-3">
            <img src="{{ $logoUrl }}" alt="SEMTUR" class="h-12 w-12 rounded-lg bg-slate-100 object-cover p-1">
            <div>
              <div class="text-[24px] leading-8 font-semibold text-[#2B3536]">SEMTUR</div>
              <div class="mt-0.5 flex items-center gap-2 text-sm text-[#868B8B]">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#868B8B" stroke-width="1.6"><path d="M12 21s-7-4.438-7-10a7 7 0 0 1 14 0c0 5.562-7 10-7 10z"/><circle cx="12" cy="11" r="3"/></svg>
                Altamira
              </div>
            </div>
          </div>
          <div class="hidden sm:flex items-center gap-1 text-[#FCCF05]">
            @for($i=0;$i<5;$i++)
              <svg width="18" height="18" viewBox="0 0 24 24" fill="#FCCF05"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.401 8.165L12 18.896l-7.335 3.866 1.401-8.165L.132 9.21l8.2-1.192z"/></svg>
            @endfor
            <span class="ml-1 text-sm text-[#868B8B]">5.0</span>
          </div>
        </div>
      </div>

      {{-- Abas --}}
      <div class="mt-2 flex items-center gap-[49px] px-4">
        <a href="{{ route('site.semtur',['tab'=>'descricao']) }}"
           class="flex flex-col items-center gap-2 py-4 w-[114px]">
          <span class="text-[16px] leading-5 font-semibold {{ $tab==='descricao' ? 'text-[#00837B]' : 'text-[#2B3536]' }}">
            Descrição
          </span>
          <span class="h-0 w-[114px] border-b-2 {{ $tab==='descricao' ? 'border-[#00837B]' : 'border-transparent' }}"></span>
        </a>
        <a href="{{ route('site.semtur',['tab'=>'redes']) }}"
           class="flex flex-col items-center gap-2 py-4 w-[110px]">
          <span class="text-[16px] leading-5 font-semibold {{ $tab==='redes' ? 'text-[#00837B]' : 'text-[#2B3536]' }}">
            Redes Sociais
          </span>
          <span class="h-0 w-[110px] border-b-2 {{ $tab==='redes' ? 'border-[#00837B]' : 'border-transparent' }}"></span>
        </a>
      </div>

      {{-- Conteúdo da aba --}}
      @if($tab === 'redes')
        <div class="px-4 pb-4">
          @php $labels = ['instagram'=>'Instagram','facebook'=>'Facebook','linkedin'=>'LinkedIn','site'=>'Site','whatsapp'=>'WhatsApp']; @endphp
          <div class="grid gap-3">
            @php $tem=false; @endphp
            @foreach($labels as $k=>$label)
              @if(!empty($redes[$k])) @php $tem=true; @endphp
                <a href="{{ $redes[$k] }}" target="_blank"
                   class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 hover:bg-slate-50">
                  <span class="text-[#2B3536] font-medium">{{ $label }}</span>
                  <span class="max-w-[60%] truncate text-emerald-700">{{ $redes[$k] }}</span>
                </a>
              @endif
            @endforeach
            @unless($tem)
              <div class="text-sm text-slate-500">Nenhuma rede social informada.</div>
            @endunless
          </div>
        </div>
      @else
        <div class="px-4 pb-2">
          <div class="mb-2 text-[16px] leading-5 font-semibold text-[#2B3536]">Sobre</div>
          <div class="text-[14px] leading-5 text-[#868B8B] text-justify">{!! nl2br(e($sec->descricao)) !!}</div>
        </div>
      @endif

      {{-- Equipe --}}
      <div class="px-4 pt-2 pb-5">
        <div class="mb-2 flex items-center justify-between">
          <div class="text-[16px] leading-5 font-semibold text-[#2B3536]">Equipe SEMTUR</div>

        </div>

        <div id="equipe" class="grid gap-4">
          @forelse($membros as $m)
            <article class="relative h-[139px] w-full rounded-[10px] bg-white shadow-[0_4px_36px_rgba(0,0,0,0.09)] ring-1 ring-black/5">
              {{-- Imagem --}}
              <div class="absolute left-2 top-2 h-[123px] w-[136px] overflow-hidden rounded-[10px]">
                <img src="{{ $m->foto_url ?? asset('imagens/avatar.png') }}"
                     alt="{{ $m->nome }}" class="h-full w-full object-cover"/>
                <div class="pointer-events-none absolute inset-0 rounded-b-[10px]"
                     style="background:linear-gradient(180deg,rgba(0,0,0,0) 0%,rgba(0,0,0,0.58) 100%);"></div>
              </div>

              {{-- Detalhes --}}
              <div class="absolute left-[157px] top-[13px] right-3">
                <div class="mb-1 line-clamp-1 text-[16px] leading-[22px] font-semibold text-[#2B3536]">
                  {{ $m->nome }}
                </div>
                <div class="text-[12px] leading-[18px] text-[#868B8B]">
                  {{ $m->cargo }}
                </div>

                @if($m->resumo)
                  <div class="mt-2 line-clamp-2 text-[12px] leading-[18px] text-[#868B8B]">
                    {{ $m->resumo }}
                  </div>
                @endif
              </div>
            </article>
          @empty
            <div class="text-sm text-slate-500">Nenhum membro cadastrado.</div>
          @endforelse
        </div>
      </div>
    </div>
  </section>

  {{-- Espaço p/ não cobrir conteúdo (mobile/tablet) --}}
  <div class="h-[98px] pb-[env(safe-area-inset-bottom)] lg:hidden"></div>
</div>

{{-- Bottom nav fixo (fica fora do wrapper branco) --}}
@includeIf('site.partials._bottom_nav')
@endsection
