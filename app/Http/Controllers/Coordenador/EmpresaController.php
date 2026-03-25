<?php

namespace App\Http\Controllers\Coordenador;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Models\Catalogo\EmpresaRecomendacao;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\EmpresaFoto;
use Illuminate\Support\Facades\Schema;

class EmpresaController extends Controller
{
    public function index(Request $request)
    {
        $busca  = trim((string)$request->input('busca',''));
        $status = $request->input('status');
        $buscaAtiva = mb_strlen($busca) >= 3;

        $q = Empresa::query()
            ->when($buscaAtiva && $status && $status!=='todos', fn($qq)=>$qq->where('status',$status))
            ->when($buscaAtiva , function($qq) use($busca){
                $like = DB::connection()->getDriverName()==='pgsql' ? 'ilike' : 'like';
                $qq->where(function($w) use($busca,$like){
                    $w->where('nome',$like,"%{$busca}%")
                      ->orWhere('descricao',$like,"%{$busca}%");
                });
            }, fn($qq) => $qq->whereRaw('1 = 0'))
            // Flag "em_destaque" para Home (global = categoria_id null) — evita N+1:
            ->withExists(['recomendacoes as em_destaque' => function($q){
                $q->whereNull('categoria_id')
                  ->where(function($w){
                      $w->where('ativo_forcado', true)
                        ->orWhere(function($p){
                            $p->where(function($d){
                                  $d->whereNull('inicio_em')->orWhere('inicio_em','<=', now());
                              })
                              ->where(function($d){
                                  $d->whereNull('fim_em')->orWhere('fim_em','>=', now());
                              });
                        });
                  });
            }])
            ->orderBy('ordem')->orderBy('nome');

        $empresas = $q->paginate(12)->withQueryString();

        return view('coordenador.empresas.index', compact('empresas','busca','status'));
    }

    public function create()
    {
        $empresa       = new Empresa(['status'=>Empresa::STATUS_RASCUNHO,'ordem'=>0]);
        $categorias    = Categoria::orderBy('ordem')->orderBy('nome')->get(['id','nome']);
        $selecionadas  = [];
        return view('coordenador.empresas.create', compact('empresa','categorias','selecionadas'));
    }

    private function makeUniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $i = 2;

        $exists = function($s) use ($ignoreId) {
            $q = Empresa::withTrashed()->where('slug', $s);
            if ($ignoreId) $q->where('id','<>',$ignoreId);
            return $q->exists();
        };

        while ($exists($slug)) {
            $slug = $base.'-'.$i;
            $i++;
        }
        return $slug;
    }

    public function show(string $slugOrId)
    {
        $empresa = Empresa::query()
            ->when(is_numeric($slugOrId),
                fn($q) => $q->where('id', (int) $slugOrId),
                fn($q) => $q->where('slug', $slugOrId)
            )
            ->with(['categorias', 'galeriaFotos'])
            ->firstOrFail();

        return view('site.empresas.show', compact('empresa'));
    }

    // -----------------------
    // LOCALIZAÇÃO (URL -> lat/lng)
    // -----------------------

    // Extrai coordenadas de URLs do Google, Bing e OpenStreetMap (e variantes comuns)
   // Extrai coordenadas de URLs do Google, Bing e OpenStreetMap (e variantes)
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

    // Google: !3dLAT!4dLNG (ou invertido)
    if (preg_match('~!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)~', $s, $m)) {
        return [(float)$m[1], (float)$m[2]];
    }
    if (preg_match('~!4d(-?\d+(?:\.\d+)?)!3d(-?\d+(?:\.\d+)?)~', $s, $m)) {
        return [(float)$m[2], (float)$m[1]];
    }

    // Bing: cp=LAT~LNG   (ESCAPE do til interno \~)
    if (preg_match('~[?&]cp=(-?\d+(?:\.\d+)?)\~(-?\d+(?:\.\d+)?)~i', $s, $m)) {
        return [(float)$m[1], (float)$m[2]];
    }

    // Bing: sp=point.LAT_LNG_...
    if (preg_match('~[?&]sp=point\.(-?\d+(?:\.\d+)?)_(-?\d+(?:\.\d+)?)~i', $s, $m)) {
        return [(float)$m[1], (float)$m[2]];
    }

    // OpenStreetMap: ?mlat=...&mlon=...  ou  ?lat=...&lon=...
    if (preg_match('~[?&](?:mlat|lat)=(-?\d+(?:\.\d+)?)~i', $s, $ma)
     && preg_match('~[?&](?:mlon|lon|lng)=(-?\d+(?:\.\d+)?)~i', $s, $mb)) {
        return [(float)$ma[1], (float)$mb[1]];
    }

    // Genérico: qualquer "LAT, LNG" no texto
    if (preg_match('~(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)~', $s, $m)) {
        return [(float)$m[1], (float)$m[2]];
    }

    return [null, null];
}

    // Mixa lat/lng extraídos no Request para que a validação enxergue
    private function mergeCoordsFromUrl(Request $req): void
    {
        [$lat, $lng] = $this->extractCoordsFromUrl($req->input('maps_url'));
        if ($lat !== null && $lng !== null) {
            $req->merge(['lat' => $lat, 'lng' => $lng]);
        }
    }

    // Colunas de capa/perfil com fallback seguro (evita quebrar se helpers não existirem)
    private function colunaCapa(): ?string
    {
        if (method_exists($this, 'colunaCapaEmpresa')) return $this->colunaCapaEmpresa();
        return Schema::hasColumn('empresas','foto_capa_path') ? 'foto_capa_path' : null;
    }
    private function colunaPerfil(): ?string
    {
        if (method_exists($this, 'colunaPerfilEmpresa')) return $this->colunaPerfilEmpresa();
        return Schema::hasColumn('empresas','foto_perfil_path') ? 'foto_perfil_path' : null;
    }

    // -----------------------
    // CRUD
    // -----------------------

    public function store(Request $request)
    {
        // Extrai coordenadas (Google/Bing/OSM) se a URL tiver
        $this->mergeCoordsFromUrl($request);

        $data = $request->validate([
            'nome'        => ['required','string','max:255'],
            'slug'        => [
                'nullable','string','max:255',
                Rule::unique('empresas','slug')->where(fn($q)=>$q->whereNull('deleted_at')),
            ],
            'descricao'   => ['nullable','string'],
            'status'      => ['required','in:rascunho,publicado,arquivado'],
            'ordem'       => ['nullable','integer','min:0'],

            'maps_url'    => ['nullable','url','max:2048'],
            'lat'         => ['nullable','numeric','between:-90,90'],
            'lng'         => ['nullable','numeric','between:-180,180'],

            'endereco'    => ['nullable','string','max:255'],
            'bairro'      => ['nullable','string','max:255'],
            'cidade'      => ['nullable','string','max:255'],

            'categorias'  => ['nullable','array'],
            'categorias.*'=> ['integer','exists:categorias,id'],

            'contatos'           => ['nullable','array'],
            'contatos.whatsapp'  => ['nullable','string','max:30'],
            'contatos.instagram' => ['nullable','string','max:120'],
            'contatos.facebook'  => ['nullable','string','max:120'],
            'contatos.tiktok'    => ['nullable','string','max:120'],
            'contatos.youtube'   => ['nullable','string','max:200'],
            'contatos.site'      => ['nullable','string','max:200'],
            'contatos.maps'      => ['nullable','url','max:2048'],
            'contatos.email'     => ['nullable','email','max:190'],

            'galeria'       => ['nullable','array','max:12'],
            'galeria.*'     => ['image','mimes:jpg,jpeg,png,webp','max:5120'],

            // >>> AGORA OBRIGATÓRIOS NA CRIAÇÃO <<<
            'capa'        => ['required','image','max:5120'],
            'perfil'      => ['required','image','max:5120'],
        ], [
            'maps_url.url' => 'Cole um link válido de mapa (Google, Bing ou OpenStreetMap).',
            'lat.between'  => 'Latitude deve estar entre -90 e 90.',
            'lng.between'  => 'Longitude deve estar entre -180 e 180.',
            'capa.required'   => 'A foto de capa é obrigatória na criação.',
            'perfil.required' => 'A foto de perfil é obrigatória na criação.',
        ]);

        // garante chaves mesmo quando ausentes
        $data += ['lat' => null, 'lng' => null];

        // se for publicar, exige coords válidas
        if ($data['status'] === 'publicado' && ($data['lat'] === null || $data['lng'] === null)) {
            return back()->withErrors([
                'maps_url' => 'Para publicar, informe um link de mapa que contenha as coordenadas.',
            ])->withInput();
        }

        $empresa = null;

        DB::transaction(function () use ($request, $data, &$empresa) {
            $empresa = new Empresa();
            $empresa->nome      = $data['nome'];
            $empresa->slug      = $this->makeUniqueSlug($data['slug'] ?? \Illuminate\Support\Str::slug($data['nome']));
            $empresa->descricao = $data['descricao'] ?? null;
            $empresa->status    = $data['status'];
            $empresa->ordem     = $data['ordem'] ?? 0;

            // Localização
            $empresa->maps_url  = $data['maps_url'] ?? null;
            $empresa->lat       = $data['lat'];
            $empresa->lng       = $data['lng'];

            $empresa->endereco  = $data['endereco'] ?? null;
            $empresa->bairro    = $data['bairro'] ?? null;
            $empresa->cidade    = $data['cidade'] ?? null;

            $empresa->contatos = $this->normalizeContatos((array) $request->input('contatos', []));


            $empresa->save();

            if (!empty($data['categorias'])) {
                $empresa->categorias()->sync($data['categorias']);
            }

            // Uploads — AGORA garantidamente existem
            if ($col = $this->colunaCapa()) {
                $path = $request->file('capa')->store('empresas/capas','public');
                $empresa->{$col} = ltrim($path, '/');
                $empresa->save();
            }

            if ($col = $this->colunaPerfil()) {
                $path = $request->file('perfil')->store('empresas/perfil','public');
                $empresa->{$col} = ltrim($path, '/');
                $empresa->save();
            }

            $this->storeGaleria($request, $empresa);
        });

        return redirect()
            ->route('coordenador.empresas.edit', $empresa)
            ->with('ok', 'Empresa criada com sucesso.');
    }

    public function edit(Empresa $empresa)
    {
        $categorias    = Categoria::orderBy('ordem')->orderBy('nome')->get(['id','nome']);
        $selecionadas  = $empresa->categorias()->pluck('categorias.id')->all();
        $empresa->load('galeriaFotos');
        return view('coordenador.empresas.edit', compact('empresa','categorias','selecionadas'));
    }

    public function update(Request $request, Empresa $empresa)
    {
        // Extrai coordenadas (se a URL tiver)
        $this->mergeCoordsFromUrl($request);

        $data = $request->validate([
            'nome'        => ['required','string','max:255'],
            'slug'        => [
                'nullable','string','max:255',
                Rule::unique('empresas','slug')->ignore($empresa->id)->where(fn($q)=>$q->whereNull('deleted_at')),
            ],
            'descricao'   => ['nullable','string'],
            'status'      => ['required','in:rascunho,publicado,arquivado'],
            'ordem'       => ['nullable','integer','min:0'],

            'maps_url'    => ['nullable','url','max:2048'],
            'lat'         => ['nullable','numeric','between:-90,90'],
            'lng'         => ['nullable','numeric','between:-180,180'],

            'endereco'    => ['nullable','string','max:255'],
            'bairro'      => ['nullable','string','max:255'],
            'cidade'      => ['nullable','string','max:255'],

            'categorias'  => ['nullable','array'],
            'categorias.*'=> ['integer','exists:categorias,id'],

            'galeria'       => ['nullable','array','max:12'],
            'galeria.*'     => ['image','mimes:jpg,jpeg,png,webp','max:5120'],
            'remover_fotos'   => ['nullable','array'],
            'remover_fotos.*' => ['integer'],

            'capa'        => ['nullable','image','max:5120'],
            'perfil'      => ['nullable','image','max:5120'],
        ], [
            'maps_url.url' => 'Cole um link válido de mapa (Google, Bing ou OpenStreetMap).',
            'lat.between'  => 'Latitude deve estar entre -90 e 90.',
            'lng.between'  => 'Longitude deve estar entre -180 e 180.',
        ]);

        // garante chaves mesmo quando ausentes
        $data += ['lat' => null, 'lng' => null];

        if ($data['status'] === 'publicado' && ($data['lat'] === null || $data['lng'] === null)) {
            return back()->withErrors([
                'maps_url' => 'Para publicar, informe um link de mapa que contenha as coordenadas.',
            ])->withInput();
        }

        DB::transaction(function () use ($request, $data, $empresa) {
            $empresa->nome      = $data['nome'];
            $empresa->slug      = $this->makeUniqueSlug($data['slug'] ?? Str::slug($data['nome']), $empresa->id);
            $empresa->descricao = $data['descricao'] ?? null;
            $empresa->status    = $data['status'];
            $empresa->ordem     = $data['ordem'] ?? 0;

            $empresa->maps_url  = $data['maps_url'] ?? null;
            $empresa->lat       = $data['lat'];
            $empresa->lng       = $data['lng'];

            $empresa->endereco  = $data['endereco'] ?? null;
            $empresa->bairro    = $data['bairro'] ?? null;
            $empresa->cidade    = $data['cidade'] ?? null;

            $contatosNovos = $this->normalizeContatos((array) $request->input('contatos', []));
            $empresa->contatos = array_filter(array_replace($empresa->contatos ?? [], $contatosNovos), fn($v)=>filled($v));


            $empresa->save();

            $empresa->categorias()->sync($data['categorias'] ?? []);

            if ($request->hasFile('capa')) {
                if ($col = $this->colunaCapa()) {
                    if (!empty($empresa->{$col})) Storage::disk('public')->delete($empresa->{$col});
                    $path = $request->file('capa')->store('empresas/capas','public');
                    $empresa->{$col} = ltrim($path, '/');
                    $empresa->save();
                }
            }

            if ($request->hasFile('perfil')) {
                if ($col = $this->colunaPerfil()) {
                    if (!empty($empresa->{$col})) Storage::disk('public')->delete($empresa->{$col});
                    $path = $request->file('perfil')->store('empresas/perfil','public');
                    $empresa->{$col} = ltrim($path, '/');
                    $empresa->save();
                }
            }

            $this->removeSelectedFotos($request, $empresa);
            $this->storeGaleria($request, $empresa);
        });

        return redirect()
            ->route('coordenador.empresas.edit', $empresa)
            ->with('ok','Empresa atualizada.');
    }

    private function normalizeContatos(array $in): array
    {
        $take = fn($k) => trim((string)($in[$k] ?? ''));
        $nz   = fn($v) => $v !== '' ? $v : null;

        // WhatsApp somente dígitos; se vier sem DDI, prefixa 55 (BR)
        $whats = preg_replace('/\D+/', '', $take('whatsapp'));
        if ($whats && strlen($whats) <= 11) $whats = '55'.$whats;

        return array_filter([
            'whatsapp'  => $nz($whats),
            'instagram' => $nz($this->stripHandleOrKeepUrl($take('instagram'), 'instagram')),
            'facebook'  => $nz($this->stripHandleOrKeepUrl($take('facebook'), 'facebook')),
            'tiktok'    => $nz($this->stripHandleOrKeepUrl($take('tiktok'), 'tiktok')),
            'youtube'   => $nz($take('youtube')),  // geralmente já é URL/slug
            'site'      => $nz($this->ensureScheme($take('site'))),
            'maps'      => $nz($take('maps')),     // já validado como URL
            'email'     => $nz($take('email')),
        ], fn($v)=>filled($v));
    }

private function stripHandleOrKeepUrl(string $v, string $net): string
{
    $v = trim($v);
    if ($v === '') return '';
    if (str_starts_with($v, 'http')) return $v;     // já é URL completa
    $v = ltrim($v, '@/ ');
    // remove prefixos comuns colados
    $v = preg_replace('#^(instagram\.com/|www\.instagram\.com/|facebook\.com/|www\.facebook\.com/|www\.tiktok\.com/@)#i', '', $v);
    return $v;
}

    private function ensureScheme(string $v): string
    {
        $v = trim($v);
        if ($v === '') return '';
        return str_starts_with($v, 'http') ? $v : "https://{$v}";
    }

    private function storeGaleria(Request $request, Empresa $empresa): void
    {
        if (!$request->hasFile('galeria')) {
            return;
        }

        $ordemBase = ((int) $empresa->galeriaFotos()->max('ordem')) + 1;

        foreach ($request->file('galeria') as $i => $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $empresa->galeriaFotos()->create([
                'path' => ltrim($file->store('empresas/galeria', 'public'), '/'),
                'alt' => $empresa->nome,
                'ordem' => $ordemBase + $i,
            ]);
        }
    }

    private function removeSelectedFotos(Request $request, Empresa $empresa): void
    {
        $ids = collect($request->input('remover_fotos', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        if (empty($ids)) {
            return;
        }

        $fotos = $empresa->galeriaFotos()->whereIn('id', $ids)->get();

        foreach ($fotos as $foto) {
            if ($foto->path && Storage::disk('public')->exists($foto->path)) {
                Storage::disk('public')->delete($foto->path);
            }

            $foto->delete();
        }
    }


    public function destroy(Empresa $empresa)
    {
        $empresa->delete();
        return back()->with('ok','Empresa movida para a lixeira.');
    }

    public function removerCapa(Empresa $empresa)
    {
        if ($empresa->foto_capa_path && Storage::disk('public')->exists($empresa->foto_capa_path)) {
            Storage::disk('public')->delete($empresa->foto_capa_path);
        }
        $empresa->update(['foto_capa_path'=>null]);
        return back()->with('ok','Capa removida.');
    }

    public function removerPerfil(Empresa $empresa)
    {
        if ($empresa->foto_perfil_path && Storage::disk('public')->exists($empresa->foto_perfil_path)) {
            Storage::disk('public')->delete($empresa->foto_perfil_path);
        }
        $empresa->update(['foto_perfil_path'=>null]);
        return back()->with('ok','Foto de perfil removida.');
    }

    public function publicar(Request $request, Empresa $empresa)
    {
        // Se ainda não há coords no model, tenta extrair da URL enviada/agora
        if ($empresa->lat === null || $empresa->lng === null) {
            $this->mergeCoordsFromUrl($request);
            if ($request->filled('lat') && $request->filled('lng')) {
                $empresa->lat = (float)$request->input('lat');
                $empresa->lng = (float)$request->input('lng');
            }
        }

        // Valida intervalo final (agora obrigatório para publicar)
        $request->merge(['lat' => $empresa->lat, 'lng' => $empresa->lng]);
        $request->validate([
            'lat' => ['required','numeric','between:-90,90'],
            'lng' => ['required','numeric','between:-180,180'],
        ], [
            'lat.between' => 'Latitude deve estar entre -90 e 90.',
            'lng.between' => 'Longitude deve estar entre -180 e 180.',
        ]);

        $empresa->status = 'publicado';
        $empresa->published_at ??= now();
        $empresa->save();

        return back()->with('ok','Empresa publicada.');
    }

    public function arquivar(Empresa $empresa)
    {
        $empresa->status = 'arquivado';
        $empresa->save();

        return back()->with('ok','Empresa arquivada.');
    }

    public function rascunho(Empresa $empresa)
    {
        $empresa->status = 'rascunho';
        $empresa->save();

        return back()->with('ok','Empresa movida para rascunho.');
    }

    // -----------------------
    // RECOMENDAÇÕES (Home/Mapa)
    // -----------------------

    public function recomendar(Request $r, Empresa $empresa)
    {
        $data = $r->validate([
            'contexto'      => ['required', Rule::in(['global','categoria'])],
            'categoria_id'  => ['nullable','integer','exists:categorias,id'],
            'ordem'         => ['nullable','integer','min:0'],
            'inicio_em'     => ['nullable','date'],
            'fim_em'        => ['nullable','date','after_or_equal:inicio_em'],
            'ativo_forcado' => ['nullable','boolean'],
        ]);

        $categoriaId = $data['contexto'] === 'categoria'
            ? ($data['categoria_id'] ?? null)
            : null;

        $rec = EmpresaRecomendacao::withTrashed()->firstOrNew([
            'empresa_id'   => $empresa->id,
            'categoria_id' => $categoriaId,
        ]);

        $rec->fill([
            'ordem'         => $data['ordem'] ?? 0,
            'inicio_em'     => $data['inicio_em'] ?? null,
            'fim_em'        => $data['fim_em'] ?? null,
            'ativo_forcado' => (bool)($data['ativo_forcado'] ?? false),
            'created_by'    => auth()->id(),
        ]);

        if ($rec->trashed()) {
            $rec->restore();
        }

        $rec->save();

        return back()->with('ok','Empresa recomendada com sucesso.');
    }



    public function removerRecomendacao(Empresa $empresa, Request $r)
    {
        $data = $r->validate([
            'contexto'     => ['required', Rule::in(['global','categoria'])],
            'categoria_id' => ['nullable','integer','exists:categorias,id'],
        ]);

        $q = EmpresaRecomendacao::where('empresa_id', $empresa->id);

        if ($data['contexto'] === 'categoria') {
            if (empty($data['categoria_id'])) {
                return back()->withErrors(['categoria_id' => 'Selecione a categoria.']);
            }

            $q->where('categoria_id', $data['categoria_id']);
        } else {
            $q->whereNull('categoria_id');
        }

        $q->delete();

        return back()->with('ok','Recomendação removida.');
    }

    public function reordenarRecomendacao(Request $r, EmpresaRecomendacao $rec)
    {
        $r->validate(['ordem'=>['required','integer','min:0']]);
        $rec->update(['ordem'=>$r->ordem]);
        return back()->with('ok','Ordem atualizada.');
    }
}
