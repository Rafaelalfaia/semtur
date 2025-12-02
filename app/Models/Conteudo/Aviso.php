<?php

namespace App\Models\Conteudo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Aviso extends Model
{
    protected $table = 'avisos';
    protected $fillable = ['titulo','descricao','whatsapp','imagem_path','status','inicio_em','fim_em'];

    protected $casts = [
        'inicio_em' => 'datetime',
        'fim_em'    => 'datetime',
    ];

    /* Scopes */
    public function scopePublicados($q){ return $q->where('status','publicado'); }
    public function scopeJanelaAtiva($q){
        $now = now();
        return $q->where(function($w) use($now){
            $w->whereNull('inicio_em')->orWhere('inicio_em','<=',$now);
        })->where(function($w) use($now){
            $w->whereNull('fim_em')->orWhere('fim_em','>=',$now);
        });
    }

    /* Helpers */
    public function getImagemUrlAttribute(): ?string {
        return $this->imagem_path ? Storage::url($this->imagem_path) : null;
    }
    public function getWhatsappLinkAttribute(): ?string {
        if(!$this->whatsapp) return null;
        $num  = preg_replace('/\D+/','',$this->whatsapp);
        $text = rawurlencode($this->titulo.' — Olá! Gostaria de mais informações.');
        return "https://wa.me/{$num}?text={$text}";
    }

    /* Reexibir quando houver alteração */
    public function getDismissKeyAttribute(): string {
        return 'aviso:'.$this->id.':'.md5(optional($this->updated_at)->toIso8601String() ?? '');
    }
}
