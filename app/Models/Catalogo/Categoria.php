<?php

namespace App\Models\Catalogo;

use App\Models\Concerns\HasPublicado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;



class Categoria extends Model
{
    use SoftDeletes, HasPublicado;

    protected $fillable = [
    'nome','slug','descricao','icone_path','ordem','status','published_at','created_by',
    ];
    protected $appends = ['icone_url'];
    protected $casts = [
        'published_at' => 'datetime',
    ];

    // ===== Status padronizado (usar nos controllers) =====
    public const STATUS_RASCUNHO  = 'rascunho';
    public const STATUS_PUBLICADO = 'publicado';
    public const STATUS_ARQUIVADO = 'arquivado';

    // Lista útil (ex.: validação/opções de select)
    public const STATUS = [
        self::STATUS_RASCUNHO,
        self::STATUS_PUBLICADO,
        self::STATUS_ARQUIVADO,
    ];

    // Defaults de criação
    protected $attributes = [
        'status' => self::STATUS_RASCUNHO,
        'ordem'  => 0,
    ];


    // Rel. inversas úteis
    public function empresas()
    {
        return $this->belongsToMany(Empresa::class, 'categoria_empresa');
    }

    public function scopeBusca(Builder $q, ?string $busca): Builder
    {
        $busca = trim((string) $busca);
        if ($busca === '') return $q;

        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        return $q->where(function ($w) use ($busca, $like) {
            $w->where('nome', $like, "%{$busca}%")
            ->orWhere('descricao', $like, "%{$busca}%");
        });
    }

    public function pontos()
    {
        return $this->belongsToMany(PontoTuristico::class, 'categoria_ponto_turistico', 'categoria_id', 'ponto_turistico_id');
    }

    public function scopeStatus(Builder $q, ?string $status): Builder
    {
        $status = $status ? trim($status) : null;
        if (!$status || $status === 'todos') return $q;
        return $q->where('status', $status);
    }

    public function scopeOrdenado(Builder $q): Builder
    {
        return $q->orderBy('ordem')->orderBy('nome');
    }

    public function getIconeUrlAttribute(): string
    {
        $caminho = $this->getAttribute('icone_path') ?: $this->getAttribute('icone');
        return $caminho ? Storage::url(ltrim($caminho,'/')) : asset('images/placeholder-categoria.png');
    }
}
