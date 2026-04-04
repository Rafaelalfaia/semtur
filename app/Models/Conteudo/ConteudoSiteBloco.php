<?php

namespace App\Models\Conteudo;

use App\Models\Concerns\HasPublicado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConteudoSiteBloco extends Model
{
    use HasPublicado;

    protected $table = 'conteudo_site_blocos';

    protected $fillable = [
        'parent_id',
        'pagina',
        'chave',
        'rotulo',
        'tipo',
        'regiao',
        'ordem',
        'configuracao',
        'status',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'configuracao' => 'array',
        'published_at' => 'datetime',
    ];

    public const STATUS_RASCUNHO = 'rascunho';
    public const STATUS_PUBLICADO = 'publicado';
    public const STATUS_ARQUIVADO = 'arquivado';

    public const STATUS = [
        self::STATUS_RASCUNHO,
        self::STATUS_PUBLICADO,
        self::STATUS_ARQUIVADO,
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if ($model->status === self::STATUS_PUBLICADO && empty($model->published_at)) {
                $model->published_at = now();
            }

            if ($model->status !== self::STATUS_PUBLICADO) {
                $model->published_at = null;
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('ordem')
            ->orderBy('id');
    }

    public function traducoes(): HasMany
    {
        return $this->hasMany(ConteudoSiteBlocoTraducao::class, 'conteudo_site_bloco_id');
    }

    public function midias(): HasMany
    {
        return $this->hasMany(ConteudoSiteMidia::class, 'conteudo_site_bloco_id')
            ->orderBy('ordem')
            ->orderBy('id');
    }

    public function scopeDaPagina($query, string $pagina)
    {
        return $query->where('pagina', $pagina);
    }

    public function scopeDaChave($query, string $chave)
    {
        return $query->where('chave', $chave);
    }
}
