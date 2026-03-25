@foreach($tokenGroups as $group)
    @if(! empty($group['fields']))
        <x-dashboard.section-card :title="$group['title']" :subtitle="$group['subtitle']">
            <div class="grid gap-4 lg:grid-cols-2">
                @foreach($group['fields'] as $field)
                    @php
                        $savedValue = $theme->persistedTokenValue($field['key']);
                        $value = old("tokens.{$field['key']}", $savedValue);
                        $colorPreview = preg_match('/^#([a-fA-F0-9]{6})$/', (string) $value) ? $value : '#2f7d57';
                    @endphp
                    <div class="rounded-[22px] border border-[var(--ui-border)] bg-[var(--ui-surface-soft)] p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <label class="ui-form-label mb-1 block" for="token_{{ $field['key'] }}">{{ $field['label'] }}</label>
                                <div class="text-xs text-[var(--ui-text-soft)]">{{ $field['key'] }}</div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="ui-badge ui-badge-neutral">{{ $theme->hasPersistedTokenValue($field['key']) ? 'Salvo' : 'Fallback' }}</span>
                                @if($field['uses_color_picker'])
                                    <span id="token_{{ $field['key'] }}_swatch" class="mt-1 h-10 w-10 rounded-[14px] border border-[var(--ui-border)]" style="background: {{ $colorPreview }}"></span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-2 text-xs text-[var(--ui-text-soft)]">
                            @if($theme->hasPersistedTokenValue($field['key']))
                                Valor salvo: <span class="font-medium text-[var(--ui-text-title)]">{{ $savedValue }}</span>
                            @else
                                Sem valor salvo. Se ficar vazio, o sistema continua usando o fallback institucional.
                            @endif
                        </div>

                        <div class="mt-3 flex items-center gap-3">
                            <input
                                id="token_{{ $field['key'] }}"
                                name="tokens[{{ $field['key'] }}]"
                                type="text"
                                value="{{ $value }}"
                                class="ui-form-control"
                                placeholder="{{ $field['placeholder'] }}"
                                @if($field['uses_color_picker']) oninput="document.getElementById('token_{{ $field['key'] }}_swatch').style.background = this.value || '#2f7d57'" @endif
                            >

                            @if($field['uses_color_picker'])
                                <input
                                    type="color"
                                    value="{{ $colorPreview }}"
                                    class="h-11 w-14 rounded-[16px] border border-[var(--ui-border)] bg-[var(--ui-surface)] p-1"
                                    oninput="document.getElementById('token_{{ $field['key'] }}').value = this.value; document.getElementById('token_{{ $field['key'] }}_swatch').style.background = this.value"
                                >
                            @endif
                        </div>

                        <p class="mt-2 text-xs text-[var(--ui-text-soft)]">Apague o conteúdo para remover a personalização deste token e voltar ao fallback do sistema.</p>
                        @error("tokens.{$field['key']}")<p class="ui-form-error mt-2">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>
        </x-dashboard.section-card>
    @endif
@endforeach
