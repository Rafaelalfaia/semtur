<?php

namespace App\Models\Catalogo;

use App\Models\Concerns\HasPublicado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Roteiro extends Model
{
    use SoftDeletes, HasPublicado;

    protected $table = 'roteiros';

    protected $fillable = [
        'titulo',
        'slug',
        'resumo',
        'descricao',
        'duracao_slug',
        'perfil_slug',
        'publico_label',
        'melhor_epoca',
        'deslocamento',
        'nivel_intensidade',
        'capa_path',
        'seo_title',
        'seo_description',
        'ordem',
        'status',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $appends = [
        'capa_url',
        'duracao_label',
        'perfil_label',
        'intensidade_label',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public const STATUS_RASCUNHO  = 'rascunho';
    public const STATUS_PUBLICADO = 'publicado';
    public const STATUS_ARQUIVADO = 'arquivado';

    public const STATUS = [
        self::STATUS_RASCUNHO,
        self::STATUS_PUBLICADO,
        self::STATUS_ARQUIVADO,
    ];

    public const DURACOES = [
        '1_dia'     => 'Altamira em 1 dia',
        '2_3_dias'  => 'Altamira em 2 ou 3 dias',
        'meio_dia'  => 'Meio dia',
        'personalizado' => 'Personalizado',
    ];

    public const PERFIS = [
        'geral'               => 'Geral',
        'natureza_rio'        => 'Natureza e rio',
        'cultura_memoria'     => 'Cultura e memória',
        'gastronomia_local'   => 'Gastronomia local',
        'base_comunitaria'    => 'Turismo de base comunitária',
        'familia_educacao'    => 'Família e educação',
    ];

    public const TIPOS_BLOCO = [
        'manha' => 'Manhã',
        'tarde' => 'Tarde',
        'noite' => 'Noite',
        'dia'   => 'Dia',
        'extra' => 'Extra',
    ];

    public const TIPOS_SUGESTAO = [
        'passeio'      => 'Passeio',
        'alimentacao'  => 'Alimentação',
        'hospedagem'   => 'Hospedagem',
        'apoio'        => 'Apoio local',
        'guia'         => 'Guia',
        'compra'       => 'Compra',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $base = trim((string) ($model->slug ?: $model->titulo));

            if ($base !== '') {
                $base = Str::slug($base);
                $model->slug = static::uniqueSlug($base, $model->exists ? (int) $model->getKey() : null);
            }

            if ($model->status === self::STATUS_PUBLICADO && empty($model->published_at)) {
                $model->published_at = now();
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

    public function etapas()
    {
        return $this->hasMany(RoteiroEtapa::class)->orderBy('ordem')->orderBy('id');
    }

    public function empresasSugestao()
    {
        return $this->hasMany(RoteiroEmpresa::class)->orderBy('ordem')->orderBy('id');
    }

    public function getCapaUrlAttribute(): ?string
    {
        return $this->capa_path ? Storage::url($this->capa_path) : null;
    }

    public function getDuracaoLabelAttribute(): string
    {
        return self::DURACOES[$this->duracao_slug] ?? $this->duracao_slug;
    }

    public function getPerfilLabelAttribute(): string
    {
        return self::PERFIS[$this->perfil_slug] ?? $this->perfil_slug;
    }

    public function getIntensidadeLabelAttribute(): ?string
    {
        return match ($this->nivel_intensidade) {
            'leve' => 'Leve',
            'moderado' => 'Moderado',
            'intenso' => 'Intenso',
            default => null,
        };
    }
}
