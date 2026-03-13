<?php

namespace App\Models\Catalogo;

use App\Models\Concerns\HasPublicado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EspacoCultural extends Model
{
    use SoftDeletes, HasPublicado;

    protected $table = 'espacos_culturais';

    protected $fillable = [
        'tipo',
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
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'published_at' => 'datetime',
    ];

    public const STATUS_RASCUNHO  = 'rascunho';
    public const STATUS_PUBLICADO = 'publicado';
    public const STATUS_ARQUIVADO = 'arquivado';

    public const TIPOS = ['museu', 'teatro'];

    protected $attributes = [
        'tipo'   => 'museu',
        'cidade' => 'Altamira',
        'status' => self::STATUS_RASCUNHO,
        'ordem'  => 0,
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $base = Str::slug($model->slug ?: $model->nome ?: 'espaco-cultural');
            $model->slug = static::uniqueSlug($base, $model->exists ? (int) $model->getKey() : null);

            if ($model->status === self::STATUS_PUBLICADO && empty($model->published_at)) {
                $model->published_at = now();
            }
        });
    }

    public static function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $i = 2;

        while (
            static::withTrashed()
                ->when($ignoreId, fn($q) => $q->where('id', '<>', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function horarios()
    {
        return $this->hasMany(EspacoCulturalHorario::class, 'espaco_cultural_id')
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio');
    }

    public function getTipoLabelAttribute(): string
    {
        return $this->tipo === 'teatro' ? 'Teatro' : 'Museu';
    }

    public function scopeMuseus($q)
    {
        return $q->where('tipo', 'museu');
    }

    public function scopeTeatros($q)
    {
        return $q->where('tipo', 'teatro');
    }
}
