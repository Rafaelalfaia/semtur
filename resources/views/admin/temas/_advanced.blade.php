@php
    $configJsonValue = old('config_json');

    if ($configJsonValue === null) {
        $configSource = $theme->config_json ?? null;
        $configJsonValue = is_array($configSource) && ! empty($configSource)
            ? json_encode($configSource, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : '';
    }
@endphp

<x-dashboard.section-card title="Configurações avançadas" subtitle="Área secundária para o contrato técnico complementar do tema.">
    <details class="group rounded-[22px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-4">
        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-medium text-[var(--ui-text-title)]">
            <span>Editar config_json manualmente</span>
            <span class="text-xs text-[var(--ui-text-soft)] group-open:hidden">Expandir</span>
            <span class="hidden text-xs text-[var(--ui-text-soft)] group-open:inline">Recolher</span>
        </summary>

        <div class="mt-4 space-y-3">
            <p class="text-sm text-[var(--ui-text-soft)]">
                Use apenas para ajustes do contrato técnico de <code>shell</code>, <code>site</code>, <code>auth</code>, <code>flags</code> e <code>notes</code>.
            </p>

            <textarea
                id="config_json"
                name="config_json"
                rows="10"
                class="ui-form-control font-mono text-xs"
                placeholder='{"shell":{"variant":"institutional","density":"comfortable"},"site":{"hero_variant":"default"},"auth":{"layout":"split"}}'
            >{{ $configJsonValue }}</textarea>

            @error('config_json')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>
    </details>
</x-dashboard.section-card>
