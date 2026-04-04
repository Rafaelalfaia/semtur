<?php

namespace App\Models\Conteudo;

use App\Models\Idioma;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConteudoSiteBlocoTraducao extends Model
{
    protected $table = 'conteudo_site_bloco_traducoes';

    protected $fillable = [
        'conteudo_site_bloco_id',
        'idioma_id',
        'eyebrow',
        'titulo',
        'subtitulo',
        'lead',
        'conteudo',
        'cta_label',
        'cta_href',
        'seo_title',
        'seo_description',
        'extras',
        'is_auto_translated',
        'auto_translated_at',
        'reviewed_at',
        'source_locale',
        'source_hash',
    ];

    protected $casts = [
        'extras' => 'array',
        'is_auto_translated' => 'boolean',
        'auto_translated_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected $appends = [
        'translation_state',
        'is_reviewed',
    ];

    public function bloco(): BelongsTo
    {
        return $this->belongsTo(ConteudoSiteBloco::class, 'conteudo_site_bloco_id');
    }

    public function idioma(): BelongsTo
    {
        return $this->belongsTo(Idioma::class, 'idioma_id');
    }

    public function getTranslationStateAttribute(): string
    {
        if ($this->reviewed_at) {
            return 'revisado';
        }

        if ($this->is_auto_translated) {
            return 'traduzido_auto';
        }

        return 'original';
    }

    public function getIsReviewedAttribute(): bool
    {
        return $this->reviewed_at !== null;
    }
}
