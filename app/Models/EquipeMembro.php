<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EquipeMembro extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome','slug','cargo','resumo',
        'foto_path','redes','ordem','status','created_by'
    ];

    protected $casts = ['redes'=>'array'];

    public function getFotoUrlAttribute(): ?string {
        return $this->foto_path ? Storage::url($this->foto_path) : null;
    }

    public function scopePublicados($q){ return $q->where('status','publicado'); }
    public function scopeOrdenados($q){ return $q->orderBy('ordem')->orderBy('id','desc'); }

    protected static function booted(): void
    {
        static::saving(function(self $m){
            if (blank($m->slug)) $m->slug = Str::slug($m->nome);
        });
    }
}
