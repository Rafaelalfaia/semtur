import "./bootstrap";

import Alpine from "alpinejs";

window.Alpine = Alpine;

import { registerSW } from "virtual:pwa-register";
registerSW({ immediate: true });

Alpine.start();
