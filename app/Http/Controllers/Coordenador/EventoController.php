<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\EventoEdicao;
use App\Models\EventoAtrativo;
use App\Models\EventoMidia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventoController extends Controller
{
    /* =========================
     *         EVENTOS
     * ========================= */

    public function index(Request $req)
    {
        $q = trim((string)$req->get('q'));
        $status = $req->get('status');

        $builder = Evento::query()
            ->when($q, function ($qq) use ($q) {
                // ILIKE no Postgres; LIKE nos demais
                $drv = DB::getDriverName();
                if ($drv === 'pgsql') {
                    $qq->where('nome', 'ILIKE', "%{$q}%")
                       ->orWhere('slug', 'ILIKE', "%{$q}%")
                       ->orWhere('cidade','ILIKE', "%{$q}%");
                } else {
                    $qq->where(function($w) use ($q) {
                        $w->where('nome', 'LIKE', "%{$q}%")
                          ->orWhere('slug', 'LIKE', "%{$q}%")
                          ->orWhere('cidade','LIKE', "%{$q}%");
                    });
                }
            })
            ->when($status, fn($qq) => $qq->where('status',$status))
            ->orderByDesc('created_at');

        $eventos = $builder->paginate(20)->withQueryString();

        return view('coordenador.eventos.index', compact('eventos','q','status'));
    }

    public function create()
    {
        $evento = new Evento;
        return view('coordenador.eventos.create', compact('evento'));
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'nome'        => ['required','string','max:255'],
            'slug'        => ['nullable','string','max:255','unique:eventos,slug'],
            'cidade'      => ['nullable','string','max:120'],
            'regiao'      => ['nullable','string','max:120'],
            'descricao'   => ['nullable','string'],
            'status'      => ['nullable','in:publicado,rascunho,arquivado'],
            'capa'        => ['nullable','image','mimes:jpeg,png,webp','max:4096'],
            'perfil'      => ['nullable','image','mimes:jpeg,png,webp','max:2048'],
        ]);

        DB::transaction(function () use ($req, &$data) {
            // slug automático se não veio
            $data['slug'] = $this->uniqueSlug(Evento::class, $data['slug'] ?? Str::slug($data['nome']));

            // uploads
            if ($req->hasFile('capa')) {
                $data['capa_path']   = $req->file('capa')->store('eventos/capas','public');
            }
            if ($req->hasFile('perfil')) {
                $data['perfil_path'] = $req->file('perfil')->store('eventos/perfis','public');
            }

            $data['status'] = $data['status'] ?? 'publicado';

            $evento = Evento::create($data);

            // opcional: cria edição do ano atual já rascunho
            // EventoEdicao::firstOrCreate(['evento_id' => $evento->id, 'ano' => now()->year], ['status' => 'rascunho']);
        });

        return redirect()->route('coordenador.eventos.index')->with('ok','Evento criado com sucesso!');
    }

    public function edit(Evento $evento)
    {
        return view('coordenador.eventos.edit', compact('evento'));
    }

    public function update(Request $req, Evento $evento)
    {
        $data = $req->validate([
            'nome'        => ['required','string','max:255'],
            'slug'        => ['nullable','string','max:255','unique:eventos,slug,'.$evento->id],
            'cidade'      => ['nullable','string','max:120'],
            'regiao'      => ['nullable','string','max:120'],
            'descricao'   => ['nullable','string'],
            'status'      => ['nullable','in:publicado,rascunho,arquivado'],
            'capa'        => ['nullable','image','mimes:jpeg,png,webp','max:4096'],
            'perfil'      => ['nullable','image','mimes:jpeg,png,webp','max:2048'],
            'remove_capa' => ['nullable','boolean'],
            'remove_perfil'=>['nullable','boolean'],
        ]);

        DB::transaction(function () use ($req, $evento, &$data) {
            // slug
            $data['slug'] = $data['slug']
                ? $this->uniqueSlug(Evento::class, $data['slug'], $evento->id)
                : $this->uniqueSlug(Evento::class, Str::slug($data['nome']), $evento->id);

            // troca de imagens
            if ($req->boolean('remove_capa') && $evento->capa_path) {
                Storage::disk('public')->delete($evento->capa_path);
                $data['capa_path'] = null;
            }
            if ($req->boolean('remove_perfil') && $evento->perfil_path) {
                Storage::disk('public')->delete($evento->perfil_path);
                $data['perfil_path'] = null;
            }
            if ($req->hasFile('capa')) {
                if ($evento->capa_path) Storage::disk('public')->delete($evento->capa_path);
                $data['capa_path'] = $req->file('capa')->store('eventos/capas','public');
            }
            if ($req->hasFile('perfil')) {
                if ($evento->perfil_path) Storage::disk('public')->delete($evento->perfil_path);
                $data['perfil_path'] = $req->file('perfil')->store('eventos/perfis','public');
            }

            $evento->update($data);
        });

        return back()->with('ok','Evento atualizado!');
    }

    public function destroy(Evento $evento)
    {
        DB::transaction(function () use ($evento) {
            // apaga mídias de todas as edições
            foreach ($evento->edicoes as $ed) {
                foreach ($ed->midias as $m) {
                    Storage::disk('public')->delete($m->path);
                }
            }
            $evento->delete();
        });
        return redirect()->route('coordenador.eventos.index')->with('ok','Evento removido!');
    }

    /* =========================
     *         EDIÇÕES
     * ========================= */

    public function edicoesIndex(Evento $evento)
    {
        $edicoes = $evento->edicoes()->orderByDesc('ano')->paginate(20);
        return view('coordenador.eventos.edicoes.index', compact('evento','edicoes'));
    }

    public function edicoesCreate(Evento $evento)
    {
        $edicao = new EventoEdicao(['ano' => now()->year]);
        return view('coordenador.eventos.edicoes.create', compact('evento','edicao'));
    }

    public function edicoesStore(Request $req, Evento $evento)
{
    $this->mergeCoordsFromUrl($req); // <== NOVO

    $data = $req->validate([
        'maps_url'   => ['nullable','string','max:2000'], // <== NOVO
        'ano'        => ['required','integer','min:1900','max:2100'],
        'data_inicio'=> ['nullable','date'],
        'data_fim'   => ['nullable','date','after_or_equal:data_inicio'],
        'local'      => ['nullable','string','max:255'],
        'resumo'     => ['nullable','string'],
        'lat'        => ['nullable','numeric','between:-90,90'],
        'lng'        => ['nullable','numeric','between:-180,180'],
        'status'     => ['nullable','in:publicado,rascunho,arquivado'],
    ]);

    $data['status']    = $data['status'] ?? 'publicado';
    $data['evento_id'] = $evento->id;

    // unicidade (evento_id, ano)
    if (EventoEdicao::where('evento_id',$evento->id)->where('ano',$data['ano'])->exists()) {
        return back()->withErrors(['ano'=>'Já existe uma edição para este ano.'])->withInput();
    }

    EventoEdicao::create($data);
    return redirect()->route('coordenador.eventos.edicoes.index', $evento)->with('ok','Edição criada!');
}

public function edicoesUpdate(Request $req, EventoEdicao $edicao)
{
    $this->mergeCoordsFromUrl($req); // <== NOVO

    $data = $req->validate([
        'maps_url'   => ['nullable','string','max:2000'], // <== NOVO
        'ano'        => ['required','integer','min:1900','max:2100'],
        'data_inicio'=> ['nullable','date'],
        'data_fim'   => ['nullable','date','after_or_equal:data_inicio'],
        'local'      => ['nullable','string','max:255'],
        'resumo'     => ['nullable','string'],
        'lat'        => ['nullable','numeric','between:-90,90'],
        'lng'        => ['nullable','numeric','between:-180,180'],
        'status'     => ['nullable','in:publicado,rascunho,arquivado'],
    ]);

    // unicidade do ano dentro do evento
    $dup = EventoEdicao::where('evento_id',$edicao->evento_id)
            ->where('ano',$data['ano'])
            ->where('id','!=',$edicao->id)
            ->exists();
    if ($dup) {
        return back()->withErrors(['ano'=>'Já existe outra edição com este ano.'])->withInput();
    }

    $edicao->update($data);
    return back()->with('ok','Edição atualizada!');
}

public function edicoesEdit(Evento $evento, EventoEdicao $edicao)
{
    // garante que o evento veio certo mesmo se a rota não for aninhada
    if (!$evento || ($edicao->evento_id !== $evento->id)) {
        $evento = $edicao->evento; // fallback via relacionamento
    }

    return view('coordenador.eventos.edicoes.edit', compact('evento','edicao'));
}



    public function edicoesDestroy(EventoEdicao $edicao)
    {
        DB::transaction(function () use ($edicao) {
            foreach ($edicao->midias as $m) {
                Storage::disk('public')->delete($m->path);
            }
            $edicao->delete();
        });

        return redirect()
            ->route('coordenador.eventos.edicoes.index', $edicao->evento_id)
            ->with('ok','Edição removida!');
    }

    /* =========================
     *        ATRATIVOS
     * ========================= */

    public function atrativosIndex(EventoEdicao $edicao)
    {
        $atrativos = $edicao->atrativos()->orderBy('ordem')->paginate(50);
        return view('coordenador.eventos.atrativos.index', compact('edicao','atrativos'));
    }

    public function atrativosCreate(EventoEdicao $edicao)
    {
        $atrativo = new EventoAtrativo(['ordem' => ($edicao->atrativos()->max('ordem') ?? 0) + 1]);
        return view('coordenador.eventos.atrativos.create', compact('edicao','atrativo'));
    }

    public function atrativosStore(Request $req, EventoEdicao $edicao)
    {
        $data = $req->validate([
            'nome'       => ['required','string','max:255'],
            'slug'       => ['nullable','string','max:255'],
            'descricao'  => ['nullable','string'],
            'ordem'      => ['nullable','integer','min:1'],
            'status'     => ['nullable','in:publicado,rascunho,arquivado'],
            'thumb'      => ['nullable','image','mimes:jpeg,png,webp','max:4096'],
        ]);

        $data['slug']   = $this->uniqueSlugWithinEdition($edicao->id, $data['slug'] ?: Str::slug($data['nome']));
        $data['status'] = $data['status'] ?? 'publicado';
        $data['edicao_id'] = $edicao->id;
        $data['ordem']  = $data['ordem'] ?? (($edicao->atrativos()->max('ordem') ?? 0)+1);

        if ($req->hasFile('thumb')) {
            $data['thumb_path'] = $req->file('thumb')->store("eventos/edicoes/{$edicao->id}/atrativos",'public');
        }

        EventoAtrativo::create($data);

        return redirect()->route('coordenador.edicoes.atrativos.index', $edicao)->with('ok','Atrativo criado!');
    }

    public function atrativosEdit(EventoAtrativo $atrativo)
    {
        $edicao = $atrativo->edicao;
        return view('coordenador.eventos.atrativos.edit', compact('edicao','atrativo'));
    }

    public function atrativosUpdate(Request $req, EventoAtrativo $atrativo)
    {
        $data = $req->validate([
            'nome'       => ['required','string','max:255'],
            'slug'       => ['nullable','string','max:255'],
            'descricao'  => ['nullable','string'],
            'ordem'      => ['nullable','integer','min:1'],
            'status'     => ['nullable','in:publicado,rascunho,arquivado'],
            'thumb'      => ['nullable','image','mimes:jpeg,png,webp','max:4096'],
            'remove_thumb' => ['nullable','boolean'],
        ]);

        $data['slug'] = $data['slug']
          ? $this->uniqueSlugWithinEdition($atrativo->edicao_id, $data['slug'], $atrativo->id)
          : $this->uniqueSlugWithinEdition($atrativo->edicao_id, Str::slug($data['nome']), $atrativo->id);

        if ($req->boolean('remove_thumb') && $atrativo->thumb_path) {
            Storage::disk('public')->delete($atrativo->thumb_path);
            $data['thumb_path'] = null;
        }
        if ($req->hasFile('thumb')) {
            if ($atrativo->thumb_path) Storage::disk('public')->delete($atrativo->thumb_path);
            $data['thumb_path'] = $req->file('thumb')->store("eventos/edicoes/{$atrativo->edicao_id}/atrativos",'public');
        }

        $atrativo->update($data);

        return back()->with('ok','Atrativo atualizado!');
    }

    public function atrativosDestroy(EventoAtrativo $atrativo)
    {
        if ($atrativo->thumb_path) Storage::disk('public')->delete($atrativo->thumb_path);
        $edicaoId = $atrativo->edicao_id;
        $atrativo->delete();

        return redirect()->route('coordenador.edicoes.atrativos.index', $edicaoId)->with('ok','Atrativo removido!');
    }

    public function atrativosReordenar(Request $req, EventoEdicao $edicao)
    {
        $data = $req->validate([
            'ordem' => ['required','array','min:1'],   // ['atrativo_id' => nova_ordem, ...]
        ]);

        DB::transaction(function () use ($data, $edicao) {
            foreach ($data['ordem'] as $id => $ordem) {
                EventoAtrativo::where('edicao_id',$edicao->id)->where('id',$id)->update(['ordem' => (int)$ordem]);
            }
        });

        return back()->with('ok','Ordem atualizada!');
    }

    /* =========================
     *          MÍDIAS
     * ========================= */

    public function midiasIndex(EventoEdicao $edicao)
    {
        $midias = $edicao->midias()->orderBy('ordem')->paginate(60);
        return view('coordenador.eventos.midias.index', compact('edicao','midias'));
    }

    public function midiasStore(Request $req, EventoEdicao $edicao)
    {
        $data = $req->validate([
            'fotos'   => ['required','array','min:1'],
            'fotos.*' => ['file','image','mimes:jpeg,png,webp','max:6144'],
        ]);

        DB::transaction(function () use ($data, $edicao, $req) {
            $baseOrder = (int) ($edicao->midias()->max('ordem') ?? 0);
            $i = 1;
            foreach ($req->file('fotos') as $file) {
                $path = $file->store("eventos/edicoes/{$edicao->id}/galeria",'public');
                EventoMidia::create([
                    'edicao_id' => $edicao->id,
                    'path'      => $path,
                    'alt'       => null,
                    'ordem'     => $baseOrder + $i,
                    'tipo'      => 'foto',
                ]);
                $i++;
            }
        });

        return back()->with('ok','Fotos adicionadas!');
    }

    public function midiasDestroy(EventoMidia $midia)
    {
        Storage::disk('public')->delete($midia->path);
        $edicaoId = $midia->edicao_id;
        $midia->delete();

        return redirect()->route('coordenador.edicoes.midias.index', $edicaoId)->with('ok','Mídia removida!');
    }

    public function midiasReordenar(Request $req, EventoEdicao $edicao)
    {
        $data = $req->validate([
            'ordem' => ['required','array','min:1'], // ['midia_id' => nova_ordem, ...]
        ]);

        DB::transaction(function () use ($data, $edicao) {
            foreach ($data['ordem'] as $id => $ordem) {
                EventoMidia::where('edicao_id',$edicao->id)->where('id',$id)->update(['ordem' => (int)$ordem]);
            }
        });

        return back()->with('ok','Ordem da galeria atualizada!');
    }

    // === [UTILS] URL -> LAT/LNG (mesmo padrão das Empresas) ===
    private function extractCoordsFromUrl(?string $url): array
    {
        $lat = $lng = null;
        if (!$url) return [null, null];
        $s = urldecode(trim($url));

        // Google: .../@LAT,LNG, ...
        if (preg_match('~@\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)~', $s, $m)) {
            return [(float)$m[1], (float)$m[2]];
        }
        // Google: ?q=LAT,LNG ou ?ll=LAT,LNG
        if (preg_match('~[?&](?:q|ll)=\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)~i', $s, $m)) {
            return [(float)$m[1], (float)$m[2]];
        }
        // Google share encurtado: ...!3dLAT!4dLNG (às vezes invertido)
        if (preg_match('~!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)~', $s, $m)) {
            return [(float)$m[1], (float)$m[2]];
        }
        if (preg_match('~!4d(-?\d+(?:\.\d+)?)!3d(-?\d+(?:\.\d+)?)~', $s, $m)) {
            return [(float)$m[2], (float)$m[1]];
        }
        // Bing: cp=LAT~LNG
        if (preg_match('~[?&]cp=(-?\d+(?:\.\d+)?)\~(-?\d+(?:\.\d+)?)~i', $s, $m)) {
            return [(float)$m[1], (float)$m[2]];
        }
        // Bing: sp=point.LAT_LNG_...
        if (preg_match('~[?&]sp=point\.(-?\d+(?:\.\d+)?)_(-?\d+(?:\.\d+)?)~i', $s, $m)) {
            return [(float)$m[1], (float)$m[2]];
        }
        // OpenStreetMap: ?mlat=...&mlon=...
        if (preg_match('~[?&]mlat=(-?\d+(?:\.\d+)?)~i', $s, $latM) &&
            preg_match('~[?&]mlon=(-?\d+(?:\.\d+)?)~i', $s, $lonM)) {
            return [(float)$latM[1], (float)$lonM[1]];
        }
        return [null, null];
    }

    private function mergeCoordsFromUrl(Request $req): void
    {
        [$lat, $lng] = $this->extractCoordsFromUrl($req->input('maps_url'));
        if ($lat !== null && $lng !== null) {
            $req->merge(['lat' => $lat, 'lng' => $lng]);
        }
    }


    /* =========================
     *       HELPERS PRIVADOS
     * ========================= */

    private function uniqueSlug(string $modelClass, string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base);
        $original = $slug;
        $i = 2;
        while ($modelClass::where('slug',$slug)
                ->when($ignoreId, fn($q) => $q->where('id','!=',$ignoreId))
                ->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }
        return $slug;
    }

    private function uniqueSlugWithinEdition(int $edicaoId, string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base);
        $original = $slug;
        $i = 2;
        while (EventoAtrativo::where('edicao_id',$edicaoId)
                ->where('slug',$slug)
                ->when($ignoreId, fn($q) => $q->where('id','!=',$ignoreId))
                ->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }
        return $slug;
    }
}
