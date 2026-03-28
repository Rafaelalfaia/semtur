<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Services\ThemeManager;
use App\Services\ThemeResolver;
use Illuminate\Http\Request;

class ThemeExecutionController extends Controller
{
    public function index(Request $request, ThemeResolver $resolver)
    {
        $filters = [
            'q' => trim((string) $request->string('q')),
        ];

        $themes = Theme::query()
            ->whereIn('status', [Theme::STATUS_DISPONIVEL, 'available'])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($builder) use ($filters) {
                    $builder
                        ->where('name', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('slug', 'like', '%' . $filters['q'] . '%');
                });
            })
            ->latest('updated_at')
            ->paginate(12)
            ->withQueryString();

        return view('coordenador.temas.index', [
            'themes' => $themes,
            'filters' => $filters,
            'activeConsoleTheme' => $resolver->activeTheme(Theme::SCOPE_CONSOLE),
            'activeSiteTheme' => $resolver->activeTheme(Theme::SCOPE_SITE),
            'previewTheme' => $resolver->previewThemeFor(auth()->user(), Theme::SCOPE_CONSOLE),
        ]);
    }

    public function previewConsole(Theme $tema, ThemeManager $manager)
    {
        abort_unless($tema->normalizedStatus() === Theme::STATUS_DISPONIVEL, 422, 'Apenas temas disponíveis podem entrar em preview.');
        abort_unless($tema->appliesTo(Theme::SCOPE_CONSOLE), 422, 'Este tema não é compatível com o console.');

        $manager->setPreview($tema);

        return back()->with('ok', 'Preview aplicado no seu console.');
    }

    public function clearPreview(ThemeManager $manager)
    {
        $manager->clearPreview();

        return back()->with('ok', 'Preview encerrado no console.');
    }

    public function activateConsole(Theme $tema, ThemeManager $manager)
    {
        abort_unless($tema->isAvailableFor(Theme::SCOPE_CONSOLE), 422, 'Este tema não está disponível para o console.');

        $manager->activateForScope($tema, Theme::SCOPE_CONSOLE);

        return back()->with('ok', 'Tema aplicado no console.');
    }

    public function activateSite(Theme $tema, ThemeManager $manager)
    {
        abort_unless($tema->isAvailableFor(Theme::SCOPE_SITE), 422, 'Este tema não está disponível para o site.');

        $manager->activateForScope($tema, Theme::SCOPE_SITE);

        return back()->with('ok', 'Tema aplicado no site público.');
    }
    public function restoreConsoleDefault(ThemeManager $manager)
    {
        $manager->restoreDefaultForScope(Theme::SCOPE_CONSOLE);

        return back()->with('ok', 'Tema padrao restaurado no console.');
    }

    public function restoreSiteDefault(ThemeManager $manager)
    {
        $manager->restoreDefaultForScope(Theme::SCOPE_SITE);

        return back()->with('ok', 'Tema padrao restaurado no site publico.');
    }
}
