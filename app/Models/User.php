<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\PermissionRegistrar;

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

    public static function forbiddenForTecnicoPermissions(): array
    {
        return [
            'tecnicos.manage',
            'usuarios.manage',
            'console.cache.clear',
        ];
    }

    public function delegablePermissionNames(): array
    {
        return $this->getAllPermissions()
            ->pluck('name')
            ->reject(function ($permission) {
                return in_array($permission, self::forbiddenForTecnicoPermissions(), true)
                    || Str::startsWith($permission, ['usuarios.', 'console.cache.', 'tecnicos.']);
            })
            ->values()
            ->all();
    }

    public function syncCoordenadorDirectPermissions(array $permissions): void
    {
        $this->syncPermissions($permissions);

        if ($this->hasRole('Coordenador')) {
            $this->syncTecnicosDelegatedPermissions();
        }
    }

    public function syncTecnicosDelegatedPermissions(): void
    {
        if (! $this->hasRole('Coordenador')) {
            return;
        }

        $allowed = $this->delegablePermissionNames();

        foreach ($this->tecnicos()->get() as $tecnico) {
            $currentDirect = $tecnico->getDirectPermissions()->pluck('name')->all();
            $tecnico->syncPermissions(array_values(array_intersect($currentDirect, $allowed)));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
