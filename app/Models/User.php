<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    // Spatie guard
    protected $guard_name = 'web';

    /**
     * Campos preenchíveis (IMPORTANTE: incluir coordenador_id e cpf)
     */
    protected $fillable = [
        'name',
        'email',
        'cpf',
        'password',
        'google_id',
        'avatar_url',
        'coordenador_id',
    ];

    /**
     * Atributos ocultos
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed', // ok manter mesmo usando Hash::make no controller
        ];
    }

    // -----------------
    // Relações
    // -----------------
    public function coordenador()
    {
        return $this->belongsTo(User::class, 'coordenador_id');
    }

    public function tecnicos()
    {
        return $this->hasMany(User::class, 'coordenador_id')
            ->whereHas('roles', fn($q) => $q->where('name', 'Tecnico'));
    }

    // -----------------
    // Escopos
    // -----------------
    public function scopeOnlyMyTecnicos($q)
    {
        return $q->where('coordenador_id', auth()->id())
                 ->role('Tecnico');
    }
}
