@extends('site.layouts.app')
@section('title','Redes Sociais')

@section('site.content')
<div class="min-h-dvh bg-white text-slate-900">
  <header class="sticky top-0 z-10 bg-white/80 backdrop-blur border-b border-slate-100">
    <div class="mx-auto max-w-md px-4 py-3 flex items-center gap-3">
      <a href="{{ route('site.perfil.index') }}" class="h-10 w-10 rounded-full bg-slate-100 grid place-items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
      </a>
      <h1 class="text-lg font-semibold">Redes Sociais</h1>
    </div>
  </header>

  <main class="mx-auto max-w-md px-6 py-6 pb-24">
    @if (session('status'))
      <div class="mb-4 text-xs rounded-lg p-2 bg-emerald-50 border border-emerald-200 text-emerald-700">
        {{ session('status') }}
      </div>
    @endif

    <form method="POST" action="{{ route('site.perfil.redes.atualizar') }}" class="space-y-4">
      @csrf @method('PUT')

      <div>
        <label class="block text-sm font-medium text-slate-700">Instagram</label>
        <input name="instagram" type="text" value="{{ old('instagram',$socials['instagram'] ?? '') }}"
               placeholder="@usuario ou https://instagram.com/usuario"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
        @error('instagram')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Facebook</label>
        <input name="facebook" type="text" value="{{ old('facebook',$socials['facebook'] ?? '') }}"
               placeholder="https://facebook.com/usuario"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
        @error('facebook')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Site pessoal</label>
        <input name="site" type="text" value="{{ old('site',$socials['site'] ?? '') }}"
               placeholder="https://meusite.com"
               class="mt-1 w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500">
        @error('site')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>

      <div class="pt-2">
        <button class="w-full h-12 rounded-2xl bg-emerald-600 text-white font-semibold hover:bg-emerald-500">Salvar</button>
      </div>
    </form>
  </main>
</div>
{{-- Espaço p/ não cobrir conteúdo (mobile) + bottom nav --}}
<div class="h-[80px] pb-[env(safe-area-inset-bottom)] md:hidden"></div>
@endsection
