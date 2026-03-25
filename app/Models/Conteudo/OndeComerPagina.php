<?php

namespace App\Models\Conteudo;

use App\Models\Concerns\HasPublicado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OndeComerPagina extends Model
{
    use HasPublicado;

    protected $table = 'onde_comer_paginas';

    protected $fillable = [
        'titulo',
        'subtitulo',
        'resumo',
        'texto_intro',
        'texto_gastronomia_local',
        'texto_dicas',
        'hero_path',
        'seo_title',
        'seo_description',
        'status',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $appends = [
        'hero_url',
    ];

    public const STATUS_RASCUNHO  = 'rascunho';
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

    public static function singleton(): self
    {
        return static::query()->with([
            'empresasSelecionadas' => fn ($q) => $q->orderBy('ordem')->orderBy('id'),
            'empresasSelecionadas.empresa',
        ])->first() ?? new static([
            'titulo' => 'Onde comer em Altamira',
            'subtitulo' => 'Sabores locais',
            'status' => self::STATUS_RASCUNHO,
        ]);
    }

    public function empresasSelecionadas()
    {
        return $this->hasMany(OndeComerPaginaEmpresa::class)
            ->orderBy('ordem')
            ->orderBy('id');
    }

    public function getHeroUrlAttribute(): ?string
    {
        return $this->hero_path
            ? Storage::disk('public')->url($this->hero_path)
            : null;
    }
}
