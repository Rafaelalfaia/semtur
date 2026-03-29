@extends('console.layout')

@section('title', 'Onde ficar')
@section('page.title', 'Onde ficar')
@section('topbar.description', 'Gerencie a página editorial de hospedagem com o mesmo shell compartilhado do console.')

@section('topbar.nav')
  <span class="ui-console-topbar-tab is-active">Onde ficar</span>
@endsection

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $heroAtual = old('remover_hero') ? null : ($pagina->hero_url ?? null);

    $selecionadasIniciais = old(
        'empresas',
        collect($pagina->empresasSelecionadas ?? [])->map(function ($item) {
            return [
                'empresa_id' => $item->empresa_id,
                'observacao_curta' => $item->observacao_curta,
                'destaque' => (bool) $item->destaque,
            ];
        })->values()->all()
    );

    $empresasDisponiveis = collect($empresas ?? [])->map(function ($empresa) {
        return [
            'id' => $empresa->id,
            'nome' => $empresa->nome,
            'cidade' => $empresa->cidade,
        ];
    })->values()->all();

    $previewPublico = Route::has('site.onde_ficar') ? route('site.onde_ficar') : '#';
@endphp

<div
    class="ui-console-page"
    x-data="{
        heroPreview: @js($heroAtual),
        empresasDisponiveis: @js($empresasDisponiveis),
        selecionadas: @js($selecionadasIniciais),

        addEmpresa() {
            this.selecionadas.push({
                empresa_id: '',
                observacao_curta: '',
                destaque: false
            });
        },

        removeEmpresa(index) {
            this.selecionadas.splice(index, 1);
        },

        empresaLabel(id) {
            const item = this.empresasDisponiveis.find(e => Number(e.id) === Number(id));
            return item ? `${item.nome}${item.cidade ? ' • ' + item.cidade : ''}` : 'Selecione uma empresa';
        },

        onHeroChange(event) {
            const file = event.target.files?.[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = e => this.heroPreview = e.target.result;
            reader.readAsDataURL(file);
        }
    }"
>
    <div class="space-y-6">
        <x-dashboard.page-header
            title="Onde ficar"
            subtitle="Defina os textos editoriais, a imagem principal e as empresas que devem aparecer em /onde-ficar."
        >
            <a href="{{ $previewPublico }}" target="_blank" rel="noopener noreferrer" class="ui-btn-secondary">
                Ver página pública
            </a>
        </x-dashboard.page-header>

        @if(session('ok'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('ok') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-800">
                <div class="font-semibold">Revise os campos abaixo:</div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ route('coordenador.onde_ficar.update') }}"
            method="POST"
            enctype="multipart/form-data"
            class="mt-5 space-y-6"
        >
            @csrf
            @method('PUT')

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                <div class="space-y-6">
                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">Dados principais</h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    Estrutura básica da página de hospedagem.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-5 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label for="titulo" class="mb-2 block text-sm font-medium text-slate-700">
                                    Título
                                </label>
                                <input
                                    id="titulo"
                                    name="titulo"
                                    type="text"
                                    value="{{ old('titulo', $pagina->titulo) }}"
                                    class="w-full rounded-2xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    placeholder="Onde ficar em Altamira"
                                    required
                                >
                            </div>

                            <div class="md:col-span-2">
                                <label for="subtitulo" class="mb-2 block text-sm font-medium text-slate-700">
                                    Subtítulo
                                </label>
                                <input
                                    id="subtitulo"
                                    name="subtitulo"
                                    type="text"
                                    value="{{ old('subtitulo', $pagina->subtitulo) }}"
                                    class="w-full rounded-2xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    placeholder="Hospedagem, conforto e boa localização"
                                >
                            </div>

                            <div class="md:col-span-2">
                                <label for="resumo" class="mb-2 block text-sm font-medium text-slate-700">
                                    Resumo
                                </label>
                                <textarea
                                    id="resumo"
                                    name="resumo"
                                    rows="4"
                                    class="w-full rounded-2xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    placeholder="Texto curto para introduzir a página."
                                    required
                                >{{ old('resumo', $pagina->resumo) }}</textarea>
                            </div>

                            <div>
                                <label for="status" class="mb-2 block text-sm font-medium text-slate-700">
                                    Status
                                </label>
                                <select
                                    id="status"
                                    name="status"
                                    class="w-full rounded-2xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    required
                                >
                                    @foreach(\App\Models\Conteudo\OndeFicarPagina::STATUS as $status)
                                        <option value="{{ $status }}" @selected(old('status', $pagina->status) === $status)>
                                            {{ ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                <div class="font-semibold">Regra de publicação</div>
                                <p class="mt-1 leading-6">
                                    Para publicar, selecione pelo menos uma empresa de hospedagem.
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-900">Textos editoriais</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Conteúdo descritivo da página pública.
                        </p>

                        <div class="mt-6 space-y-5">
                            <div>
                                <label for="texto_intro" class="mb-2 block text-sm font-medium text-slate-700">
                                    Texto de introdução
                                </label>
                                <textarea
                                    id="texto_intro"
                                    name="texto_intro"
                                    rows="6"
                                    class="w-full rounded-2xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    placeholder="Apresentação geral da experiência de se hospedar na cidade."
                                >{{ old('texto_intro', $pagina->texto_intro) }}</textarea>
                            </div>

                            <div>
                                <label for="texto_hospedagem_local" class="mb-2 block text-sm font-medium text-slate-700">
                                    Texto sobre hospedagem local
                                </label>
                                <textarea
                                    id="texto_hospedagem_local"
                                    name="texto_hospedagem_local"
                                    rows="6"
                                    class="w-full rounded-2xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    placeholder="Fale sobre hotelaria, pousadas, localização, conforto e perfil das estadias."
                                >{{ old('texto_hospedagem_local', $pagina->texto_hospedagem_local) }}</textarea>
                            </div>

                            <div>
                                <label for="texto_dicas" class="mb-2 block text-sm font-medium text-slate-700">
                                    Dicas para o visitante
                                </label>
                                <textarea
                                    id="texto_dicas"
                                    name="texto_dicas"
                                    rows="5"
                                    class="w-full rounded-2xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    placeholder="Ex.: reservar com antecedência, localização, períodos mais movimentados."
                                >{{ old('texto_dicas', $pagina->texto_dicas) }}</textarea>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">Empresas em destaque</h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    Selecione as empresas que aparecerão na página pública.
                                </p>
                            </div>

                            <button
                                type="button"
                                @click="addEmpresa()"
                                class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-500"
                            >
                                Adicionar empresa
                            </button>
                        </div>

                        <template x-if="selecionadas.length === 0">
                            <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                                <div class="text-sm font-medium text-slate-900">
                                    Nenhuma empresa selecionada ainda.
                                </div>
                                <p class="mt-2 text-sm leading-6 text-slate-500">
                                    Adicione hotéis, pousadas ou outras estadias que devem aparecer no site.
                                </p>
                            </div>
                        </template>

                        <div class="mt-6 space-y-4">
                            <template x-for="(item, index) in selecionadas" :key="index">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">
                                                Seleção <span x-text="index + 1"></span>
                                            </div>
                                            <div class="mt-1 text-sm text-slate-500" x-text="empresaLabel(item.empresa_id)"></div>
                                        </div>

                                        <button
                                            type="button"
                                            @click="removeEmpresa(index)"
                                            class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-50"
                                        >
                                            Remover
                                        </button>
                                    </div>

                                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                                        <div class="md:col-span-2">
                                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                                Empresa
                                            </label>

                                            <select
                                                :name="`empresas[${index}][empresa_id]`"
                                                x-model="item.empresa_id"
                                                class="w-full rounded-2xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                                required
                                            >
                                                <option value="">Selecione</option>
                                                <template x-for="empresa in empresasDisponiveis" :key="empresa.id">
                                                    <option :value="empresa.id" x-text="`${empresa.nome}${empresa.cidade ? ' • ' + empresa.cidade : ''}`"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <div class="md:col-span-2">
                                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                                Observação curta
                                            </label>

                                            <textarea
                                                :name="`empresas[${index}][observacao_curta]`"
                                                x-model="item.observacao_curta"
                                                rows="3"
                                                class="w-full rounded-2xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                                placeholder="Ex.: boa localização, ideal para família, vista para o rio..."
                                            ></textarea>
                                        </div>

                                        <div class="md:col-span-2">
                                            <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                                <input
                                                    type="hidden"
                                                    :name="`empresas[${index}][destaque]`"
                                                    value="0"
                                                >
                                                <input
                                                    type="checkbox"
                                                    value="1"
                                                    :name="`empresas[${index}][destaque]`"
                                                    x-model="item.destaque"
                                                    class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                                >
                                                <span class="text-sm font-medium text-slate-700">
                                                    Marcar como destaque
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-900">SEO</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Metadados básicos da página pública.
                        </p>

                        <div class="mt-6 grid gap-5">
                            <div>
                                <label for="seo_title" class="mb-2 block text-sm font-medium text-slate-700">
                                    SEO title
                                </label>
                                <input
                                    id="seo_title"
                                    name="seo_title"
                                    type="text"
                                    value="{{ old('seo_title', $pagina->seo_title) }}"
                                    class="w-full rounded-2xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    placeholder="Onde ficar em Altamira • Visit Altamira"
                                >
                            </div>

                            <div>
                                <label for="seo_description" class="mb-2 block text-sm font-medium text-slate-700">
                                    SEO description
                                </label>
                                <textarea
                                    id="seo_description"
                                    name="seo_description"
                                    rows="3"
                                    class="w-full rounded-2xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    placeholder="Resumo otimizado para buscadores."
                                >{{ old('seo_description', $pagina->seo_description) }}</textarea>
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="space-y-6">
                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-900">Imagem principal</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Hero da página pública.
                        </p>

                        <div class="mt-5 overflow-hidden rounded-3xl border border-slate-200 bg-slate-100">
                            <template x-if="heroPreview">
                                <img
                                    :src="heroPreview"
                                    alt="Prévia do hero"
                                    class="h-56 w-full object-cover"
                                >
                            </template>

                            <template x-if="!heroPreview">
                                <div class="flex h-56 items-center justify-center px-6 text-center text-sm text-slate-500">
                                    Nenhuma imagem selecionada.
                                </div>
                            </template>
                        </div>

                        <div class="mt-5 space-y-4">
                            <div>
                                <label for="hero" class="mb-2 block text-sm font-medium text-slate-700">
                                    Enviar nova imagem
                                </label>
                                <input
                                    id="hero"
                                    name="hero"
                                    type="file"
                                    accept="image/*"
                                    @change="onHeroChange"
                                    class="block w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-emerald-700 hover:file:bg-emerald-100"
                                >
                            </div>

                            @if($pagina->hero_url)
                                <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <input
                                        type="checkbox"
                                        name="remover_hero"
                                        value="1"
                                        class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                    >
                                    <span class="text-sm font-medium text-slate-700">
                                        Remover imagem atual
                                    </span>
                                </label>
                            @endif
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-900">Resumo da página</h2>

                        <div class="mt-5 space-y-3">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Status atual</div>
                                <div class="mt-1 text-sm font-semibold text-slate-900">
                                    {{ ucfirst(old('status', $pagina->status ?? 'rascunho')) }}
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Empresas selecionadas</div>
                                <div class="mt-1 text-sm font-semibold text-slate-900" x-text="selecionadas.length"></div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div class="text-[11px] uppercase tracking-[0.16em] text-slate-500">Publicação</div>
                                <div class="mt-1 text-sm font-semibold text-slate-900">
                                    Página editorial de hospedagem
                                </div>
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="mt-5 inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-emerald-500"
                        >
                            Salvar página
                        </button>
                    </section>
                </aside>
            </div>
        </form>
    </div>
</div>
@endsection
