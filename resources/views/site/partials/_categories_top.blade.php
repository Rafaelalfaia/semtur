<section class="site-section">
    <div class="site-home-categories-shell">
        <x-section-head
            eyebrow="Explorar por interesse"
            title="Categorias"
            subtitle="Atalhos editoriais para entrar no clima da viagem sem perder o panorama do portal."
        />

        @include('site.partials._categories_chips', [
            'categorias' => $categorias,
            'currentCat' => $currentCat ?? null,
            'href' => $href ?? null,
        ])
    </div>
</section>
