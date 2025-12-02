<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\{PontoTuristico, PontoMidia, PontoRecomendacao, Categoria, Empresa};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class PontoTuristicoController extends Controller
{
    public function index(\Illuminate\Http\Request $r)
    {
        $busca  = trim((string)$r->input('busca',''));
        $status = $r->input('status');

        $isPg = \DB::getDriverName()==='pgsql';
        $like = $isPg ? 'ilike' : 'like';

        $q = \App\Models\Catalogo\PontoTuristico::query()
            ->when($status && $status!=='todos', fn($qq)=>$qq->where('status',$status))
            ->when($busca!=='' , fn($qq)=>$qq->where(fn($w)=>$w
                ->where('nome',$like,"%{$busca}%")->orWhere('descricao',$like,"%{$busca}%")))
            ->with(['midias' => fn($m)=>$m->orderBy('ordem')->limit(1)]) // pra usar $ponto->capa_url sem N+1
            ->orderBy('ordem')->orderBy('nome');

        $pontos = $q->paginate(12)->withQueryString();

        return view('coordenador.pontos.index', compact('pontos','status','busca'));
    }




    public function create()
    {
        $ponto = new PontoTuristico(['status'=>PontoTuristico::RASCUNHO,'ordem'=>0]);

        return view('coordenador.pontos.create', [
            'ponto'      => $ponto,
            'categorias' => Categoria::orderBy('nome')->get(['id','nome']),
            'empresas'   => Empresa::orderBy('nome')->get(['id','nome']),
        ]);
    }

    public function store(Request $request)
    {
        // Extrai lat/lng da URL do mapa (Google/Bing/OSM)
        $this->mergeCoordsFromUrl($request);

        // 🔐 Idempotência por sessão: se já processou este form, não cria de novo
        if ($this->alreadyProcessed($request)) {
            return redirect()->route('coordenador.pontos.index')
                ->with('ok', 'Ponto já criado.');
        }

        $dados = $request->validate([
            'nome'        => ['required','string','max:190'],
            'status'      => ['required', Rule::in(['rascunho','publicado','arquivado'])],

            'maps_url'    => ['nullable','url','max:2048'],
            'lat'         => ['nullable','numeric','between:-90,90'],
            'lng'         => ['nullable','numeric','between:-180,180'],

            'descricao'   => ['nullable','string'],
            'ordem'       => ['nullable','integer','min:0'],

            'capa'        => ['nullable','image','max:6144'], // 6MB
            'galeria.*'   => ['nullable','image','max:6144'],

            'categorias'   => ['array'],
            'categorias.*' => ['integer','exists:categorias,id'],

            'empresas'     => ['array'],
            'empresas.*'   => ['integer','exists:empresas,id'],
        ]);

        // Se for publicar, exige coordenadas válidas
        if ($dados['status'] === 'publicado' && (empty($dados['lat']) || empty($dados['lng']))) {
            return back()->withErrors([
                'maps_url' => 'Para publicar, cole um link de mapa que contenha as coordenadas.',
            ])->withInput();
        }

        $ponto = null;

        DB::transaction(function () use ($request, $dados, &$ponto) {
            // ✅ Cria UMA única vez
            $ponto = new PontoTuristico();
            $ponto->nome        = $dados['nome'];
            $ponto->slug        = $this->makeUniqueSlug($dados['nome']);
            $ponto->descricao   = $dados['descricao'] ?? null;
            $ponto->status      = $dados['status'];
            $ponto->ordem       = $dados['ordem'] ?? 0;

            // Localização
            $ponto->maps_url    = $dados['maps_url'] ?? null;
            $ponto->lat         = $dados['lat'] ?? null;
            $ponto->lng         = $dados['lng'] ?? null;

            if ($dados['status']==='publicado' && empty($ponto->published_at)) {
                $ponto->published_at = now();
            }

            $ponto->save(); // garante ID

            // Relações
            $ponto->categorias()->sync($dados['categorias'] ?? []);
            if (method_exists($ponto, 'empresas')) {
                $ponto->empresas()->sync($dados['empresas'] ?? []);
            }

            // Upload da capa (coluna flexível)
            if ($request->hasFile('capa')) {
                $path = $request->file('capa')->store('pontos/capas', 'public');
                $path = ltrim($path, '/');

                $capaCol = collect(['capa_path','foto_capa_path','capa'])
                    ->first(fn($c) => Schema::hasColumn('pontos_turisticos', $c));

                if ($capaCol) {
                    $ponto->{$capaCol} = $path;
                    $ponto->save();
                }
            }

            // Upload da galeria (opcional)
            if ($request->hasFile('galeria')) {
                foreach ($request->file('galeria') as $file) {
                    if (!$file || !$file->isValid()) continue;
                    $p = $file->store('pontos/galeria', 'public');
                    if (method_exists($ponto, 'midias')) {
                        $ponto->midias()->create([
                            'path'  => ltrim($p,'/'),
                            'tipo'  => 'image',
                            'ordem' => 0,
                        ]);
                    }
                }
            }
        });

        // 🔐 marca o form como processado após salvar
        if ($ponto?->id) {
            $this->markFormProcessed($request, $ponto->id);
        }

        // ✅ volta ao INDEX
        return redirect()
            ->route('coordenador.pontos.index')
            ->with('ok', 'Ponto criado com sucesso.');
    }


// Marca o token de formulário como processado (guarda o ID criado)
private function markFormProcessed(Request $request, int $id): void
{
    $token = (string) $request->input('form_token');
    if ($token !== '') {
        session(["forms.processed.$token" => $id]);
    }
}

// Verifica se já processou este token; retorna o ID se sim
private function alreadyProcessed(Request $request): ?int
{
    $token = (string) $request->input('form_token');
    if ($token !== '' && session()->has("forms.processed.$token")) {
        return (int) session("forms.processed.$token");
    }
    return null;
}

    public function edit(PontoTuristico $ponto)
    {
        return view('coordenador.pontos.edit', [
            'ponto'      => $ponto->load('midias','recomendacoes','categorias','empresas'),
            'categorias' => Categoria::orderBy('nome')->get(['id','nome']),
            'empresas'   => Empresa::orderBy('nome')->get(['id','nome']),
        ]);
    }

    public function update(Request $request, PontoTuristico $ponto)
    {
        $this->mergeCoordsFromUrl($request);
        $dados = $request->validate([
        'nome'        => ['required','string','max:190'],
        'status'      => ['required', Rule::in(['rascunho','publicado','arquivado'])],

        'maps_url'    => ['nullable','url','max:2048'],
        'lat'         => ['nullable','numeric','between:-90,90'],
        'lng'         => ['nullable','numeric','between:-180,180'],

        'descricao'   => ['nullable','string'],
        'capa'        => ['nullable','image','max:5120'],
        'galeria.*'   => ['nullable','image','max:5120'],
        'categorias'  => ['nullable','array'],
        'categorias.*'=> ['integer','exists:categorias,id'],
        'empresas'    => ['nullable','array'],
        'empresas.*'  => ['integer','exists:empresas,id'],
    ]);

        if ($dados['status'] === 'publicado' && (empty($dados['lat']) || empty($dados['lng']))) {
            return back()->withErrors([
                'maps_url' => 'Para publicar, cole um link de mapa que contenha as coordenadas.',
            ])->withInput();
        }

        $ponto->nome      = $dados['nome'];
        $ponto->descricao = $dados['descricao'] ?? null;
        $ponto->status    = $dados['status'];
        $ponto->maps_url  = $dados['maps_url'] ?? null;
        $ponto->lat       = $dados['lat'] ?? null;
        $ponto->lng       = $dados['lng'] ?? null;
        // ...demais campos...
        $ponto->save();

        return DB::transaction(function() use ($request, $ponto, $dados) {

            $ponto->fill($dados);

            if (empty($ponto->slug) && !empty($ponto->nome)) {
                $ponto->slug = Str::slug($ponto->nome);
            }

            if ($dados['status']==='publicado' && empty($ponto->published_at)) {
                $ponto->published_at = now();
            }

            $ponto->save();

            $ponto->categorias()->sync($dados['categorias'] ?? []);
            if (method_exists($ponto, 'empresas')) {
                $ponto->empresas()->sync($dados['empresas'] ?? []);
            }

            // Upload capa
            if ($request->hasFile('capa')) {
                $capaCol = collect(['capa_path','foto_capa_path','capa'])
                    ->first(fn($c) => Schema::hasColumn('pontos_turisticos', $c));

                if ($capaCol) {
                    if (!empty($ponto->{$capaCol})) {
                        \Storage::disk('public')->delete($ponto->{$capaCol});
                    }
                    $path = $request->file('capa')->store('pontos/capas', 'public');
                    $ponto->{$capaCol} = ltrim($path, '/');
                    $ponto->save();
                }
            }


            if ($request->hasFile('galeria')) {
                foreach ($request->file('galeria') as $file) {
                    $p = $file->store('pontos/galeria', 'public');
                    if (method_exists($ponto, 'midias')) {
                        $ponto->midias()->create(['path' => ltrim($p,'/'), 'ordem' => 0]);
                    }
                }
            }

            return back()->with('ok', 'Ponto atualizado com sucesso.');
        });
    }

    // Aceita Google, Bing e OpenStreetMap
    private function extractCoordsFromUrl(?string $url): array
    {
        $lat = $lng = null;
        if (!$url) return [null, null];

        $s = urldecode(trim($url));

        // Google: .../@LAT,LNG,...
        if (preg_match('~@\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)~', $s, $m)) return [(float)$m[1], (float)$m[2]];
        // Google: ?q=LAT,LNG ou ?ll=LAT,LNG
        if (preg_match('~[?&](?:q|ll)=\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)~i', $s, $m)) return [(float)$m[1], (float)$m[2]];
        // Google: !3dLAT!4dLNG (ou invertido)
        if (preg_match('~!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)~', $s, $m)) return [(float)$m[1], (float)$m[2]];
        if (preg_match('~!4d(-?\d+(?:\.\d+)?)!3d(-?\d+(?:\.\d+)?)~', $s, $m)) return [(float)$m[2], (float)$m[1]];

        // Bing: cp=LAT~LNG  (escape do til)
        if (preg_match('~[?&]cp=(-?\d+(?:\.\d+)?)\~(-?\d+(?:\.\d+)?)~i', $s, $m)) return [(float)$m[1], (float)$m[2]];
        // Bing: sp=point.LAT_LNG_...
        if (preg_match('~[?&]sp=point\.(-?\d+(?:\.\d+)?)_(-?\d+(?:\.\d+)?)~i', $s, $m)) return [(float)$m[1], (float)$m[2]];

        // OpenStreetMap: ?mlat=...&mlon=...  ou  ?lat=...&lon=...
        if (preg_match('~[?&](?:mlat|lat)=(-?\d+(?:\.\d+)?)~i', $s, $ma)
        && preg_match('~[?&](?:mlon|lon|lng)=(-?\d+(?:\.\d+)?)~i', $s, $mb)) return [(float)$ma[1], (float)$mb[1]];

        // Genérico: "LAT, LNG"
        if (preg_match('~(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)~', $s, $m)) return [(float)$m[1], (float)$m[2]];

        return [null, null];
    }

    private function mergeCoordsFromUrl(\Illuminate\Http\Request $req): void
    {
        [$lat, $lng] = $this->extractCoordsFromUrl($req->input('maps_url'));
        if ($lat !== null && $lng !== null) {
            $req->merge(['lat' => $lat, 'lng' => $lng]);
        }
    }


    private function makeUniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base) ?: 'ponto';
        $i = 2;

        $exists = function($s) use ($ignoreId) {
            $q = PontoTuristico::withTrashed()->where('slug', $s);
            if ($ignoreId) $q->where('id','<>',$ignoreId);
            return $q->exists();
        };

        while ($exists($slug)) {
            $slug = Str::slug($base.'-'.$i);
            $i++;
        }
        return $slug;
    }



    public function publicar(Request $request, PontoTuristico $ponto)
    {
        if (empty($ponto->lat) || empty($ponto->lng)) {
            $request->validate([
                'lat' => ['required','numeric','between:-90,90'],
                'lng' => ['required','numeric','between:-180,180'],
            ]);
            $ponto->lat = $request->float('lat');
            $ponto->lng = $request->float('lng');
        }

        $ponto->status = 'publicado';
        $ponto->published_at ??= now();
        $ponto->save();

        return back()->with('ok','Ponto publicado.');
    }


    public function arquivar(PontoTuristico $ponto)
    {
        $ponto->update([
            'status'       => PontoTuristico::ARQUIVADO,
            'published_at' => null,
        ]);
        return back()->with('ok','Ponto arquivado.');
    }

    public function rascunho(PontoTuristico $ponto)
    {
        $ponto->status = 'rascunho';
        $ponto->save();

        return back()->with('ok','Ponto movido para rascunho.');
    }

    public function destroy(PontoTuristico $ponto)
    {
        $ponto->delete();
        return back()->with('ok','Ponto movido para a lixeira.');
    }

    public function removerCapa(PontoTuristico $ponto)
    {
        if ($ponto->capa_path && Storage::disk('public')->exists($ponto->capa_path)) {
            Storage::disk('public')->delete($ponto->capa_path);
        }
        $ponto->update(['capa_path'=>null]);
        return back()->with('ok','Capa removida.');
    }

    /* ===== Mídias ===== */
    public function adicionarImagens(Request $r, PontoTuristico $ponto)
    {
        $r->validate(['imagens.*'=>['required','file','mimes:png,jpg,jpeg,webp','max:4096']]);

        foreach ($r->file('imagens', []) as $file) {
            if (!$file->isValid()) continue;
            $path = str_replace('\\','/',$file->store('pontos/galeria','public'));
            PontoMidia::create([
                'ponto_turistico_id' => $ponto->id,
                'tipo'               => 'image',           // correto
                'path'               => ltrim($path,'/'),  // correto
                'ordem'              => 0,
            ]);
        }
        return back()->with('ok','Imagens adicionadas.');
    }

    public function adicionarVideoLink(Request $r, PontoTuristico $ponto)
    {
        $r->validate(['video_url'=>['required','url','max:500']]);

        PontoMidia::create([
            'ponto_turistico_id' => $ponto->id,
            'tipo'               => 'video_link',
            'url'                => $r->video_url,
            'ordem'              => 0,
        ]);
        return back()->with('ok','Vídeo (link) adicionado.');
    }

    public function adicionarVideoFile(Request $r, PontoTuristico $ponto)
    {
        $r->validate(['video_file'=>['required','file','mimes:mp4,webm','max:20480']]);

        $path = str_replace('\\','/',$r->file('video_file')->store('pontos/videos','public'));
        PontoMidia::create([
            'ponto_turistico_id' => $ponto->id,
            'tipo'               => 'video_file',
            'path'               => ltrim($path,'/'),
            'ordem'              => 0,
        ]);

        return back()->with('ok','Vídeo enviado.');
    }

    public function removerMidia(PontoMidia $midia)
    {
        if (in_array($midia->tipo,['image','video_file'], true) && $midia->path) {
            if (Storage::disk('public')->exists($midia->path)) {
                Storage::disk('public')->delete($midia->path);
            }
        }
        $midia->delete();
        return back()->with('ok','Mídia removida.');
    }

    /* ===== Recomendações / Destaques ===== */
    public function recomendar(Request $r, PontoTuristico $ponto)
    {
        $data = $r->validate([
            'contexto'      => ['required', Rule::in(['global','categoria'])],
            'categoria_id'  => ['nullable','integer','exists:categorias,id'], // corrigido
            'ordem'         => ['nullable','integer','min:0'],
            'inicio_em'     => ['nullable','date'],
            'fim_em'        => ['nullable','date','after_or_equal:inicio_em'],
            'ativo_forcado' => ['nullable','boolean'],
        ]);

        $categoriaId = $data['contexto'] === 'categoria' ? ($data['categoria_id'] ?? null) : null;

        $rec = PontoRecomendacao::withTrashed()->firstOrNew([
            'ponto_turistico_id' => $ponto->id,
            'categoria_id'       => $categoriaId,
        ]);

        $rec->fill([
            'ordem'         => $data['ordem'] ?? 0,
            'inicio_em'     => $data['inicio_em'] ?? null,
            'fim_em'        => $data['fim_em'] ?? null,
            'ativo_forcado' => (bool)($data['ativo_forcado'] ?? false),
            'created_by'    => auth()->id(),
        ]);

        if ($rec->trashed()) $rec->restore();
        $rec->save();

        return back()->with('ok','Ponto destacado com sucesso.');
    }

    public function removerRecomendacao(Request $r, PontoTuristico $ponto)
    {
        $data = $r->validate([
            'contexto'     => ['required', Rule::in(['global','categoria'])],
            'categoria_id' => ['nullable','integer','exists:categorias,id'],
        ]);

        $q = PontoRecomendacao::where('ponto_turistico_id', $ponto->id);

        if ($data['contexto']==='categoria') {
            if (empty($data['categoria_id'])) {
                return back()->withErrors(['categoria_id'=>'Selecione a categoria.']);
            }
            $q->where('categoria_id', $data['categoria_id']);
        } else {
            $q->whereNull('categoria_id'); // global
        }

        $q->delete();

        return back()->with('ok','Destaque removido.');
    }

    /* ===== Validação base ===== */
    private function validated(Request $r, ?int $id): array
    {
        return $r->validate([
            'nome'        => ['required','string','max:160'],
            'slug'        => ['nullable','string','max:180', Rule::unique('pontos_turisticos','slug')->ignore($id)],
            'descricao'   => ['nullable','string','max:12000'],

            'maps_url'    => ['nullable','url'],
            'endereco'    => ['nullable','string','max:255'],
            'bairro'      => ['nullable','string','max:120'],
            'cidade'      => ['nullable','string','max:120'],
            'lat'         => ['nullable','numeric'],
            'lng'         => ['nullable','numeric'],

            'capa'        => ['nullable','file','mimes:png,jpg,jpeg,webp','max:4096'],
            'galeria'     => ['nullable','array'],
            'galeria.*'   => ['nullable','file','mimes:png,jpg,jpeg,webp','max:4096'],

            'ordem'       => ['nullable','integer','min:0'],
            'status'      => ['required', Rule::in([
                PontoTuristico::RASCUNHO,
                PontoTuristico::PUBLICADO,
                PontoTuristico::ARQUIVADO,
            ])],

            'categorias'   => ['nullable','array'],
            'categorias.*' => ['integer','exists:categorias,id'],

            'empresas'     => ['nullable','array'],
            'empresas.*'   => ['integer','exists:empresas,id'],
        ]);
    }
}
