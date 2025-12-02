<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Catalogo\{Categoria, Empresa, PontoTuristico};

class MapaApiController extends Controller
{
    /** Centro padrão (Altamira) – usado como fallback quando a URL não traz coordenadas. */
    private const FALLBACK_CENTER = [-3.206000, -52.210000];


    public function feed(Request $request)
    {
        $tipo      = $request->string('tipo')->lower()->value() ?: 'all'; // all|empresas|pontos
        $q         = trim((string)$request->input('q', ''));
        $limit     = max(1, min((int)$request->input('limit', 50), 500));
        $categoria = $request->input('categoria'); // id ou slug
        $bbox      = trim((string)$request->input('bbox', ''));
        $bboxUsed  = false;

        // Normaliza bbox
        $bounds = null;
        if ($bbox !== '') {
            $parts = array_map('trim', explode(',', $bbox));
            if (count($parts) === 4) {
                [$w, $s, $e, $n] = array_map('floatval', $parts);
                $bounds = [
                    'west'  => min($w, $e),
                    'east'  => max($w, $e),
                    'south' => min($s, $n),
                    'north' => max($s, $n),
                ];
                $bboxUsed = true;
            }
        }

        // Extrai/garante lat/lng de cada item
        $ensureLatLng = function (&$item) {
            if (isset($item['lat'], $item['lng']) && $item['lat'] !== null && $item['lng'] !== null) {
                return true;
            }
            $url = (string)($item['maps_url'] ?? '');
            if ($url) {
                // Bing cp=LAT~LNG  | Google @LAT,LNG
                if (preg_match('/(?:cp=|@)(-?\d+(?:\.\d+))[^-]*?(-?\d+(?:\.\d+))/', $url, $m)) {
                    $item['lat'] = (float)$m[1];
                    $item['lng'] = (float)$m[2];
                    return true;
                }
                if (str_contains(strtolower($url), 'altamira')) {
                    // fallback gentil pro centro de Altamira
                    $item['lat'] = -3.2049;
                    $item['lng'] = -52.2176;
                    return true;
                }
            }
            return false;
        };

        // Filtro por categoria (id ou slug)
        $categoriaFilter = function ($q) use ($categoria) {
            if (!$categoria) return;
            $q->whereHas('categorias', function ($qq) use ($categoria) {
                if (is_numeric($categoria)) {
                    $qq->where('categorias.id', (int)$categoria);
                } else {
                    $qq->where('categorias.slug', (string)$categoria);
                }
            });
        };

        // Mapeadores
        $mapEmpresa = function ($e) {
            return [
                'id'      => $e->id,
                'slug'    => $e->slug,
                'type'    => 'empresa',
                'nome'    => $e->nome,
                'lat'     => $e->lat,
                'lng'     => $e->lng,
                'foto'    => $e->perfil_url ?? $e->capa_url ?? $e->foto_capa_url ?? '/imagens/placeholder.jpg',
                'cidade'  => $e->cidade,
                'maps_url'=> $e->maps_url,
            ];
        };
        $mapPonto = function ($p) {
            return [
                'id'      => $p->id,
                'slug'    => $p->slug, // seu front pode cair pra /ponto/{id} se slug for null
                'type'    => 'ponto',
                'nome'    => $p->nome,
                'lat'     => $p->lat,
                'lng'     => $p->lng,
                'foto'    => $p->capa_url ?? $p->foto_capa_url ?? '/imagens/placeholder.jpg',
                'cidade'  => $p->cidade,
                'maps_url'=> $p->maps_url,
            ];
        };

        // === MESMO gate de publicação (trait) ===
        $empQ = \App\Models\Catalogo\Empresa::query()->publicados();
        $ptoQ = \App\Models\Catalogo\PontoTuristico::query()->publicados();

        if ($q !== '') {
            $empQ->where('nome', 'ilike', "%{$q}%");
            $ptoQ->where(function ($qq) use ($q) {
                $qq->where('nome', 'ilike', "%{$q}%")
                ->orWhere('descricao', 'ilike', "%{$q}%");
            });
        }

        $categoriaFilter($empQ);
        $categoriaFilter($ptoQ);

        if ($bounds) {
            $empQ->whereNotNull('lat')->whereNotNull('lng')
                ->whereBetween('lat', [$bounds['south'], $bounds['north']])
                ->whereBetween('lng', [$bounds['west'],  $bounds['east']]);
            $ptoQ->whereNotNull('lat')->whereNotNull('lng')
                ->whereBetween('lat', [$bounds['south'], $bounds['north']])
                ->whereBetween('lng', [$bounds['west'],  $bounds['east']]);
        }

        $empQ->orderBy('ordem')->orderBy('nome');
        $ptoQ->orderBy('ordem')->orderBy('nome');

        $items = collect();

        if ($tipo === 'empresas' || $tipo === 'all') {
            foreach ($empQ->limit($limit)->get() as $e) {
                $it = $mapEmpresa($e);
                if ($ensureLatLng($it)) $items->push($it);
            }
        }
        if ($tipo === 'pontos' || $tipo === 'all') {
            foreach ($ptoQ->limit($limit)->get() as $p) {
                $it = $mapPonto($p);
                if ($ensureLatLng($it)) $items->push($it);
            }
        }

        return response()->json([
            'items' => $items->values(),
            'meta'  => [
                'count'      => $items->count(),
                'tipo'       => $tipo,
                'categoria'  => $categoria ?: null,
                'bbox_used'  => $bboxUsed,
            ],
        ]);
    }




    /* =================== Helpers de geo =================== */

    /** Tenta lat/lng direto; se faltar, extrai da URL; se ainda faltar e a URL/cidade indicar Altamira, usa fallback. */
    private function ensureLatLng($lat, $lng, ?string $mapsUrl, ?string $cidade): array
    {
        if ($this->isValidLatLng($lat,$lng)) return [(float)$lat,(float)$lng];

        if ($mapsUrl) {
            [$a,$b] = $this->extractCoordsFromUrl($mapsUrl);
            if ($this->isValidLatLng($a,$b)) return [$a,$b];

            if ($this->containsAltamira($mapsUrl)) return self::FALLBACK_CENTER;
        }
        if (is_string($cidade) && $this->containsAltamira($cidade)) {
            return self::FALLBACK_CENTER;
        }
        return [null,null];
    }

   private function isValidLatLng($lat,$lng): bool
    {
        return is_numeric($lat) && is_numeric($lng)
            && $lat >= -90 && $lat <= 90
            && $lng >= -180 && $lng <= 180;
    }

    private function containsAltamira(string $text): bool
    {
        return mb_strpos(mb_strtolower($text), 'altamira') !== false;
    }

    /** bbox string → [west,south,east,north] (floats) ou null */
    private function parseBbox($raw): ?array
    {
        if (!is_string($raw) || trim($raw) === '') return null;
        if (!preg_match(
            '/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/',
            $raw, $m
        )) return null;

        $west  = (float) $m[1];
        $south = (float) $m[2];
        $east  = (float) $m[3];
        $north = (float) $m[4];

        // normaliza (ordem)
        if ($west > $east)  [$west, $east]   = [$east, $west];
        if ($south > $north)[$south, $north] = [$north, $south];

        return compact('west','south','east','north');
    }

    /** Testa se (lat,lng) está dentro do bbox */
    private function inBbox(array $bbox, float $lat, float $lng): bool
    {
        $inLng = $lng >= min($bbox['west'],$bbox['east'])   && $lng <= max($bbox['west'],$bbox['east']);
        $inLat = $lat >= min($bbox['south'],$bbox['north']) && $lat <= max($bbox['south'],$bbox['north']);
        return $inLng && $inLat;
    }

    /**
     * Extrai coordenadas de uma URL de mapa (Google/Bing/OSM/geo:).
     * Suporta:
     *  - Google: .../@LAT,LNG,... | ?q=LAT,LNG | ?ll=LAT,LNG | ?query=LAT,LNG | ?center=LAT,LNG | !3dLAT!4dLNG
     *  - Bing:   ?cp=LAT~LNG | ?sp=point.LAT_LNG_...
     *  - OSM:    ?mlat=...&mlon=...  ou  ?lat=...&lon/lng=...
     *  - geo:    geo:LAT,LNG
     *  - Genérico: primeiro "LAT, LNG" encontrado no texto
     */
    private function extractCoordsFromUrl(?string $url): array
    {
        if (!$url) return [null, null];
        $s = urldecode(trim($url));

        // Google
        if (preg_match('~@\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)~', $s, $m)) return [(float)$m[1], (float)$m[2]];
        if (preg_match('~[?&](?:q|ll|query|center)=\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)~i', $s, $m)) return [(float)$m[1], (float)$m[2]];
        if (preg_match('~!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)~', $s, $m)) return [(float)$m[1], (float)$m[2]];
        if (preg_match('~!4d(-?\d+(?:\.\d+)?)!3d(-?\d+(?:\.\d+)?)~', $s, $m)) return [(float)$m[2], (float)$m[1]];

        // Bing
        if (preg_match('~[?&]cp=(-?\d+(?:\.\d+)?)\~(-?\d+(?:\.\d+)?)~i', $s, $m)) return [(float)$m[1], (float)$m[2]];
        if (preg_match('~[?&]sp=point\.(-?\d+(?:\.\d+)?)_(-?\d+(?:\.\d+)?)~i', $s, $m)) return [(float)$m[1], (float)$m[2]];

        // OSM
        if (preg_match('~[?&](?:mlat|lat)=(-?\d+(?:\.\d+)?)~i', $s, $ma)
         && preg_match('~[?&](?:mlon|lon|lng)=(-?\d+(?:\.\d+)?)~i', $s, $mb)) return [(float)$ma[1], (float)$mb[1]];

        // geo:
        if (preg_match('~^geo:\s*(-?\d+(?:\.\d+)?),\s*(-?\d+(?:\.\d+)?)~i',$s,$m)) return [(float)$m[1],(float)$m[2]];

        // Genérico
        if (preg_match('~(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)~', $s, $m)) return [(float)$m[1], (float)$m[2]];

        return [null, null];
    }

    /**
     * (Opcional) Endpoint simples só com markers, caso queira usar em outro lugar.
     * Mantido por compatibilidade; reutiliza a lógica de feed, mas sem meta.
     */
    public function markers(Request $r)
    {
        $resp = $this->feed($r);
        $data = $resp->getData(true);
        return response()->json($data['items'] ?? []);
    }
}
