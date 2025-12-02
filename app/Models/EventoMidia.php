<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventoMidia extends Model
{
    protected $table = 'evento_midias';
    protected $fillable = ['edicao_id','path','alt','ordem','tipo'];

    public function edicao(){ return $this->belongsTo(EventoEdicao::class,'edicao_id'); }
}
