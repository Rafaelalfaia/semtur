<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JogosIndigenasEdicao extends Model
{
    use SoftDeletes;

    protected $table = 'jogos_indigenas_edicoes';

    protected $fillable = [
        'jogos_indigenas_id',
        'ano',
        'titulo',
        'slug',
        'descricao',
        'capa_path',
        'ordem',
        'status',
        'published_at',
    ];

    protected $casts = [
        'ano' => 'integer',
        'ordem' => 'integer',
        'published_at' => 'datetime',
    ];

    protected $attributes = [
        'ordem' => 0,
        'status' => self::STATUS_RASCUNHO,
    ];

    protected $appends = [
        'capa_url',
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
            $base = trim((string) ($model->slug ?: $model->titulo ?: $model->ano ?: 'edicao'));

            if ($base !== '') {
                $model->slug = static::uniqueSlug(
                    Str::slug($base),
                    (int) $model->jogos_indigenas_id,
                    $model->exists ? (int) $model->getKey() : null
                );
            }

            if ($model->status === self::STATUS_PUBLICADO) {
                $model->published_at ??= now();
            } else {
                $model->published_at = null;
            }
        });
    }

    public static function uniqueSlug(string $base, int $jogosIndigenasId, ?int $ignoreId = null): string
    {
        $slug = $base;
        $i = 2;

        while (
            static::withTrashed()
                ->where('jogos_indigenas_id', $jogosIndigenasId)
                ->when($ignoreId, fn ($q) => $q->where('id', '<>', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function jogosIndigenas()
    {
        return $this->belongsTo(JogosIndigenas::class, 'jogos_indigenas_id');
    }

    public function fotos()
    {
        return $this->hasMany(JogosIndigenasEdicaoFoto::class, 'jogos_indigenas_edicao_id')
            ->orderBy('ordem')
            ->orderBy('id');
    }

    public function videos()
    {
        return $this->hasMany(JogosIndigenasEdicaoVideo::class, 'jogos_indigenas_edicao_id')
            ->orderBy('ordem')
            ->orderBy('id');
    }

    public function patrocinadores()
    {
        return $this->hasMany(JogosIndigenasEdicaoPatrocinador::class, 'jogos_indigenas_edicao_id')
            ->orderBy('ordem')
            ->orderBy('id');
    }

    public function scopePublicados($query)
    {
        return $query->where('status', self::STATUS_PUBLICADO);
    }

    public function scopeOrdenadas($query)
    {
        return $query->orderByDesc('ano')->orderBy('ordem')->orderBy('titulo');
    }

    public function getCapaUrlAttribute(): ?string
    {
        return $this->capa_path
            ? Storage::disk('public')->url($this->capa_path)
            : null;
    }
}
