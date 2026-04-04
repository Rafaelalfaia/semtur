<?php

namespace App\Models\Conteudo;

use App\Models\Concerns\HasPublicado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Curso extends Model
{
    use SoftDeletes, HasPublicado;

    protected $table = 'cursos';

    protected $fillable = [
        'nome',
        'slug',
        'capa_path',
        'descricao_curta',
        'publico_alvo',
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
        'publico_alvo_label',
    ];

    public const STATUS_RASCUNHO = 'rascunho';
    public const STATUS_PUBLICADO = 'publicado';
    public const STATUS_ARQUIVADO = 'arquivado';

    public const STATUS = [
        self::STATUS_RASCUNHO,
        self::STATUS_PUBLICADO,
        self::STATUS_ARQUIVADO,
    ];

    public const PUBLICO_COORDENADOR = 'coordenador';
    public const PUBLICO_TECNICO = 'tecnico';
    public const PUBLICO_AMBOS = 'ambos';

    public const PUBLICOS_ALVO = [
        self::PUBLICO_COORDENADOR,
        self::PUBLICO_TECNICO,
        self::PUBLICO_AMBOS,
    ];

    public const PUBLICOS_LABELS = [
        self::PUBLICO_COORDENADOR => 'Coordenador',
        self::PUBLICO_TECNICO => 'Tecnico',
        self::PUBLICO_AMBOS => 'Ambos',
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

    public function modulos(): HasMany
    {
        return $this->hasMany(CursoModulo::class, 'curso_id')
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

    public function getPublicoAlvoLabelAttribute(): string
    {
        return self::PUBLICOS_LABELS[$this->publico_alvo] ?? ucfirst((string) $this->publico_alvo);
    }
}
