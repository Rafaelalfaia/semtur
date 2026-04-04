<?php

namespace App\Models\Conteudo;

use App\Models\Concerns\HasPublicado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CursoAula extends Model
{
    use SoftDeletes, HasPublicado;

    protected $table = 'curso_aulas';

    protected $fillable = [
        'modulo_id',
        'nome',
        'slug',
        'capa_path',
        'descricao',
        'link_acesso',
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
        'embed_url',
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

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(CursoModulo::class, 'modulo_id');
    }

    public function getCapaUrlAttribute(): ?string
    {
        return $this->capa_path
            ? Storage::disk('public')->url($this->capa_path)
            : null;
    }

    public function getEmbedUrlAttribute(): ?string
    {
        return static::buildEmbedUrl($this->link_acesso);
    }

    public static function buildEmbedUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        if (preg_match('~(?:drive|docs)\.google\.com/file/d/([a-zA-Z0-9_-]+)~', $url, $m)) {
            return "https://drive.google.com/file/d/{$m[1]}/preview";
        }

        $query = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        if (!empty($query['id'])) {
            $id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $query['id']);

            if ($id !== '') {
                return "https://drive.google.com/file/d/{$id}/preview";
            }
        }

        return $url;
    }
}
