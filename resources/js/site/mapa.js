// window.MapaPage
window.MapaPage = function () {
    return {
        map: null,
        init() {
            this.map = L.map("map");
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                maxZoom: 19,
            }).addTo(this.map);
            this.map.setView([-3.203, -52.206], 13);
            this.carregar();
        },
        async carregar() {
            const r = await fetch("/api/mapa/feed?tipo=all&limit=200");
            const data = await r.json();
            const markers = data.markers ?? [];
            const bounds = [];
            markers.forEach((m) => {
                if (!m?.position) return;
                const { lat, lng } = m.position;
                if (typeof lat !== "number" || typeof lng !== "number") return;
                const slug = m.slug || "";
                const href =
                    m.type === "empresa"
                        ? `/empresa/${slug}`
                        : `/ponto/${slug}`;
                L.marker([lat, lng])
                    .addTo(this.map)
                    .bindPopup(
                        `<a href="${href}">${m.title || "Sem título"}</a>`
                    );
                bounds.push([lat, lng]);
            });
            if (bounds.length)
                this.map.fitBounds(bounds, { padding: [40, 40] });
        },
    };
};
