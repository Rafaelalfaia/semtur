(() => {
    "use strict";

    const qs = (s) => document.querySelector(s);
    const clamp = (v, a, b) =>
        Math.min(Math.max(Number.isFinite(v) ? v : 50, a), b);
    const to1 = (n) => (Math.round(n * 10) / 10).toFixed(1);

    const MAP = {
        // Se você também usa desktop/mobile em outras telas, deixe estes:
        "#preview-desktop-img": {
            x: "#pos_desktop_x",
            y: "#pos_desktop_y",
            combined: "#pos_desktop",
            file: 'input[name="imagem_desktop"]',
        },
        "#preview-mobile-img": {
            x: "#pos_mobile_x",
            y: "#pos_mobile_y",
            combined: "#pos_mobile",
            file: 'input[name="imagem_mobile"]',
        },

        // Banner desta tela:
        "#preview-banner-img": {
            x: "#pos_banner_x",
            y: "#pos_banner_y",
            combined: "#pos_banner",
            file: 'input[name="imagem"]',
        },
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init, { once: true });
    } else init();

    function init() {
        // 1) Input[file] -> preview
        Object.entries(MAP).forEach(([imgSel, cfg]) => {
            const input = qs(cfg.file);
            const img = qs(imgSel);
            if (!input || !img) return;
            let lastURL = null;

            input.addEventListener("change", (e) => {
                const f = e.target.files?.[0];
                if (!f) return;
                if (lastURL) URL.revokeObjectURL(lastURL);
                lastURL = URL.createObjectURL(f);
                img.src = lastURL;
                img.classList.remove("hidden");

                whenLoaded(img, () => {
                    if (img.dataset.panBound === "1") {
                        const initPos = readInitial(img, cfg);
                        setObjPos(img, initPos.x, initPos.y);
                        writeXY(cfg, initPos.x, initPos.y);
                        writeCombined(cfg, initPos.x, initPos.y);
                    } else {
                        enablePan(img, cfg);
                    }
                });
            });
        });

        // 2) Edição (sem trocar arquivo): garantir pan e, se preciso, usar original
        Object.entries(MAP).forEach(([imgSel, cfg]) => {
            const img = qs(imgSel);
            if (!img || !img.src || img.classList.contains("hidden")) return;
            whenLoaded(img, () => {
                if (!hasOverflow(img)) {
                    const orig = img.dataset.srcOriginal;
                    if (orig && orig !== img.src) {
                        img.addEventListener(
                            "load",
                            () => enablePan(img, cfg),
                            { once: true }
                        );
                        img.src = orig;
                        img.classList.remove("hidden");
                        return;
                    }
                }
                enablePan(img, cfg);
            });
        });

        // 3) Normaliza antes de enviar
        const form = document.querySelector("form");
        if (form) {
            form.addEventListener("submit", () => {
                Object.values(MAP).forEach((cfg) => {
                    const ix = qs(cfg.x),
                        iy = qs(cfg.y),
                        ic = qs(cfg.combined);
                    if (!ix || !iy) return;
                    const x = clamp(parseFloat(ix.value), 0, 100);
                    const y = clamp(parseFloat(iy.value), 0, 100);
                    ix.value = to1(x);
                    iy.value = to1(y);
                    if (ic) ic.value = `${to1(x)},${to1(y)}`; // "x,y" sem %
                });
            });
        }
    }

    function whenLoaded(img, cb) {
        if (img.complete && img.naturalWidth) cb();
        else img.addEventListener("load", cb, { once: true });
    }

    function hasOverflow(img) {
        const c = img.parentElement;
        if (!c || !img.naturalWidth || !img.naturalHeight) return false;
        const r = c.getBoundingClientRect();
        const scale = Math.max(
            r.width / img.naturalWidth,
            r.height / img.naturalHeight
        );
        const scaledW = img.naturalWidth * scale;
        const scaledH = img.naturalHeight * scale;
        return scaledW > r.width + 0.5 || scaledH > r.height + 0.5;
    }

    function enablePan(img, cfg) {
        if (img.dataset.panBound === "1") return;
        img.dataset.panBound = "1";

        const container = img.parentElement;
        if (!container) return;

        // UX
        img.draggable = false;
        img.style.touchAction = "none";
        container.style.userSelect = "none";
        container.style.cursor = "grab";

        // posição inicial
        const init = readInitial(img, cfg);
        setObjPos(img, init.x, init.y);
        writeXY(cfg, init.x, init.y);
        writeCombined(cfg, init.x, init.y);

        let dragging = false,
            startX = 0,
            startY = 0;
        let start = { x: init.x, y: init.y };
        let overflowX = 0,
            overflowY = 0;
        let raf = false,
            next = { x: init.x, y: init.y };

        const recalc = () => {
            const c = container.getBoundingClientRect();
            const nw = img.naturalWidth,
                nh = img.naturalHeight;
            if (!nw || !nh) {
                overflowX = 0;
                overflowY = 0;
                return;
            }
            const scale = Math.max(c.width / nw, c.height / nh);
            overflowX = Math.max(0, nw * scale - c.width);
            overflowY = Math.max(0, nh * scale - c.height);
        };
        recalc();
        try {
            new ResizeObserver(recalc).observe(container);
        } catch {}

        img.addEventListener(
            "pointerdown",
            (ev) => {
                if (!img.naturalWidth || !img.naturalHeight) return;
                ev.preventDefault();
                dragging = true;
                try {
                    img.setPointerCapture(ev.pointerId);
                } catch {}
                container.style.cursor = "grabbing";
                startX = ev.clientX;
                startY = ev.clientY;
                start = getObjPos(img);
                recalc();
            },
            { passive: false }
        );

        img.addEventListener(
            "pointermove",
            (ev) => {
                if (!dragging) return;
                ev.preventDefault();
                const dx = ev.clientX - startX;
                const dy = ev.clientY - startY;

                let x = start.x,
                    y = start.y;
                if (overflowX > 0)
                    x = clamp(start.x + (dx * 100) / overflowX, 0, 100);
                if (overflowY > 0)
                    y = clamp(start.y + (dy * 100) / overflowY, 0, 100);

                next.x = x;
                next.y = y;

                if (!raf) {
                    raf = true;
                    requestAnimationFrame(() => {
                        setObjPos(img, next.x, next.y);
                        writeXY(cfg, next.x, next.y);
                        writeCombined(cfg, next.x, next.y);
                        raf = false;
                    });
                }
            },
            { passive: false }
        );

        const stop = (ev) => {
            if (!dragging) return;
            dragging = false;
            try {
                img.releasePointerCapture(ev.pointerId);
            } catch {}
            container.style.cursor = "grab";
            writeXY(cfg, next.x, next.y);
            writeCombined(cfg, next.x, next.y);
        };
        img.addEventListener("pointerup", stop, { passive: false });
        img.addEventListener("pointercancel", stop, { passive: false });

        img.addEventListener("dblclick", () => {
            setObjPos(img, 50, 50);
            writeXY(cfg, 50, 50);
            writeCombined(cfg, 50, 50);
        });
    }

    // ---- helpers de IO/posição ----
    function readInitial(img, cfg) {
        const ix = qs(cfg.x),
            iy = qs(cfg.y),
            ic = qs(cfg.combined);
        const xv = ix ? parseFloat(ix.value) : NaN;
        const yv = iy ? parseFloat(iy.value) : NaN;
        if (Number.isFinite(xv) && Number.isFinite(yv))
            return { x: clamp(xv, 0, 100), y: clamp(yv, 0, 100) };

        if (ic && ic.value) {
            const p = parseCombined(ic.value);
            if (p) return p;
        }
        const s = getObjPos(img);
        if (Number.isFinite(s.x) && Number.isFinite(s.y))
            return { x: clamp(s.x, 0, 100), y: clamp(s.y, 0, 100) };
        return { x: 50, y: 50 };
    }

    // aceita "12.3,45.6" | "12.3 45.6" | "12.3 45.6%"
    function parseCombined(raw) {
        const t = String(raw).trim();
        let m = t.match(/^\s*([\d.]+)\s*,\s*([\d.]+)\s*%?\s*$/);
        if (m)
            return {
                x: clamp(parseFloat(m[1]), 0, 100),
                y: clamp(parseFloat(m[2]), 0, 100),
            };
        m = t.match(/^\s*([\d.]+)\s+([\d.]+)\s*%?\s*$/);
        if (m)
            return {
                x: clamp(parseFloat(m[1]), 0, 100),
                y: clamp(parseFloat(m[2]), 0, 100),
            };
        return null;
    }

    function setObjPos(img, x, y) {
        const rx = Math.round(clamp(x, 0, 100) * 10) / 10;
        const ry = Math.round(clamp(y, 0, 100) * 10) / 10;
        img.style.objectPosition = `${rx}% ${ry}%`;
    }

    function getObjPos(img) {
        const raw = (
            img.style.objectPosition ||
            getComputedStyle(img).objectPosition ||
            "50% 50%"
        ).trim();
        if (raw.includes("center")) return { x: 50, y: 50 };
        const m = raw.match(/([\d.]+)%\s+([\d.]+)%/);
        if (!m) return { x: 50, y: 50 };
        return { x: parseFloat(m[1]), y: parseFloat(m[2]) };
    }

    function writeXY(cfg, x, y) {
        const ix = qs(cfg.x),
            iy = qs(cfg.y);
        if (ix) ix.value = to1(clamp(x, 0, 100));
        if (iy) iy.value = to1(clamp(y, 0, 100));
    }

    function writeCombined(cfg, x, y) {
        const ic = qs(cfg.combined);
        if (!ic) return;
        ic.value = `${to1(clamp(x, 0, 100))},${to1(clamp(y, 0, 100))}`;
    }
})();
