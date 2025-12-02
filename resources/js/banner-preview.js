// resources/js/simple-previews.js
(() => {
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }

    function init() {
        // desktop
        bindPreview({
            fileSel: 'input[name="imagem_desktop"]',
            imgSel: "#preview-desktop-img",
            posName: "pos_desktop",
        });

        // mobile
        bindPreview({
            fileSel: 'input[name="imagem_mobile"]',
            imgSel: "#preview-mobile-img",
            posName: "pos_mobile",
        });

        // banner simples (se usar)
        bindPreview({
            fileSel: 'input[name="imagem"]',
            imgSel: "#preview-banner-img",
            posName: "pos_banner",
        });
    }

    function bindPreview({ fileSel, imgSel, posName }) {
        const input = document.querySelector(fileSel);
        const img = document.querySelector(imgSel);
        if (!img) return;

        const form = input?.form || document.querySelector("form");
        if (!form) return;

        // hidden da posição (0..100). Criamos se não existir.
        let hidden = form.querySelector(`input[name="${posName}"]`);
        if (!hidden) {
            hidden = document.createElement("input");
            hidden.type = "hidden";
            hidden.name = posName;
            form.appendChild(hidden);
        }

        // Se já tem imagem carregada (edit), habilita pan
        if (img.src && !img.classList.contains("hidden")) {
            if (img.complete) enablePan(img, hidden);
            else img.onload = () => enablePan(img, hidden);
        }

        // Troca de arquivo → preview + pan
        input?.addEventListener("change", (e) => {
            const file = e.target.files?.[0];
            if (!file) return;
            if (img._lastURL) URL.revokeObjectURL(img._lastURL);
            img._lastURL = URL.createObjectURL(file);
            img.src = img._lastURL;
            img.classList.remove("hidden");

            if (img.complete) enablePan(img, hidden);
            else img.onload = () => enablePan(img, hidden);
        });
    }

    // ---------- Pan suave ajustando object-position + salva hidden ----------
    function enablePan(img, hidden) {
        const container = img.parentElement;
        if (!container || container.dataset.panBound === "1") return;
        container.dataset.panBound = "1";

        img.draggable = false;
        container.style.cursor = "grab";
        container.style.userSelect = "none";
        container.style.touchAction = "none"; // não rola a página ao arrastar

        // posição inicial 50/50
        setObjectPosition(img, 50, 50);
        hidden.value = "50,50";

        let dragging = false;
        let startX = 0,
            startY = 0;
        let startPos = { x: 50, y: 50 };
        let overflowX = 0,
            overflowY = 0;

        let rafPending = false;
        let nextPos = { x: 50, y: 50 };

        container.addEventListener(
            "pointerdown",
            (ev) => {
                if (!img.naturalWidth) return;
                ev.preventDefault();
                dragging = true;
                container.setPointerCapture(ev.pointerId);
                container.style.cursor = "grabbing";

                startX = ev.clientX;
                startY = ev.clientY;
                startPos = getObjectPositionPercent(img);

                // calcula “sobra” atual
                const rect = container.getBoundingClientRect();
                const scale = Math.max(
                    rect.width / img.naturalWidth,
                    rect.height / img.naturalHeight
                );
                const scaledW = img.naturalWidth * scale;
                const scaledH = img.naturalHeight * scale;
                overflowX = Math.max(0, scaledW - rect.width);
                overflowY = Math.max(0, scaledH - rect.height);
            },
            { passive: false }
        );

        container.addEventListener(
            "pointermove",
            (ev) => {
                if (!dragging) return;
                ev.preventDefault();

                const dx = ev.clientX - startX;
                const dy = ev.clientY - startY;

                let x = startPos.x,
                    y = startPos.y;
                if (overflowX > 0)
                    x = clamp(startPos.x + (dx * 100) / overflowX, 0, 100);
                if (overflowY > 0)
                    y = clamp(startPos.y + (dy * 100) / overflowY, 0, 100);

                nextPos.x = x;
                nextPos.y = y;

                if (!rafPending) {
                    rafPending = true;
                    requestAnimationFrame(() => {
                        setObjectPosition(img, nextPos.x, nextPos.y);
                        hidden.value = `${Math.round(nextPos.x * 10) / 10},${
                            Math.round(nextPos.y * 10) / 10
                        }`;
                        rafPending = false;
                    });
                }
            },
            { passive: false }
        );

        const stop = (ev) => {
            if (!dragging) return;
            dragging = false;
            try {
                container.releasePointerCapture(ev.pointerId);
            } catch {}
            container.style.cursor = "grab";
        };
        container.addEventListener("pointerup", stop);
        container.addEventListener("pointercancel", stop);
        // não paramos no pointerleave pra não “cortar” o gesto

        // duplo clique → centraliza
        container.addEventListener("dblclick", () => {
            setObjectPosition(img, 50, 50);
            hidden.value = "50,50";
        });
    }

    function setObjectPosition(img, x, y) {
        const rx = Math.round(x * 10) / 10;
        const ry = Math.round(y * 10) / 10;
        img.style.objectPosition = `${rx}% ${ry}%`;
    }

    function getObjectPositionPercent(img) {
        const val = (
            img.style.objectPosition ||
            getComputedStyle(img).objectPosition ||
            "50% 50%"
        ).trim();
        if (val.includes("center")) return { x: 50, y: 50 };
        const m = val.match(/([\d.]+)%\s+([\d.]+)%/);
        if (!m) return { x: 50, y: 50 };
        return { x: parseFloat(m[1]), y: parseFloat(m[2]) };
    }

    const clamp = (v, a, b) => Math.min(Math.max(v, a), b);
})();
