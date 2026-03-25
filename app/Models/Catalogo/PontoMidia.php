<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PontoMidia extends Model
{
    use SoftDeletes;

    protected $table = 'ponto_midias';

    public const TIPO_IMAGEM = 'image';
    public const TIPO_VIDEO = 'video';
    public const TIPO_VIDEO_FILE = 'video_file';
    public const TIPO_VIDEO_LINK = 'video_link';

    protected $fillable = [
        'ponto_turistico_id',
        'tipo',
        'path',
        'url',
        'thumb_path',
        'ordem',
    ];

    protected $attributes = [
        'ordem' => 0,
        'tipo'  => self::TIPO_IMAGEM,
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function ponto()
    {
        return $this->belongsTo(PontoTuristico::class, 'ponto_turistico_id');
    }

    public static function supportsExternalUrl(): bool
    {
        return Schema::hasColumn('ponto_midias', 'url');
    }

    public static function supportsExtendedVideoType(): bool
    {
        $driver = config('database.default');

        return in_array($driver, ['pgsql', 'sqlite'], true);
    }

    public static function normalizeFileVideoType(): string
    {
        return self::supportsExtendedVideoType()
            ? self::TIPO_VIDEO_FILE
            : self::TIPO_VIDEO;
    }

    public function isImage(): bool
    {
        return $this->tipo === self::TIPO_IMAGEM;
    }

    public function isVideoFile(): bool
    {
        return in_array($this->tipo, [self::TIPO_VIDEO, self::TIPO_VIDEO_FILE], true);
    }

    public function isVideoLink(): bool
    {
        return $this->tipo === self::TIPO_VIDEO_LINK;
    }

    public function getUrlAttribute(): ?string
    {
        if ($this->isVideoLink()) {
            return $this->attributes['url'] ?? null;
        }

        if ($this->path) {
            return Storage::disk('public')->url($this->path);
        }

        return null;
    }

    public function getPathUrlAttribute(): ?string
    {
        return $this->path ? Storage::disk('public')->url($this->path) : null;
    }

    public function url(): ?string
    {
        return $this->getUrlAttribute();
    }
}
