@php
    $idioma ??= null;
@endphp

<x-dashboard.section-card title="Dados principais" subtitle="Estruture o idioma base do sistema com o mesmo padrão visual do console.">
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="ui-form-label" for="nome">Nome</label>
            <input id="nome" name="nome" type="text" value="{{ old('nome', $idioma->nome ?? '') }}" class="ui-form-control" placeholder="Português" required>
            @error('nome')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="ui-form-label" for="sigla">Sigla</label>
            <input id="sigla" name="sigla" type="text" value="{{ old('sigla', $idioma->sigla ?? '') }}" class="ui-form-control" placeholder="PT" required>
            @error('sigla')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="ui-form-label" for="codigo">Código</label>
            <input id="codigo" name="codigo" type="text" value="{{ old('codigo', $idioma->codigo ?? '') }}" class="ui-form-control" placeholder="pt" required>
            <p class="mt-1 text-xs text-[var(--ui-text-soft)]">Use o identificador curto do idioma, como `pt`, `en` ou `es`.</p>
            @error('codigo')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="ui-form-label" for="bandeira">Bandeira</label>
            <input id="bandeira" name="bandeira" type="text" value="{{ old('bandeira', $idioma->bandeira ?? '') }}" class="ui-form-control" placeholder="icons/pt.png">
            <p class="mt-1 text-xs text-[var(--ui-text-soft)]">Informe um caminho público ou URL da imagem da bandeira.</p>
            @error('bandeira')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>
    </div>
</x-dashboard.section-card>

<x-dashboard.section-card title="Metadados técnicos" subtitle="Campos mantidos agora para evitar retrabalho quando o frontend passar a consumir a base nova.">
    <div class="grid gap-4 md:grid-cols-3">
        <div>
            <label class="ui-form-label" for="html_lang">HTML lang</label>
            <input id="html_lang" name="html_lang" type="text" value="{{ old('html_lang', $idioma->html_lang ?? '') }}" class="ui-form-control" placeholder="pt-BR">
            @error('html_lang')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="ui-form-label" for="hreflang">hreflang</label>
            <input id="hreflang" name="hreflang" type="text" value="{{ old('hreflang', $idioma->hreflang ?? '') }}" class="ui-form-control" placeholder="pt-BR">
            @error('hreflang')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="ui-form-label" for="og_locale">OG locale</label>
            <input id="og_locale" name="og_locale" type="text" value="{{ old('og_locale', $idioma->og_locale ?? '') }}" class="ui-form-control" placeholder="pt_BR">
            @error('og_locale')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>
    </div>
</x-dashboard.section-card>

<x-dashboard.section-card title="Estado" subtitle="Controle quais idiomas ficam disponíveis e qual deles é o padrão do sistema.">
    <div class="grid gap-4 md:grid-cols-2">
        <label class="inline-flex items-start gap-3 rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-input-bg)] p-4">
            <input type="checkbox" name="is_active" value="1" class="ui-form-check mt-1 rounded" @checked(old('is_active', $idioma->is_active ?? true))>
            <span>
                <span class="block text-sm font-semibold text-[var(--ui-text)]">Idioma ativo</span>
                <span class="mt-1 block text-xs text-[var(--ui-text-soft)]">Idiomas ativos poderão ser exibidos no painel e no site quando o frontend for conectado à nova base.</span>
            </span>
        </label>

        <label class="inline-flex items-start gap-3 rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-input-bg)] p-4">
            <input type="checkbox" name="is_default" value="1" class="ui-form-check mt-1 rounded" @checked(old('is_default', $idioma->is_default ?? false))>
            <span>
                <span class="block text-sm font-semibold text-[var(--ui-text)]">Idioma padrão</span>
                <span class="mt-1 block text-xs text-[var(--ui-text-soft)]">Sempre existe apenas um idioma padrão, e ele permanece ativo por segurança.</span>
            </span>
        </label>
    </div>
</x-dashboard.section-card>
