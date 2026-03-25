<?php

namespace App\Models\Catalogo;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class EspacoCulturalAgendamento extends Model
{
    protected $table = 'espaco_cultural_agendamentos';

    public const STATUS_PENDENTE   = 'pendente';
    public const STATUS_EM_ANALISE = 'em_analise';
    public const STATUS_CONFIRMADO = 'confirmado';
    public const STATUS_CANCELADO  = 'cancelado';
    public const STATUS_CONCLUIDO  = 'concluido';
    public const STATUS_EXPIRADO   = 'expirado';

    public const STATUSES = [
        self::STATUS_PENDENTE,
        self::STATUS_EM_ANALISE,
        self::STATUS_CONFIRMADO,
        self::STATUS_CANCELADO,
        self::STATUS_CONCLUIDO,
        self::STATUS_EXPIRADO,
    ];

    public const STATUSES_QUE_CONSOMEM_VAGA = [
        self::STATUS_PENDENTE,
        self::STATUS_EM_ANALISE,
        self::STATUS_CONFIRMADO,
    ];

    protected $fillable = [
        'espaco_cultural_id',
        'espaco_cultural_horario_id',
        'data_visita',
        'protocolo',
        'nome',
        'telefone',
        'email',
        'qtd_visitantes',
        'observacao_visitante',
        'observacao_interna',
        'status',
        'whatsapp_phone',
        'whatsapp_message',
        'whatsapp_clicked_at',
        'expirado_em',
        'confirmado_em',
        'cancelado_em',
        'concluido_em',
        'tecnico_id',
        'atribuido_por',
    ];

    protected $casts = [
        'data_visita' => 'date',
        'qtd_visitantes' => 'integer',
        'whatsapp_clicked_at' => 'datetime',
        'expirado_em' => 'datetime',
        'confirmado_em' => 'datetime',
        'cancelado_em' => 'datetime',
        'concluido_em' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDENTE,
        'qtd_visitantes' => 1,
    ];

    protected $appends = [
        'whatsapp_link',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $model->telefone = static::onlyDigits($model->telefone);
            $model->whatsapp_phone = static::onlyDigits($model->whatsapp_phone);
        });
    }

    private static function onlyDigits(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits !== '' ? $digits : null;
    }

    public function scopeConsumindoVaga($query)
    {
        return $query->whereIn('status', self::STATUSES_QUE_CONSOMEM_VAGA);
    }

    public function espaco()
    {
        return $this->belongsTo(EspacoCultural::class, 'espaco_cultural_id');
    }

    public function horario()
    {
        return $this->belongsTo(EspacoCulturalHorario::class, 'espaco_cultural_horario_id');
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function atribuidor()
    {
        return $this->belongsTo(User::class, 'atribuido_por');
    }

    public function getWhatsappLinkAttribute(): ?string
    {
        if (!$this->whatsapp_phone || !$this->whatsapp_message) {
            return null;
        }

        return 'https://wa.me/' . $this->whatsapp_phone . '?text=' . rawurlencode($this->whatsapp_message);
    }
}
