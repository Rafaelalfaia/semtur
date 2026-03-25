<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JogosIndigenas extends Model
{
    use SoftDeletes;

    protected $table = 'jogos_indigenas';

    protected $fillable = [
        'titulo',
        'slug',
        'descricao',
        'foto_perfil_path',
        'foto_capa_path',
        'ordem',
        'status',
        'published_at',
    ];

    protected $casts = [
        'ordem' => 'integer',
        'published_at' => 'datetime',
    ];

    protected $attributes = [
        'ordem' => 0,
        'status' => self::STATUS_RASCUNHO,
    ];

    protected $appends = [
        'foto_perfil_url',
        'foto_capa_url',
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
            $base = trim((string) ($model->slug ?: $model->titulo ?: 'jogos-indigenas'));

            if ($base !== '') {
                $model->slug = static::uniqueSlug(Str::slug($base), $model->exists ? (int) $model->getKey() : null);
            }

            if ($model->status === self::STATUS_PUBLICADO) {
                $model->published_at ??= now();
            } else {
                $model->published_at = null;
            }
        });
    }

    public static function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $i = 2;

        while (
            static::withTrashed()
                ->when($ignoreId, fn ($q) => $q->where('id', '<>', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function edicoes()
    {
        return $this->hasMany(JogosIndigenasEdicao::class, 'jogos_indigenas_id')
            ->orderByDesc('ano')
            ->orderBy('ordem')
            ->orderBy('id');
    }

    public function scopePublicados($query)
    {
        return $query->where('status', self::STATUS_PUBLICADO);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('ordem')->orderBy('titulo');
    }

    public function getFotoPerfilUrlAttribute(): ?string
    {
        return $this->foto_perfil_path
            ? Storage::disk('public')->url($this->foto_perfil_path)
            : null;
    }

    public function getFotoCapaUrlAttribute(): ?string
    {
        return $this->foto_capa_path
            ? Storage::disk('public')->url($this->foto_capa_path)
            : null;
    }
}
