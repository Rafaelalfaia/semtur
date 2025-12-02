// vite.config.js
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import { VitePWA } from "vite-plugin-pwa";

export default defineConfig(({ command }) => {
    const isBuild = command === "build";

    return {
        plugins: [
            laravel({
                input: [
                    "resources/css/app.css",
                    "resources/js/app.js",
                    "resources/js/simple-previews.js",
                ],
                refresh: true,
            }),

            VitePWA({
                // Atualização automática do SW
                registerType: "autoUpdate",
                injectRegister: "auto",

                includeAssets: [
                    "icons/favicon.ico",
                    "icons/apple-touch-icon.png",
                    "icons/mask-icon.svg",
                ],
                manifest: {
                    // >>> Branding ajustado
                    name: "VisitAltamira",
                    short_name: "VisitAltamira",
                    description:
                        "VisitAltamira — guia turístico com experiências, empresas e pontos de interesse de Altamira.",
                    lang: "pt-BR",
                    start_url: "/",
                    scope: "/",
                    display: "standalone",
                    background_color: "#0e1b12",
                    theme_color: "#0e1b12", // ou mantenha "#16a34a" se preferir destaque esverdeado
                    icons: [
                        {
                            src: "icons/pwa-192.png",
                            sizes: "192x192",
                            type: "image/png",
                        },
                        {
                            src: "icons/pwa-512.png",
                            sizes: "512x512",
                            type: "image/png",
                        },
                        {
                            src: "icons/pwa-512-maskable.png",
                            sizes: "512x512",
                            type: "image/png",
                            purpose: "maskable",
                        },
                    ],
                },

                /**
                 * Política segura:
                 * - Sem precache amplo de HTML/CSS/JS (deixa o Vite cuidar com hashes)
                 * - Runtime cache só para imagens e (opcional) tiles do Google Maps
                 * - /api/* sem cache
                 */
                workbox: {
                    // Não usar navigateFallback sem página offline
                    // navigateFallback: "/offline",

                    cleanupOutdatedCaches: true,
                    clientsClaim: true,
                    skipWaiting: true,

                    // Regras unificadas (NÃO repetir runtimeCaching)
                    runtimeCaching: [
                        // Imagens do app e CDNs (img, jpeg, png, webp, svg, avif)
                        {
                            urlPattern: ({ request }) =>
                                request.destination === "image",
                            handler: "CacheFirst",
                            options: {
                                cacheName: "images",
                                expiration: {
                                    maxEntries: 150,
                                    maxAgeSeconds: 60 * 60 * 24 * 30, // 30 dias
                                },
                            },
                        },

                        // (Opcional) Tiles do Google Maps
                        {
                            urlPattern: ({ url }) =>
                                url.origin.includes("googleapis.com") ||
                                url.origin.includes("gstatic.com") ||
                                url.hostname.endsWith(".google.com"),
                            handler: "StaleWhileRevalidate",
                            options: { cacheName: "google-maps" },
                        },

                        // API da própria aplicação: nunca cachear
                        {
                            urlPattern: /^\/api\//,
                            handler: "NetworkOnly",
                            options: { cacheName: "api-bypass" },
                        },
                    ],

                    // Não interceptar navegações de /api/*
                    navigateFallbackDenylist: [new RegExp("^/api/")],
                },

                /**
                 * Em dev, mantenha desligado para não atrapalhar o HMR.
                 */
                devOptions: {
                    enabled: false,
                    selfDestroying: true,
                },
            }),
        ],
    };
});
