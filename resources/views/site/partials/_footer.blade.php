<footer class="site-footer site-footer--minimal">
    <div class="site-footer-inner site-footer-inner--minimal">
        <p class="site-footer-meta">
            © {{ date('Y') }} VisitAltamira. Guia turístico oficial de Altamira.
        </p>

        @if(\Illuminate\Support\Facades\Route::has('site.semtur'))
            <p class="site-footer-meta site-footer-meta--subtle">
                <a href="{{ route('site.semtur') }}" class="site-footer-link-subtle">Secretaria de Turismo</a>
            </p>
        @endif

        <p class="site-footer-meta">
            Desenvolvido por <strong>AL Tech</strong>
        </p>
    </div>
</footer>
