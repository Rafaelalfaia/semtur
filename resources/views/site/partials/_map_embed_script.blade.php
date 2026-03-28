<script>
document.addEventListener('DOMContentLoaded', () => {
    const config = @json($mapConfig ?? []);
    const root = document.getElementById(config.rootId || '');
    const mapElement = document.getElementById(config.mapId || '');

    if (!root || !mapElement || root.dataset.mapInitialized === 'true') {
        return;
    }

    root.dataset.mapInitialized = 'true';

    const waitLeaflet = () => new Promise((resolve) => {
        if (window.L && typeof window.L.map === 'function') {
            resolve();
            return;
        }

        const timer = window.setInterval(() => {
            if (window.L && typeof window.L.map === 'function') {
                window.clearInterval(timer);
                resolve();
            }
        }, 30);
    });

    waitLeaflet().then(() => {
        const routeToken = config.routeToken || '__TOKEN__';
        const empresaPatterns = Array.isArray(config.empresaPatterns) ? config.empresaPatterns : [];
        const pontoPatterns = Array.isArray(config.pontoPatterns) ? config.pontoPatterns : [];
        const initialItems = Array.isArray(config.initialItems) ? config.initialItems : [];
        const searchInput = document.getElementById(config.searchId || '');
        const searchClearButton = document.getElementById('map-search-clear');
        const cardsElement = document.getElementById(config.cardsId || '');
        const statusElement = config.statusId ? document.getElementById(config.statusId) : null;
        const railControls = Array.from(root.querySelectorAll('[data-map-scroll-target]'));
        const filterButtons = config.filterButtonSelector
            ? Array.from(root.querySelectorAll(config.filterButtonSelector))
            : [];

        const url = new URL(window.location.href);
        const queryString = url.searchParams;
        const focusParam = config.readFocusFromUrl ? queryString.get('focus') : null;
        const openParam = config.readFocusFromUrl ? queryString.get('open') : null;
        const latParam = config.readFocusFromUrl ? parseFloat(queryString.get('lat')) : NaN;
        const lngParam = config.readFocusFromUrl ? parseFloat(queryString.get('lng')) : NaN;
        const hasCoords = Number.isFinite(latParam) && Number.isFinite(lngParam);
        const focus = (() => {
            if (!focusParam) {
                return null;
            }

            const [type, ...rest] = String(focusParam).split(':');
            const key = rest.join(':');
            if (!type || !key) {
                return null;
            }

            if (type === 'empresa' || type === 'ponto') {
                return { type, key };
            }

            return null;
        })();
        const shouldOpen = openParam === '1' || String(openParam || '').toLowerCase() === 'true';
        const defaultCenter = Array.isArray(config.defaultCenter) ? config.defaultCenter : [-3.2049, -52.2176];
        const defaultZoom = Number.isFinite(config.defaultZoom) ? config.defaultZoom : 13;
        const focusedZoom = Number.isFinite(config.focusedZoom) ? config.focusedZoom : 15;
        const markerSizes = (config.markerSizes && typeof config.markerSizes === 'object') ? config.markerSizes : null;
        const pingRadii = (config.pingRadii && typeof config.pingRadii === 'object') ? config.pingRadii : null;

        const viewportMode = () => {
            if (window.matchMedia('(max-width: 767px)').matches) {
                return 'mobile';
            }

            if (window.matchMedia('(max-width: 1023px)').matches) {
                return 'tablet';
            }

            return 'desktop';
        };

        const currentMarkerSize = () => {
            const mode = viewportMode();
            const fallback = { width: 30, height: 30, anchorX: 15, anchorY: 15, dot: 12 };
            return markerSizes?.[mode] || markerSizes?.desktop || fallback;
        };

        const currentPingRadius = () => {
            const mode = viewportMode();
            return pingRadii?.[mode] || pingRadii?.desktop || 10;
        };

        const createMarkerIcon = (item) => {
            if (!markerSizes) {
                return null;
            }

            const size = currentMarkerSize();
            const kind = item.type === 'empresa' ? 'empresa' : 'ponto';
            return window.L.divIcon({
                className: 'site-map-marker-wrap',
                html: `<span class="site-map-marker site-map-marker--${kind}"></span>`,
                iconSize: [size.width, size.height],
                iconAnchor: [size.anchorX ?? Math.round(size.width / 2), size.anchorY ?? Math.round(size.height / 2)],
                popupAnchor: [0, -Math.max(10, Math.round(size.height * 0.34))],
            });
        };

        const isCompactHomeMap = root.classList.contains('site-home-map-shell--compact');
    const isTouchPreview = () => isCompactHomeMap && window.matchMedia('(max-width: 1023px)').matches;

    const map = window.L.map(config.mapId, {
        zoomControl: true,
        scrollWheelZoom: true,
        dragging: true,
        touchZoom: true,
        doubleClickZoom: true,
        boxZoom: false,
        keyboard: false,
        tap: true,
    });

    const applyPreviewInteractionMode = () => {
        const locked = isTouchPreview();
        const method = locked ? 'disable' : 'enable';

        if (map.dragging && typeof map.dragging[method] === 'function') {
            map.dragging[method]();
        }

        if (map.touchZoom && typeof map.touchZoom[method] === 'function') {
            map.touchZoom[method]();
        }

        if (map.doubleClickZoom && typeof map.doubleClickZoom[method] === 'function') {
            map.doubleClickZoom[method]();
        }

        if (map.tap && typeof map.tap[method] === 'function') {
            map.tap[method]();
        }

        if (map.scrollWheelZoom) {
            if (locked) {
                map.scrollWheelZoom.disable();
            } else {
                map.scrollWheelZoom.enable();
            }
        }

        if (map.boxZoom) {
            map.boxZoom.disable();
        }

        if (map.keyboard) {
            map.keyboard.disable();
        }

        map.getContainer().classList.toggle('is-touch-preview', locked);
    };

        window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap',
        }).addTo(map);

        map.setView(hasCoords ? [latParam, lngParam] : defaultCenter, hasCoords ? focusedZoom : defaultZoom);

        applyPreviewInteractionMode();

        let pingCircle = null;
        if (hasCoords) {
            pingCircle = window.L.circleMarker([latParam, lngParam], {
                radius: currentPingRadius(),
                color: '#00837B',
                fillColor: '#00837B',
                fillOpacity: 0.35,
                opacity: 0.65,
            }).addTo(map);

            window.setTimeout(() => {
                if (pingCircle) {
                    map.removeLayer(pingCircle);
                    pingCircle = null;
                }
            }, 2500);
        }

        let firstLoad = true;
        let currentQuery = typeof config.initialQuery === 'string' ? config.initialQuery : '';
        let currentCategory = typeof config.initialCategory === 'string' ? config.initialCategory : '';
        let abortController = null;

        const markersLayer = window.L.layerGroup().addTo(map);
        const markersMap = new Map();

        const debounce = (fn, ms = 250) => {
            let timer;
            return (...args) => {
                window.clearTimeout(timer);
                timer = window.setTimeout(() => fn(...args), ms);
            };
        };

        railControls.forEach((button) => {
            button.addEventListener('click', () => {
                const targetId = button.getAttribute('data-map-scroll-target');
                const direction = Number(button.getAttribute('data-map-scroll-direction') || '1');
                const target = targetId ? document.getElementById(targetId) : null;

                if (!target) {
                    return;
                }

                const step = Math.max(120, Math.round(target.clientWidth * 0.72));
                target.scrollBy({ left: step * direction, behavior: 'smooth' });
            });
        });

        const keyFor = (item) => `${item.type}:${item.id}`;
        const ensureAbs = (href) => {
            if (!href) {
                return '';
            }

            if (/^https?:\/\//i.test(href)) {
                return href;
            }

            return window.location.origin.replace(/\/$/, '') + (href.startsWith('/') ? href : `/${href}`);
        };
        const routeHref = (item) => item.maps_url || (item.lat && item.lng ? `https://www.google.com/maps?q=${item.lat},${item.lng}` : '');

        function showHref(item) {
            const token = item.slug || item.id;
            const patterns = item.type === 'empresa' ? empresaPatterns : pontoPatterns;

            for (const pattern of patterns) {
                if (pattern) {
                    return pattern.replace(routeToken, token);
                }
            }

            return `${item.type === 'empresa' ? '/empresa/' : '/ponto/'}${token}`;
        }

        function popupHtml(item) {
            const foto = ensureAbs(item.foto || '');
            const href = showHref(item);
            const directionsHref = routeHref(item);
            const title = String(item.nome || '').replace(/</g, '&lt;');
            const subtitle = String(item.cidade || i18n.altamira).replace(/</g, '&lt;');
            const tipo = item.type === 'empresa' ? i18n.company : i18n.point;

            return `
                <div class="site-map-popup">
                    ${foto ? `<div class="site-map-popup-media"><img src="${foto}" alt="${title}" class="site-map-popup-image"></div>` : ''}
                    <div class="site-map-popup-copy">
                        <span class="site-badge">${tipo}</span>
                        <div class="site-map-popup-title">${title}</div>
                        <div class="site-map-popup-subtitle">${subtitle}</div>
                        <div class="site-map-popup-actions">
                            <a href="${href}" class="site-button-primary site-map-popup-button">${i18n.detail}</a>
                            ${directionsHref ? `<a href="${directionsHref}" target="_blank" rel="noopener noreferrer" class="site-button-secondary site-map-popup-button">${i18n.route}</a>` : ''}
                        </div>
                    </div>
                </div>
            `;
        }

        function clearMarkers() {
            markersLayer.clearLayers();
            markersMap.clear();
        }

        function syncMarkers(items) {
            const incoming = new Set(items.map(keyFor));

            for (const [key, marker] of markersMap.entries()) {
                if (!incoming.has(key)) {
                    markersLayer.removeLayer(marker);
                    markersMap.delete(key);
                }
            }

            items.forEach((item) => {
                if (typeof item.lat !== 'number' || typeof item.lng !== 'number') {
                    return;
                }

                const key = keyFor(item);
                if (!markersMap.has(key)) {
                    const markerOptions = { title: item.nome || '' };
                    const icon = createMarkerIcon(item);

                    if (icon) {
                        markerOptions.icon = icon;
                    }

                    const marker = window.L.marker([item.lat, item.lng], markerOptions);
                    marker.__siteMapType = item.type === 'empresa' ? 'empresa' : 'ponto';
                    marker.bindPopup(popupHtml(item), { maxWidth: 320, closeButton: true });
                    markersLayer.addLayer(marker);
                    markersMap.set(key, marker);
                }
            });
        }

        function refreshMarkerSizes() {
            if (!markerSizes) {
                return;
            }

            markersMap.forEach((marker) => {
                marker.setIcon(createMarkerIcon({ type: marker.__siteMapType || 'ponto' }));
            });

            if (pingCircle) {
                pingCircle.setRadius(currentPingRadius());
            }
        }

        function updateStatus(items) {
            if (!statusElement) {
                return;
            }

            const total = items.length;
            const categoryLabel = filterButtons.find((button) => button.dataset.category === currentCategory)?.dataset.label
                || config.currentCategoryLabel
                || i18n.all;
            const queryLabel = currentQuery ? ` para "${currentQuery}"` : '';
            statusElement.textContent = total
                ? `${total} locais publicados${categoryLabel && categoryLabel !== 'Tudo' ? ` em ${categoryLabel}` : ''}${queryLabel}`
                : i18n.emptyStatus;
        }

        function renderCards(items) {
            if (!cardsElement) {
                return;
            }

            cardsElement.innerHTML = '';

            if (!items.length) {
                cardsElement.innerHTML = `
                    <div class="site-map-empty-state">
                        <p class="site-map-empty-title">${config.emptyTitle || i18n.emptyTitle}</p>
                        <p class="site-map-empty-copy">${config.emptyCopy || i18n.emptyCopy}</p>
                    </div>
                `;
                updateStatus(items);
                return;
            }

            items.slice(0, Number.isFinite(config.resultLimit) ? config.resultLimit : 12).forEach((item) => {
                const card = document.createElement('article');
                const href = showHref(item);
                const directionsHref = routeHref(item);

                card.className = 'site-map-card';
                card.innerHTML = `
                    ${item.foto ? `<button type="button" class="site-map-card-media"><img src="${ensureAbs(item.foto)}" alt="${String(item.nome || '').replace(/"/g, '&quot;')}" class="site-map-card-image"></button>` : '<button type="button" class="site-map-card-media site-map-card-media-placeholder"></button>'}
                    <div class="site-map-card-body">
                        <h3 class="site-map-card-title">${item.nome || i18n.itemName}</h3>
                        <p class="site-map-card-subtitle">${item.cidade || i18n.altamira}</p>
                        <p class="site-map-card-helper">${directionsHref ? i18n.helperWithRoute : i18n.helperWithoutRoute}</p>
                        <div class="site-map-card-actions">
                            <button type="button" class="site-map-card-link is-primary">${i18n.focus}</button>
                            <a href="${href}" class="site-map-card-link">${i18n.detail}</a>
                            ${directionsHref ? `<a href="${directionsHref}" target="_blank" rel="noopener noreferrer" class="site-map-card-link">${i18n.route}</a>` : ''}
                        </div>
                    </div>
                `;

                const focusButton = card.querySelector('.is-primary');
                const mediaButton = card.querySelector('.site-map-card-media');
                const focusItem = () => {
                    const marker = markersMap.get(keyFor(item));
                    if (marker) {
                        map.setView([item.lat, item.lng], Math.max(map.getZoom(), focusedZoom), { animate: true });
                        marker.openPopup();
                    }
                };

                focusButton?.addEventListener('click', focusItem);
                mediaButton?.addEventListener('click', focusItem);
                cardsElement.appendChild(card);
            });

            updateStatus(items);
        }

        function applyFocus(items) {
            if (!focus) {
                return;
            }

            const focusedItem = items.find((item) =>
                item &&
                item.type === focus.type &&
                (String(item.slug) === String(focus.key) || String(item.id) === String(focus.key))
            );

            if (!focusedItem) {
                return;
            }

            map.setView([focusedItem.lat, focusedItem.lng], Math.max(map.getZoom(), focusedZoom), { animate: true });
            const marker = markersMap.get(keyFor(focusedItem));
            if (marker && shouldOpen) {
                marker.openPopup();
            }
        }

        function buildUrl() {
            const params = new URLSearchParams({
                tipo: 'all',
                limit: String(Number.isFinite(config.requestLimit) ? config.requestLimit : 200),
            });

            if (currentQuery) {
                params.set('q', currentQuery);
            }

            if (currentCategory) {
                params.set('categoria', currentCategory);
            }

            if (!firstLoad && config.useBoundsAfterFirstLoad) {
                const bounds = map.getBounds();
                params.set('bbox', `${bounds.getWest()},${bounds.getSouth()},${bounds.getEast()},${bounds.getNorth()}`);
            }

            return `${config.apiFeed}?${params.toString()}`;
        }

        async function fetchFeed() {
            if (abortController) {
                abortController.abort();
            }

            abortController = new AbortController();

            const response = await fetch(buildUrl(), { signal: abortController.signal });
            const data = await response.json();
            const items = Array.isArray(data.items) ? data.items : [];

            syncMarkers(items);
            renderCards(items);
            applyFocus(items);

            if (firstLoad && items.length && config.fitToResultsOnFirstLoad && !focus && !hasCoords) {
                const bounds = items
                    .filter((item) => typeof item.lat === 'number' && typeof item.lng !== 'undefined' && typeof item.lng === 'number')
                    .map((item) => [item.lat, item.lng]);

                if (bounds.length) {
                    map.fitBounds(bounds, {
                        padding: Array.isArray(config.fitPadding) ? config.fitPadding : [40, 40],
                    });
                }
            }

            firstLoad = false;
        }

        const fetchFeedDebounced = debounce(() => {
            fetchFeed().catch((error) => {
                if (error?.name === 'AbortError') {
                    return;
                }

                clearMarkers();
                renderCards([]);
            });
        }, 220);

        if (searchInput) {
            searchInput.value = currentQuery;
            const syncSearchClearButton = () => {
                if (!searchClearButton) {
                    return;
                }

                searchClearButton.hidden = !currentQuery;
            };

            searchInput.addEventListener('input', debounce((event) => {
                currentQuery = event.target.value || '';
                syncSearchClearButton();
                fetchFeedDebounced();
            }));

            syncSearchClearButton();

            searchClearButton?.addEventListener('click', () => {
                currentQuery = '';
                searchInput.value = '';
                syncSearchClearButton();
                searchInput.focus();
                fetchFeedDebounced();
            });
        }

        if (filterButtons.length) {
            const updateFilterButtons = () => {
                filterButtons.forEach((button) => {
                    const isActive = (button.dataset.category || '') === currentCategory;
                    button.classList.toggle('site-chip-active', isActive);
                    button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });
            };

            filterButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    currentCategory = button.dataset.category || '';
                    updateFilterButtons();
                    fetchFeedDebounced();
                });
            });

            updateFilterButtons();
        }

        if (initialItems.length) {
            syncMarkers(initialItems);
            renderCards(initialItems);
        }

        const invalidateMapSize = debounce(() => {
            map.invalidateSize({ pan: false, animate: false });
            refreshMarkerSizes();
        }, 80);

        map.whenReady(() => {
            window.setTimeout(() => invalidateMapSize(), 120);
            fetchFeed().catch((error) => {
                if (error?.name === 'AbortError') {
                    return;
                }

                if (initialItems.length) {
                    return;
                }

                clearMarkers();
                renderCards([]);
            });
        });

        map.on('moveend', fetchFeedDebounced);

        if ('ResizeObserver' in window) {
            const observer = new ResizeObserver(() => invalidateMapSize());
            observer.observe(mapElement);
            observer.observe(root);
        }

        const syncResponsiveMapState = debounce(() => {
            applyPreviewInteractionMode();
            invalidateMapSize();
        }, 80);

        window.addEventListener('resize', invalidateMapSize, { passive: true });
        window.addEventListener('orientationchange', () => {
            window.setTimeout(() => invalidateMapSize(), 120);
        }, { passive: true });
    });
});
</script>
