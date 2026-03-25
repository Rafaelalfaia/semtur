@foreach($assetGroups as $group)
    @if(! empty($group['fields']))
        <x-dashboard.section-card :title="$group['title']" :subtitle="$group['subtitle']">
            <div class="grid gap-4 lg:grid-cols-2">
                @foreach($group['fields'] as $field)
                    @php
                        $currentAsset = $theme->persistedAssetPath($field['key']);
                        $previewUrl = $currentAsset ? theme_asset($field['key'], $theme) : theme_asset($field['key']);
                    @endphp
                    <div class="rounded-[22px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <label class="ui-form-label mb-1 block" for="{{ $field['key'] }}">{{ $field['label'] }}</label>
                                <div class="text-xs text-[var(--ui-text-soft)]">{{ $field['key'] }}</div>
                            </div>

                            <span class="ui-badge ui-badge-neutral">{{ $currentAsset ? 'Personalizado' : 'Fallback' }}</span>
                        </div>

                        <div class="mt-2 text-xs text-[var(--ui-text-soft)]">
                            @if($currentAsset)
                                Asset salvo: <span class="font-medium text-[var(--ui-text-title)]">{{ $currentAsset }}</span>
                            @else
                                Nenhum asset salvo. A prévia abaixo mostra o fallback institucional atual.
                            @endif
                        </div>

                        <div class="mt-3 overflow-hidden rounded-[18px] border border-[var(--ui-border)] bg-[var(--ui-surface)]">
                            <div class="flex h-40 items-center justify-center">
                                <img src="{{ $previewUrl }}" alt="{{ $field['label'] }}" class="h-full w-full object-cover">
                            </div>
                        </div>

                        <div class="mt-3">
                            <input id="{{ $field['key'] }}" name="{{ $field['key'] }}" type="file" accept="image/*" class="ui-form-control">
                            @error($field['key'])<p class="ui-form-error mt-2">{{ $message }}</p>@enderror
                        </div>

                        @if($currentAsset)
                            <label class="mt-3 inline-flex items-center gap-2 text-sm text-[var(--ui-text-soft)]">
                                <input type="hidden" name="remove_{{ $field['key'] }}" value="0">
                                <input type="checkbox" name="remove_{{ $field['key'] }}" value="1">
                                Remover asset atual
                            </label>
                        @else
                            <input type="hidden" name="remove_{{ $field['key'] }}" value="0">
                        @endif

                        <p class="mt-2 text-xs text-[var(--ui-text-soft)]">Remover o asset salvo devolve automaticamente este campo ao fallback institucional.</p>
                    </div>
                @endforeach
            </div>
        </x-dashboard.section-card>
    @endif
@endforeach
