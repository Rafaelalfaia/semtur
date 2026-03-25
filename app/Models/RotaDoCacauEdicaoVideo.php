<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RotaDoCacauEdicaoVideo extends Model
{
    use SoftDeletes;

    protected $table = 'rota_do_cacau_edicao_videos';

    protected $fillable = [
        'rota_do_cacau_edicao_id',
        'titulo',
        'descricao',
        'drive_url',
        'embed_url',
        'ordem',
    ];

    protected $casts = [
        'ordem' => 'integer',
    ];

    protected $attributes = [
        'ordem' => 0,
    ];

    protected $appends = [
        'embed_url_resolvida',
    ];

    public function edicao()
    {
        return $this->belongsTo(RotaDoCacauEdicao::class, 'rota_do_cacau_edicao_id');
    }

    public function getEmbedUrlResolvidaAttribute(): ?string
    {
        if (filled($this->embed_url)) {
            return $this->embed_url;
        }

        return static::buildEmbedUrl($this->drive_url);
    }

    public static function buildEmbedUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        if (preg_match('~(?:drive|docs)\.google\.com/file/d/([a-zA-Z0-9_-]+)~', $url, $m)) {
            return "https://drive.google.com/file/d/{$m[1]}/preview";
        }

        if (preg_match('~drive\.google\.com/open\?id=([a-zA-Z0-9_-]+)~', $url, $m)) {
            return "https://drive.google.com/file/d/{$m[1]}/preview";
        }

        if (preg_match('~drive\.google\.com/uc\?(?:[^#]*&)?id=([a-zA-Z0-9_-]+)~', $url, $m)) {
            return "https://drive.google.com/file/d/{$m[1]}/preview";
        }

        $query = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        if (!empty($query['id'])) {
            $id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $query['id']);

            if ($id !== '') {
                return "https://drive.google.com/file/d/{$id}/preview";
            }
        }

        return null;
    }
}
