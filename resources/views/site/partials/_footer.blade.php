<footer class="site-footer site-footer--minimal">
    <div class="site-footer-inner site-footer-inner--minimal">
        <p class="site-footer-meta">
            &copy; {{ date('Y') }} {{ __('ui.brand') }}. {{ __('ui.footer.official_guide') }}
        </p>

        @if(\Illuminate\Support\Facades\Route::has('site.semtur'))
            <p class="site-footer-meta site-footer-meta--subtle">
                <a href="{{ route('site.semtur') }}" class="site-footer-link-subtle">{{ __('ui.footer.tourism_department') }}</a>
            </p>
        @endif

        <p class="site-footer-meta">
            {{ __('ui.footer.developed_by') }} <strong>AL Tech</strong>
        </p>
    </div>
</footer>
