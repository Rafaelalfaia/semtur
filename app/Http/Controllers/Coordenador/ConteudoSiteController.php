<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveConteudoSiteBlocoRequest;
use App\Models\Conteudo\ConteudoSiteBloco;
use App\Models\Conteudo\ConteudoSiteBlocoTraducao;
use App\Models\Conteudo\ConteudoSiteMidia;
use App\Models\Idioma;
use App\Services\ConteudoSiteTranslationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ConteudoSiteController extends Controller
{
    public function update(
        SaveConteudoSiteBlocoRequest $request,
        string $pagina,
        string $chave,
        ConteudoSiteTranslationManager $translationManager,
    ): RedirectResponse {
        $idioma = Idioma::query()
            ->where('codigo', $request->string('locale')->toString())
            ->firstOrFail();

        $bloco = DB::transaction(function () use ($request, $pagina, $chave, $idioma) {
            $bloco = ConteudoSiteBloco::query()->firstOrNew([
                'pagina' => $pagina,
                'chave' => $chave,
            ]);

            $bloco->fill([
                'rotulo' => $this->nullable($request->input('rotulo')) ?: $this->defaultLabel($pagina, $chave),
                'tipo' => $this->nullable($request->input('tipo')) ?: 'hero',
                'regiao' => $this->nullable($request->input('regiao')) ?: 'topo',
                'ordem' => 0,
                'status' => $request->input('status'),
            ]);

            if (! $bloco->exists) {
                $bloco->created_by = $request->user()?->id;
            }

            $bloco->updated_by = $request->user()?->id;
            $bloco->save();

            $traducao = ConteudoSiteBlocoTraducao::query()->firstOrNew([
                'conteudo_site_bloco_id' => $bloco->id,
                'idioma_id' => $idioma->id,
            ]);

            $isDefaultLocale = $idioma->codigo === config('app.locale_prefix_fallback', 'pt');

            $traducao->fill([
                'eyebrow' => $this->nullable($request->input('eyebrow')),
                'titulo' => $this->nullable($request->input('titulo')),
                'subtitulo' => $this->nullable($request->input('subtitulo')),
                'lead' => $this->nullable($request->input('lead')),
                'conteudo' => $this->nullable($request->input('conteudo')),
                'cta_label' => $this->nullable($request->input('cta_label')),
                'cta_href' => $this->nullable($request->input('cta_href')),
                'seo_title' => $this->nullable($request->input('seo_title')),
                'seo_description' => $this->nullable($request->input('seo_description')),
                'extras' => $traducao->extras ?? [],
                'is_auto_translated' => false,
                'auto_translated_at' => null,
                'reviewed_at' => $isDefaultLocale ? null : now(),
                'source_locale' => $idioma->codigo,
                'source_hash' => null,
            ]);
            $traducao->save();

            $this->syncMedia($request, $bloco);

            return $bloco->fresh(['traducoes.idioma', 'midias']);
        });

        $locale = $idioma->codigo;
        $defaultLocale = (string) config('app.locale_prefix_fallback', 'pt');

        if ($locale === $defaultLocale) {
            $translationManager->traduzirBlocoParaIdiomasAlvo($bloco, $defaultLocale);
        }

        return back()->with('ok', 'Conteudo editorial atualizado com sucesso.');
    }

    private function syncMedia(SaveConteudoSiteBlocoRequest $request, ConteudoSiteBloco $bloco): void
    {
        $slot = $this->resolveMediaSlot($request);
        $existing = ConteudoSiteMidia::query()
            ->where('conteudo_site_bloco_id', $bloco->id)
            ->where('slot', $slot)
            ->whereNull('idioma_id')
            ->first();

        if (($request->boolean('remover_media') || $request->boolean('remover_hero')) && $existing) {
            Storage::disk($existing->disk ?: 'public')->delete($existing->path);
            $existing->delete();
            $existing = null;
        }

        $file = $request->file('media') ?: $request->file('hero');

        if (! $file) {
            return;
        }

        if ($existing) {
            Storage::disk($existing->disk ?: 'public')->delete($existing->path);
        } else {
            $existing = new ConteudoSiteMidia([
                'conteudo_site_bloco_id' => $bloco->id,
                'slot' => $slot,
                'disk' => 'public',
                'ordem' => 0,
            ]);
        }

        $existing->fill([
            'path' => ltrim($file->store('conteudo-site/'.$slot, 'public'), '/'),
            'mime_type' => $file->getMimeType(),
            'tamanho_bytes' => $file->getSize(),
            'alt_text' => $this->nullable($request->input('titulo')),
        ]);
        $existing->save();
    }

    private function resolveMediaSlot(SaveConteudoSiteBlocoRequest $request): string
    {
        $slot = $this->nullable($request->input('media_slot')) ?: 'hero';
        $slot = Str::of($slot)
            ->lower()
            ->replaceMatches('/[^a-z0-9._-]+/', '-')
            ->trim('-')
            ->value();

        return $slot !== '' ? $slot : 'hero';
    }

    private function defaultLabel(string $pagina, string $chave): string
    {
        return trim($pagina.' '.$chave);
    }

    private function nullable(mixed $value): ?string
    {
        if (! is_scalar($value) && $value !== null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
