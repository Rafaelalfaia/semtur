<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class PontoMidia extends Model
{
    use SoftDeletes;

    protected $table = 'ponto_midias';

    /**
     * CAMPOS:
     * - tipo: 'image' | 'video_file' | 'video_link'
     * - path: caminho no disco 'public' (para image/video_file)
     * - url : link externo (somente quando tipo = 'video_link')
     */
   protected $fillable = [
        'ponto_id',            // se existir
        'ponto_turistico_id',  // se existir
        'path',
        'ordem',
    ];

    protected $attributes = [
        'ordem' => 0,
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * RELAÇÕES
     */
    public function ponto()
    {
        // relação inversa opcional; FK será definida do lado do PontoTuristico
        return $this->belongsTo(PontoTuristico::class);
    }



    /**
     * ACCESSORS
     * - url        => URL "resolvida" (arquivo do storage OU link externo), para usar como $midia->url
     * - path_url   => URL pública apenas do arquivo salvo em 'path' (útil quando você quer ignorar o video_link)
     */
    public function getUrlAttribute(): ?string
    {
        // Para image/video_file: gera a URL pública do arquivo salvo em storage/public
        if (in_array($this->tipo, ['image', 'video_file'], true)) {
            return $this->path ? Storage::disk('public')->url($this->path) : null;
        }

        // Para video_link: retorna o link salvo no banco (coluna 'url')
        if ($this->tipo === 'video_link') {
            // Usa o valor bruto da coluna para evitar recursão
            return $this->attributes['url'] ?? null;
        }

        return null;
    }

    public function getPathUrlAttribute(): ?string
    {
        return $this->path ? Storage::disk('public')->url($this->path) : null;
    }

    /**
     * LEGACY: manter compatibilidade com $midia->url() usado em trechos antigos.
     * Agora ele apenas delega para o accessor ($this->url).
     */
    public function url(): ?string
    {
        return $this->url; // chama o accessor getUrlAttribute()
    }
}
