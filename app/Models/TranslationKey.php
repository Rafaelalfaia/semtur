<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TranslationKey extends Model
{
    protected $table = 'translation_keys';

    protected $fillable = [
        'key',
        'group',
        'description',
        'base_text',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(TranslationValue::class, 'translation_key_id');
    }
}
