<?php

namespace App\Models\Conteudo;

use App\Models\Concerns\HasPublicado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CursoModulo extends Model
{
    use SoftDeletes, HasPublicado;

    protected $table = 'curso_modulos';

    protected $fillable = [
        'curso_id',
        'nome',
        'slug',
        'capa_path',
        'descricao_curta',
        'ordem',
        'status',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
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
            $base = trim((string) ($model->slug ?: $model->nome));

            if ($base !== '') {
                $base = Str::slug($base);
                $model->slug = static::uniqueSlug(
                    $base,
                    $model->exists ? (int) $model->getKey() : null
                );
            }

            if ($model->status === self::STATUS_PUBLICADO && empty($model->published_at)) {
                $model->published_at = now();
            }

            if ($model->status !== self::STATUS_PUBLICADO) {
                $model->published_at = null;
            }
        });
    }

    public static function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $i = 2;

        $exists = function (string $candidate) use ($ignoreId): bool {
            $query = static::withTrashed()->where('slug', $candidate);

            if ($ignoreId) {
                $query->where('id', '<>', $ignoreId);
            }

            return $query->exists();
        };

        while ($exists($slug)) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function aulas(): HasMany
    {
        return $this->hasMany(CursoAula::class, 'modulo_id')
            ->orderBy('ordem')
            ->orderByDesc('published_at')
            ->orderBy('nome');
    }

    public function getCapaUrlAttribute(): ?string
    {
        return $this->capa_path
            ? Storage::disk('public')->url($this->capa_path)
            : null;
    }
}
