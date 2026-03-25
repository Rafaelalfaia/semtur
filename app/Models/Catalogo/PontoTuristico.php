<?php

namespace App\Models\Catalogo;

use App\Models\Concerns\HasPublicado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PontoTuristico extends Model
{
    use SoftDeletes, HasPublicado;

    protected $table = 'pontos_turisticos';
    protected $appends = ['capa_url'];

    // app/Models/Catalogo/PontoTuristico.php

    protected $fillable = [
        'nome',
        'slug',
        'descricao',
        'maps_url',
        'endereco',
        'bairro',
        'cidade',
        'lat',
        'lng',
        'ordem',
        'status',
        'capa_path',
        'published_at',
        // se existirem no schema:
        'video_url',
    ];



    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'published_at' => 'datetime',
    ];

    // Canônicos
    public const RASCUNHO  = 'rascunho';
    public const PUBLICADO = 'publicado';
    public const ARQUIVADO = 'arquivado';

    // Aliases (compat)
    public const STATUS_RASCUNHO  = self::RASCUNHO;
    public const STATUS_PUBLICADO = self::PUBLICADO;
    public const STATUS_ARQUIVADO = self::ARQUIVADO;

    // Lista útil
    public const STATUS = [ self::RASCUNHO, self::PUBLICADO, self::ARQUIVADO ];

    protected $attributes = [
        'status' => self::RASCUNHO,
        'ordem'  => 0,
    ];

    // ---------- Slug único + published_at ----------
    protected static function booted(): void
    {
        static::saving(function (PontoTuristico $m) {
            // slug base: slug informado ou derivado do nome
            $base = trim((string) ($m->slug ?: $m->nome));
            if ($base !== '') {
                $base = Str::slug($base);
                $m->slug = static::uniqueSlug($base, $m->exists ? (int)$m->getKey() : null);
            }

            // se publicar agora e ainda não tem published_at
            if ($m->status === self::PUBLICADO && empty($m->published_at)) {
                $m->published_at = now();
            }
        });
    }

    /**
     * Garante slug único (considera soft-deletados).
     */
    public static function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $i = 2;

        $exists = function (string $s) use ($ignoreId): bool {
            $q = static::withTrashed()->where('slug', $s);
            if ($ignoreId) $q->where('id', '<>', $ignoreId);
            return $q->exists();
        };

        while ($exists($slug)) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
    }

    // ---------- Relacionamentos ----------
    public function categorias()
    {
        return $this->belongsToMany(
            Categoria::class,
            'categoria_ponto_turistico',
            'ponto_turistico_id',
            'categoria_id'
        );
    }

    public function midias()
    {
        $table = (new \App\Models\Catalogo\PontoMidia())->getTable();

        $foreignKey = \Illuminate\Support\Facades\Schema::hasColumn($table, 'ponto_turistico_id')
            ? 'ponto_turistico_id'
            : 'ponto_id';

        return $this->hasMany(\App\Models\Catalogo\PontoMidia::class, $foreignKey, 'id')
            ->orderBy('ordem')
            ->orderBy('id');
    }


    public function empresas()
    {
        return $this->belongsToMany(
            Empresa::class,
            'empresa_ponto_turistico',
            'ponto_turistico_id',
            'empresa_id'
        );
    }

    public function recomendacoes()
    {
        return $this->hasMany(PontoRecomendacao::class, 'ponto_turistico_id');
    }

    public function scopeComRecomendacaoGlobalAtiva($q)
    {
        return $q->whereHas('recomendacoes', function($r){
            $r->whereNull('categoria_id')->ativas();
        });
    }

    public function scopeComRecomendacaoCategoriaAtiva($q, int $categoriaId)
    {
        return $q->whereHas('recomendacoes', function($r) use ($categoriaId){
            $r->where('categoria_id', $categoriaId)->ativas();
        });
    }

    // ---------- Helpers/Accessors ----------
    public function capaUrl(): ?string
    {
        if ($this->capa_path) {
            return Storage::disk('public')->url($this->capa_path);
        }
        $midia = $this->relationLoaded('midias')
            ? $this->midias->sortBy('ordem')->first()
            : $this->midias()->orderBy('ordem')->first();

        return $midia?->url();
    }


    public function getCapaUrlAttribute(): string
    {
        // 1) capa direta (qualquer nome comum)
        $caminho = $this->firstFilledAttr(['capa_path','foto_capa_path','capa']);
        if ($caminho) return Storage::url($caminho);

        // 2) primeira mídia (evita N+1 se já vier carregado)
        $m = $this->relationLoaded('midias')
            ? $this->midias->first()
            : $this->midias()->select('id','path')->first();

        if ($m && !empty($m->path)) {
            return Storage::url(ltrim($m->path,'/'));
        }

        // 3) placeholder
        return asset('images/placeholder-ponto.png');
    }

    protected function firstFilledAttr(array $candidatos): ?string
    {
        foreach ($candidatos as $key) {
            $val = $this->getAttribute($key);
            if (is_string($val) && $val !== '') {
                return ltrim($val, '/');
            }
        }
        return null;
    }

    public function getPublicadoAttribute(): bool
    {
        return $this->status === self::PUBLICADO;
    }

    // ---------- Scopes úteis ----------
    public function scopePublicados($q)
    {
        return $q->where('status', self::PUBLICADO);
    }

    public function scopeRascunho($q)
    {
        return $q->where('status', self::RASCUNHO);
    }

    public function scopeArquivados($q)
    {
        return $q->where('status', self::ARQUIVADO);
    }

    public function getFotoCapaUrlAttribute(){ return $this->foto_capa_path ? \Storage::url($this->foto_capa_path) : null; }


}
