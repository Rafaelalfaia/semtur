<?php

namespace App\Models\Catalogo;

use App\Models\Concerns\HasPublicado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Empresa extends Model
{
    use SoftDeletes, HasPublicado;

    protected $fillable = [
        'nome','slug','descricao','telefone','email','site_url','maps_url',
        'endereco','bairro','cidade','lat','lng','foto_perfil_path','foto_capa_path',
        'ordem','status','published_at','created_by', 'contatos',
    ];

    protected $appends = ['foto_capa_url', 'foto_perfil_url'];


    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'published_at' => 'datetime',
        'contatos' => 'array',
    ];

    // ===== Status padronizado =====
    public const STATUS_RASCUNHO  = 'rascunho';
    public const STATUS_PUBLICADO = 'publicado';
    public const STATUS_ARQUIVADO = 'arquivado';
    public const STATUS = [self::STATUS_RASCUNHO,self::STATUS_PUBLICADO,self::STATUS_ARQUIVADO];

    protected $attributes = [
        'status' => self::STATUS_RASCUNHO,
        'ordem'  => 0,
    ];

    protected static function booted(): void
    {
        // Blindagem: sanitiza opcionais e garante slug único SEMPRE
        static::saving(function (self $m) {
            foreach (['site_url','maps_url','email'] as $f) {
                $val = trim((string)($m->{$f} ?? ''));
                if ($val === '' || $val === '?') {
                    $m->{$f} = null;
                }
            }

            // base do slug: slug enviado ou gerado do nome
            $base = $m->slug ?: ($m->nome ? Str::slug($m->nome) : null);
            if ($base) {
                $m->slug = static::uniqueSlug($base, $m->id);
            }
        });
    }


    public function getSocialLinksAttribute(): array
    {
        $c = is_array($this->contatos) ? $this->contatos : [];

        $pick = function(array $keys) use ($c) {
            foreach ($keys as $k) {
                $v = data_get($c, $k);
                if (filled($v)) return $v;
                // fallback para campos “soltos” do model, se existirem
                if (property_exists($this, $k) && filled($this->{$k})) return $this->{$k};
            }
            return null;
        };

        $normUrl = function (?string $u): ?string {
            if (!$u) return null;
            $u = trim($u);
            if (preg_match('~^https?://~i', $u)) return $u;
            return 'https://'.$u;
        };

        $handle = fn(?string $h) => $h ? ltrim(trim($h), '@') : null;

        // WhatsApp: usa contatos.whatsapp OU telefone
        $rawWhats = preg_replace('/\D+/', '', (string) ($pick(['whatsapp','telefone']) ?? ''));
        if ($rawWhats) {
            // se vier com 11 dígitos (BR sem 55), prefixa 55
            if (strlen($rawWhats) === 11) $rawWhats = '55'.$rawWhats;
            // se tiver 10–13 dígitos e não iniciar com 55, prefixa por segurança
            if (!str_starts_with($rawWhats, '55') && strlen($rawWhats) >= 10) {
                $rawWhats = '55'.$rawWhats;
            }
        }

        // Instagram / Facebook (aceita @handle e URL)
        $ig = $pick(['instagram','instagram_url','ig']);
        $ig = $ig
            ? (str_contains($ig,'instagram.com') ? $ig : 'https://instagram.com/'.$handle($ig))
            : null;

        $fb = $pick(['facebook','facebook_url','fb']);
        $fb = $fb
            ? (str_contains($fb,'facebook.com') ? $fb : 'https://facebook.com/'.$handle($fb))
            : null;

        // Site (aceita sem http)
        $site = $pick(['site','site_url','website','url']);
        $site = $site ? $normUrl($site) : null;

        // YouTube / TikTok (opcional se você já guarda em contatos)
        $yt = $pick(['youtube','youtube_url','yt']);
        $yt = $yt
            ? (str_contains($yt,'youtube.com') || str_contains($yt,'youtu.be')
                ? $yt
                : 'https://www.youtube.com/@'.$handle($yt))
            : null;

        $tt = $pick(['tiktok','tiktok_url','tt']);
        $tt = $tt
            ? (str_contains($tt,'tiktok.com')
                ? $tt
                : 'https://www.tiktok.com/@'.$handle($tt))
            : null;

        // Maps: usa maps_url se existir; senão monta por lat/lng
        $maps = $pick(['maps_url']);
        if (!$maps && $this->lat && $this->lng) {
            $maps = 'https://www.google.com/maps?q='.$this->lat.','.$this->lng;
        }

        return [
            'whatsapp'  => $rawWhats ? 'https://wa.me/'.$rawWhats : null,
            'site'      => $site,
            'instagram' => $ig,
            'facebook'  => $fb,
            'youtube'   => $yt,
            'tiktok'    => $tt,
            'maps'      => $maps,
            'email'     => $pick(['email']), // pode usar em um "mailto:" no Blade
        ];
    }


    public function getCapaUrlAttribute(): ?string
    {
        return $this->fotoCapaUrl();
    }
    public function getPerfilUrlAttribute(): ?string
    {
        return $this->fotoPerfilUrl();
    }

    public function getFotoCapaUrlAttribute(): string
    {
        $caminho = $this->firstFilledAttr(['capa_path','foto_capa_path','capa']);
        return $caminho ? Storage::url($caminho) : asset('images/placeholder-empresa.png');
    }

    public function getFotoPerfilUrlAttribute(): string
    {
        $caminho = $this->firstFilledAttr(['perfil_path','foto_perfil_path','perfil']);
        if (!$caminho) {
            // fallback para capa se não houver perfil
            $caminho = $this->firstFilledAttr(['capa_path','foto_capa_path','capa']);
        }
        return $caminho ? Storage::url($caminho) : asset('images/placeholder-empresa.png');
    }

    protected function firstFilledAttr(array $candidatos): ?string
    {
        foreach ($candidatos as $key) {
            // getAttribute evita depender do Schema
            $val = $this->getAttribute($key);
            if (is_string($val) && $val !== '') {
                return ltrim($val, '/');
            }
        }
        return null;
    }

    public function getPublicadoAttribute(): bool
    {
        return $this->status === self::STATUS_PUBLICADO;
    }

    /** Gera slug único (foo, foo-2, foo-3...). */
    public static function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $i = 2;

        $exists = function (string $s) use ($ignoreId): bool {
            // ANTES: static::where('slug', $s)
            $q = static::withTrashed()->where('slug', $s);
            if ($ignoreId) $q->where('id','<>',$ignoreId);
            return $q->exists();
        };

        while ($exists($slug)) { $slug = $base.'-'.$i; $i++; }
        return $slug;
    }

    // Relações
    public function categorias()
    {
        return $this->belongsToMany(Categoria::class, 'categoria_empresa');
    }

    public function pontos()
    {
        return $this->belongsToMany(PontoTuristico::class, 'empresa_ponto_turistico', 'empresa_id', 'ponto_turistico_id');
    }

    public function recomendacoes()
    {
        return $this->hasMany(EmpresaRecomendacao::class);
    }

    // Helpers de URL
    public function fotoPerfilUrl(): ?string
    {
        return $this->foto_perfil_path ? Storage::disk('public')->url($this->foto_perfil_path) : null;
    }

    public function fotoCapaUrl(): ?string
    {
        return $this->foto_capa_path ? Storage::disk('public')->url($this->foto_capa_path) : null;
    }

}
