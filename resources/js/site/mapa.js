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
            const items = data.items ?? [];
            const bounds = [];

            items.forEach((m) => {
                if (typeof m?.lat !== "number" || typeof m?.lng !== "number") return;

                const slugOrId = m.slug || m.id;
                const href = m.type === "empresa"
                    ? `/empresa/${slugOrId}`
                    : `/ponto/${slugOrId}`;

                L.marker([m.lat, m.lng])
                    .addTo(this.map)
                    .bindPopup(
                        `<a href="${href}">${m.nome || "Sem título"}</a>`
                    );

                bounds.push([m.lat, m.lng]);
            });

            if (bounds.length) {
                this.map.fitBounds(bounds, { padding: [40, 40] });
            }
        },
    };
};
