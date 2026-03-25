@extends('site.layouts.app')
@section('title','Política e Privacidade')

@section('site.content')
<div class="min-h-dvh bg-white text-slate-900">
  <header class="sticky top-0 z-10 bg-white/80 backdrop-blur border-b border-slate-100">
    <div class="mx-auto max-w-lg px-4 py-3 flex items-center gap-3">
      <a href="{{ url()->previous() ?: route('site.perfil.index') }}"
         class="h-10 w-10 rounded-full bg-slate-100 grid place-items-center hover:bg-slate-200"
         aria-label="Voltar">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </a>
      <h1 class="text-lg sm:text-xl font-semibold">Política e Privacidade</h1>
    </div>
  </header>

  <main class="mx-auto max-w-lg px-6 py-6 pb-24 prose prose-slate prose-sm">
    <h2>1. Introdução</h2>
    <p>Esta Política descreve como coletamos e tratamos seus dados no aplicativo VISIT Altamira.</p>

    <h3>2. Dados que coletamos</h3>
    <ul>
      <li>Dados de conta: nome, e-mail (opcional), CPF (se informado).</li>
      <li>Dados de uso: avaliações de pontos turísticos e empresas.</li>
    </ul>

    <h3>3. Finalidades</h3>
    <ul>
      <li>Manter e melhorar sua experiência no app.</li>
      <li>Exibir e moderar avaliações.</li>
    </ul>

    <h3>4. Seus direitos</h3>
    <p>Você pode solicitar acesso, correção e exclusão dos seus dados pelo menu <strong>Minha Conta</strong>.</p>

    <h3>5. Contato</h3>
    <p>Para dúvidas, entre em contato com a SEMTUR.</p>

    <p class="text-xs text-slate-500 mt-6">Última atualização: {{ now()->format('d/m/Y') }}</p>
  </main>
</div>

<div class="h-[80px] pb-[env(safe-area-inset-bottom)] md:hidden"></div>
@endsection
