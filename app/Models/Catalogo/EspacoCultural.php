<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EspacoCultural extends Model
{
    use SoftDeletes;

    protected $table = 'espacos_culturais';

    public const TIPO_MUSEU  = 'museu';
    public const TIPO_TEATRO = 'teatro';

    public const TIPOS = [
        self::TIPO_MUSEU,
        self::TIPO_TEATRO,
    ];

    public const STATUS_RASCUNHO  = 'rascunho';
    public const STATUS_PUBLICADO = 'publicado';
    public const STATUS_ARQUIVADO = 'arquivado';

    public const STATUSES = [
        self::STATUS_RASCUNHO,
        self::STATUS_PUBLICADO,
        self::STATUS_ARQUIVADO,
    ];

    protected $fillable = [
        'tipo',
        'nome',
        'slug',
        'resumo',
        'descricao',
        'capa_path',
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
        'agendamento_ativo',
        'agendamento_contato_nome',
        'agendamento_contato_label',
        'agendamento_whatsapp_phone',
        'agendamento_instrucoes',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'published_at' => 'datetime',
        'agendamento_ativo' => 'boolean',
    ];

    protected $attributes = [
        'tipo' => self::TIPO_MUSEU,
        'cidade' => 'Altamira',
        'ordem' => 0,
        'status' => self::STATUS_RASCUNHO,
        'agendamento_ativo' => false,
    ];

    protected $appends = [
        'tipo_label',
        'capa_url',
        'agendamento_disponivel',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $base = Str::slug($model->slug ?: $model->nome ?: 'espaco-cultural');
            $model->slug = static::uniqueSlug($base, $model->exists ? (int) $model->id : null);

            $model->agendamento_whatsapp_phone = static::onlyDigits($model->agendamento_whatsapp_phone);

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

    private static function onlyDigits(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits !== '' ? $digits : null;
    }

    public function scopePublicados($query)
    {
        return $query->where('status', self::STATUS_PUBLICADO);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }

    public function horarios()
    {
        return $this->hasMany(EspacoCulturalHorario::class, 'espaco_cultural_id')
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->orderBy('ordem');
    }

    public function agendamentos()
    {
        return $this->hasMany(EspacoCulturalAgendamento::class, 'espaco_cultural_id')
            ->latest('id');
    }

    public function midias()
    {
        return $this->hasMany(EspacoCulturalMidia::class, 'espaco_cultural_id')
            ->orderBy('ordem')
            ->orderBy('id');
    }

    public function getTipoLabelAttribute(): string
    {
        return $this->tipo === self::TIPO_TEATRO ? 'Teatro' : 'Museu';
    }

    public function getCapaUrlAttribute(): ?string
    {
        return $this->capa_path
            ? Storage::disk('public')->url($this->capa_path)
            : null;
    }

    public function getAgendamentoDisponivelAttribute(): bool
    {
        return $this->status === self::STATUS_PUBLICADO
            && (bool) $this->agendamento_ativo
            && filled($this->agendamento_whatsapp_phone);
    }

    public function getAgendamentoContatoLabelAttribute($value): string
    {
        return $value ?: 'Agendamentos';
    }
}
