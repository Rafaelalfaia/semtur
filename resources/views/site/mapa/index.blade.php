@extends('site.layouts.app')
@section('title','Descubra Altamira')
@section('meta.description','Guia turístico oficial de Altamira, Pará.')
@section('meta.image', $capaUrl ?? '/images/og-default.jpg')

@section('title','Mapa — Explorar')

@push('head')
  {{-- Leaflet CSS --}}
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

  <style>
    :root{
      --brand:#00837B;
      --glass: rgba(255,255,255,.92);
      --text:#0f172a;
      --muted:#64748b;
      --handle:#E5E7EA;
      --shadow: 0 4px 14px rgba(0,0,0,.08);
      --shadow-strong: 0 8px 22px rgba(0,0,0,.10);
      --nav-h: 80px;               /* altura aprox. da bottom-nav; ajuste se for diferente */
      --sheet-gap: 12px;           /* respiro acima da bottom-nav */
    }

    /* base */
    #mapa-root{ position:relative; width:100%; height:100svh; background:#fff; }
    #map{ position:fixed; inset:0; z-index:1; background:#E8EEF6; }
    .leaflet-control-container{ z-index:22; }
    .leaflet-container img{ max-width:none !important; }

    /* gradiente topo */
    .top-gradient{
      position:fixed; left:0; right:0; top:0; height:414px; z-index:5;
      background: linear-gradient(179.92deg,#00837B 0.07%, rgba(255,255,255,0) 58.56%);
      pointer-events:none;
    }

    /* busca pill */
    .search-wrap{
      position:fixed; left:0; right:0; top:56px; z-index:10;
      display:flex; justify-content:center; padding:0 16px;
    }
    .search-pill{
      display:flex; align-items:center; gap:10px;
      width:min(960px, 92vw); height:42px; padding:0 12px;
      border-radius:999px; background:var(--glass); backdrop-filter: blur(10px);
      box-shadow: var(--shadow); border:1px solid rgba(255,255,255,.55);
    }
    .search-pill input{
      flex:1; height:100%; border:0; outline:0; background:transparent;
      font: 400 14px/20px system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial;
      color:var(--text);
    }
    .search-icon{ width:18px; height:18px; opacity:.65 }

    /* bottom-sheet Nearby (apenas carrossel, sem botões) */
    .nearby{
      position:fixed; left:12px; right:12px; z-index:20;
      bottom: calc(var(--nav-h) + var(--sheet-gap) + env(safe-area-inset-bottom));
      display:flex; flex-direction:column; gap:8px;
      background:#fff; box-shadow: var(--shadow-strong);
      border-radius:20px; padding:12px;
    }
    .nearby .handle{
      margin:2px auto 6px; width:48px; height:6px; border-radius:3px; background:var(--handle);
    }
    .cards{
      display:flex; gap:10px; overflow-x:auto; padding:4px 2px 2px; scrollbar-width:none;
    }
    .card{
      position:relative; width:92px; height:92px; border-radius:12px; overflow:hidden; flex:none; cursor:pointer;
      background:#f1f5f9; box-shadow:0 0 16px rgba(0,0,0,.06); border:1px solid #ECECEC;
    }
    .card img{ width:100%; height:100%; object-fit:cover; display:block; }
    /* sem rótulo e sem "Ver" nos cards */

    @media (min-width: 1024px){
      .nearby{ left:50%; right:auto; transform:translateX(-50%); width:min(980px, 92vw); bottom:16px; }
      .search-wrap{ top:72px; }
      .bottom-nav-wrap{ display:none; }
    }
  </style>
@endpush

@section('site.content')
  @php
    use Illuminate\Support\Facades\Route as R;

    // endpoint do feed
    $apiFeed = R::has('api.mapa.feed') ? route('api.mapa.feed') : url('/api/mapa/feed');
    // snapshot inicial (opcional)
    $initItems = collect($initItems ?? [])->values();

    // padrões de rotas de show (iguais à Home), com token que o JS substitui
    $TOK = '__TOKEN__';
    $safeUrl = function (string $name, array $params = [], $fallback = null) {
        try { return route($name, $params); } catch (\Throwable $e) { return $fallback; }
    };
    $EMP_PATTERNS = array_values(array_filter([
      $safeUrl('site.empresa', ['empresa'  => $TOK], null),
      $safeUrl('site.empresa', ['slugOrId' => $TOK], null),
    ]));
    $PTO_PATTERNS = array_values(array_filter([
      $safeUrl('site.ponto', ['ponto' => $TOK], null),
    ]));
  @endphp

  <div id="mapa-root">
    <div id="map" role="img" aria-label="Mapa de pontos turísticos e empresas"></div>

    {{-- gradiente topo --}}
    <div class="top-gradient" aria-hidden="true"></div>

    {{-- busca --}}
    <div class="search-wrap">
      <div class="search-pill">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" role="img" aria-hidden="true">
          <path d="M21 21l-4.2-4.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
          <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.6"/>
        </svg>
        <input id="q" type="search" placeholder="Pesquise..." autocomplete="off" />
      </div>
    </div>

    {{-- bottom-sheet: só carrossel --}}
    <section class="nearby" id="nearby">
      <div class="handle" aria-hidden="true"></div>
      <div class="cards" id="cards" aria-label="Itens próximos"></div>
    </section>

    {{-- bottom-nav em mobile/tablet, abaixo do sheet --}}
    <div class="bottom-nav-wrap" style="position:fixed; left:0; right:0; bottom:0; z-index:12;">
      @includeIf('site.partials._bottom_nav')
    </div>
  </div>
@endsection

@push('scripts')
  {{-- Leaflet JS --}}
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const waitLeaflet = () => new Promise(res=>{
      if (window.L && typeof L.map==='function') return res();
      const t = setInterval(()=>{ if (window.L) { clearInterval(t); res(); } }, 30);
    });

    waitLeaflet().then(() => {
      // ===== Config =====
      const API_FEED     = @json($apiFeed);
      const TOK          = @json($TOK);
      const EMP_PATTERNS = @json($EMP_PATTERNS);
      const PTO_PATTERNS = @json($PTO_PATTERNS);
      const INIT_ITEMS   = @json($initItems);

      // ===== Deep link (focus) =====
      const url  = new URL(window.location.href);
      const qs   = url.searchParams;
      const focusParam = qs.get('focus');   // ex.: "empresa:abelha-cacau" ou "ponto:37"
      const openParam  = qs.get('open');    // "1" | "true"
      const latParam   = parseFloat(qs.get('lat'));
      const lngParam   = parseFloat(qs.get('lng'));

      const focus = (() => {
        if (!focusParam) return null;
        const [type, ...rest] = String(focusParam).split(':');
        const key = rest.join(':');
        if (!type || !key) return null;
        const t = type === 'empresa' ? 'empresa' : (type === 'ponto' ? 'ponto' : null);
        return t ? { type: t, key } : null;
      })();
      const shouldOpen = (openParam === '1' || (openParam||'').toLowerCase()==='true');
      const hasCoords  = Number.isFinite(latParam) && Number.isFinite(lngParam);

      // ===== Mapa =====
      const map = L.map('map', { zoomControl: true });
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);

      const CENTER  = hasCoords ? [latParam, lngParam] : [-3.2049, -52.2176];
      const START_Z = hasCoords ? 15 : 13;
      map.setView(CENTER, START_Z);

      // ping visual quando vem com lat/lng
      let pingCircle = null;
      if (hasCoords) {
        pingCircle = L.circleMarker(CENTER, { radius: 10, color:'#00837B', fillColor:'#00837B', fillOpacity:.35, opacity:.65 }).addTo(map);
        setTimeout(()=>{ if (pingCircle) { map.removeLayer(pingCircle); pingCircle=null; } }, 2500);
      }

      // ===== Estado =====
      let firstLoad   = true;
      let currentQ    = '';
      let abortCtrl   = null;

      let markersLayer = L.layerGroup().addTo(map);
      const markersMap  = new Map(); // `${type}:${id}` -> marker

      // ===== Utils =====
      const debounce = (fn, ms=250) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a),ms); }; };
      const keyFor   = (it)=> `${it.type}:${it.id}`;
      const ensureAbs = (url)=> !url ? '' : (/^https?:\/\//i.test(url)? url : window.location.origin.replace(/\/$/,'') + (url.startsWith('/')? url : '/'+url));

      function showHref(item){
        const token = item.slug || item.id;
        const patterns = item.type==='empresa' ? EMP_PATTERNS : PTO_PATTERNS;
        for (const p of patterns){ if (p) return p.replace(TOK, token); }
        return (item.type==='empresa' ? '/empresa/' : '/ponto/') + token; // fallback
      }

      function popupHtml(item){
        const foto  = ensureAbs(item.foto || '');
        const href  = showHref(item);
        const title = (item.nome || '').replace(/</g,'&lt;');
        const tipo  = item.type==='empresa' ? 'Empresa' : 'Ponto turístico';
        return `
          <div style="min-width:220px;max-width:260px;font-family:system-ui,Arial;">
            ${ foto ? `<div style="border-radius:12px;overflow:hidden;border:1px solid #ECECEC">
              <img src="${foto}" alt="${title}" style="display:block;width:100%;height:140px;object-fit:cover">
            </div>` : '' }
            <div style="margin-top:6px;font-weight:600;color:#0f172a">${title}</div>
            <div style="font-size:12px;color:#64748b">${tipo}</div>
            <div style="margin-top:8px">
              <a href="${href}" style="display:inline-block;padding:6px 12px;border-radius:999px;background:#ffffff;border:1px solid rgba(15,23,42,.08);color:#0f172a;text-decoration:none;font-weight:600;font-size:12px;">Ver</a>
            </div>
          </div>
        `;
      }

      function clearMarkers(){
        markersLayer.clearLayers();
        markersMap.clear();
      }

      function syncMarkers(items){
        const incoming = new Set(items.map(keyFor));
        for (const [k, mk] of markersMap.entries()){
          if(!incoming.has(k)){ markersLayer.removeLayer(mk); markersMap.delete(k); }
        }
        items.forEach(it=>{
          if (typeof it.lat !== 'number' || typeof it.lng !== 'number') return;
          const k = keyFor(it);
          if(!markersMap.get(k)){
            const marker = L.marker([it.lat, it.lng], { title: it.nome || '' });
            marker.bindPopup(popupHtml(it), { maxWidth: 280, closeButton: true });
            markersLayer.addLayer(marker);
            markersMap.set(k, marker);
          }
        });
      }

      function renderCards(items){
        const $cards = document.getElementById('cards');
        if(!$cards) return;
        $cards.innerHTML = '';
        items.forEach(it=>{
          const foto = ensureAbs(it.foto || '');
          const el = document.createElement('button');
          el.className = 'card';
          el.innerHTML = `${ foto ? `<img src="${foto}" alt="">` : '' }`; // sem rótulo/“Ver”
          el.addEventListener('click', ()=>{
            const k = keyFor(it);
            const mk = markersMap.get(k);
            if(mk){
              map.panTo([it.lat, it.lng]);
              map.setZoom(Math.max(map.getZoom(), 15));
              mk.openPopup();
            }
          });
          $cards.appendChild(el);
        });
      }

      function applyFocus(items){
        if (!focus) return;
        const it = (items||[]).find(x =>
          x && x.type === focus.type &&
          (String(x.slug) === String(focus.key) || String(x.id) === String(focus.key))
        );
        if (!it) return;
        const k = `${it.type}:${it.id}`;
        const mk = markersMap.get(k);
        if (mk) {
          map.panTo([it.lat, it.lng]);
          map.setZoom(Math.max(map.getZoom(), 15));
          if (shouldOpen) mk.openPopup();
        }
      }

      function buildUrl(){
        const p = new URLSearchParams();
        p.set('tipo', 'all');   // sempre “Todos”
        p.set('limit', '200');
        if (currentQ) p.set('q', currentQ);
        if (!firstLoad) {
          const b = map.getBounds();
          p.set('bbox', `${b.getWest()},${b.getSouth()},${b.getEast()},${b.getNorth()}`);
        }
        return `${API_FEED}?${p.toString()}`;
      }

      async function fetchFeed(){
        if (abortCtrl) abortCtrl.abort();
        abortCtrl = new AbortController();
        try{
          const res = await fetch(buildUrl(), { signal: abortCtrl.signal });
          if (!res.ok) throw new Error(`HTTP ${res.status}`);
          const data = await res.json();
          const items = Array.isArray(data.items) ? data.items : [];
          syncMarkers(items);
          renderCards(items);
          applyFocus(items); // foca após carregar
        }catch(err){
          if (err.name!=='AbortError') console.error('Erro ao carregar feed:', err);
          clearMarkers(); renderCards([]);
        }finally{ firstLoad = false; }
      }
      const fetchFeedDebounced = debounce(fetchFeed, 220);

      // snapshot inicial (se houver)
      if (Array.isArray(INIT_ITEMS) && INIT_ITEMS.length){
        syncMarkers(INIT_ITEMS);
        renderCards(INIT_ITEMS);
        try{ map.fitBounds(INIT_ITEMS.map(i=>[i.lat,i.lng]), { padding:[30,30] }); }catch(e){}
      }

      // primeira carga e atualizações por bbox
      map.whenReady(fetchFeed);
      map.on('moveend', fetchFeedDebounced);

      // busca
      const $q = document.getElementById('q'); let qTimer=null;
      if($q){
        $q.addEventListener('input', ()=>{
          clearTimeout(qTimer);
          qTimer = setTimeout(()=>{
            currentQ = ($q.value || '').trim();
            fetchFeedDebounced();
          }, 300);
        });
      }
    });
  });
  </script>
@endpush
