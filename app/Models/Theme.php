<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Theme extends Model
{
    public const LOG_ACTION_CREATED = 'created';
    public const LOG_ACTION_UPDATED = 'updated';
    public const LOG_ACTION_PREVIEW_STARTED = 'preview_started';
    public const LOG_ACTION_PREVIEW_CLEARED = 'preview_cleared';
    public const LOG_ACTION_ACTIVATED = 'activated';
    public const LOG_ACTION_ARCHIVED = 'archived';

    protected $fillable = [
        'name',
        'slug',
        'base_theme',
        'type',
        'status',
        'description',
        'preview_image_path',
        'starts_at',
        'ends_at',
        'is_default',
        'tokens',
        'assets',
        'config_json',
        'application_scopes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_default' => 'boolean',
        'tokens' => 'array',
        'assets' => 'array',
        'config_json' => 'array',
    ];

    protected $appends = [
        'preview_image_url',
        'application_scopes',
        'application_scopes_label',
    ];

    public const STATUS_DISPONIVEL = 'disponivel';
    public const STATUS_RASCUNHO = 'rascunho';
    public const STATUS_ARQUIVADO = 'arquivado';

    public const SCOPE_GLOBAL = 'global';
    public const SCOPE_CONSOLE = 'console';
    public const SCOPE_SITE = 'site';
    public const SCOPE_AUTH = 'auth';

    public const STATUSES = [
        self::STATUS_DISPONIVEL,
        self::STATUS_RASCUNHO,
        self::STATUS_ARQUIVADO,
    ];

    public const SCOPES = [
        self::SCOPE_GLOBAL,
        self::SCOPE_CONSOLE,
        self::SCOPE_SITE,
        self::SCOPE_AUTH,
    ];

    public const DEFAULT_ASSETS = [
        'logo' => 'imagens/logosemtur.png',
        'login_background' => 'imagens/altamira.jpg',
        'hero_image' => 'imagens/altamira.jpg',
    ];

    public const BASE_THEMES = [
        'default' => 'Base institucional clara',
        'graphite' => 'Base graphite com contraste reforçado',
    ];

    public const TOKEN_DEFINITIONS = [
        'ui-app-bg' => 'Fundo externo',
        'ui-app-bg-soft' => 'Fundo externo suave',
        'ui-frame-bg' => 'Fundo do frame',
        'ui-frame-border' => 'Borda do frame',
        'ui-frame-shadow' => 'Sombra do frame',
        'ui-shell-bg' => 'Fundo do shell',
        'ui-shell-bg-2' => 'Fundo do shell secundário',
        'ui-shell-shadow' => 'Sombra do shell',
        'ui-shell-stroke' => 'Traço do shell',
        'ui-sidebar-bg' => 'Sidebar',
        'ui-sidebar-border' => 'Borda da sidebar',
        'ui-sidebar-text' => 'Texto da sidebar',
        'ui-sidebar-text-soft' => 'Texto suave da sidebar',
        'ui-sidebar-hover-bg' => 'Hover da sidebar',
        'ui-sidebar-active-bg' => 'Item ativo da sidebar',
        'ui-sidebar-active-text' => 'Texto ativo da sidebar',
        'sidebar_surface' => 'Superfície da sidebar',
        'sidebar_text' => 'Texto principal da sidebar',
        'sidebar_section_text' => 'Texto das seções da sidebar',
        'sidebar_item_bg' => 'Fundo do item da sidebar',
        'sidebar_item_text' => 'Texto do item da sidebar',
        'sidebar_item_icon' => 'Ícone do item da sidebar',
        'sidebar_item_hover_bg' => 'Hover do item da sidebar',
        'sidebar_item_hover_text' => 'Texto no hover da sidebar',
        'sidebar_item_active_bg' => 'Fundo do item ativo da sidebar',
        'sidebar_item_active_text' => 'Texto do item ativo da sidebar',
        'sidebar_item_active_icon' => 'Ícone do item ativo da sidebar',
        'ui-topbar-bg' => 'Topbar',
        'ui-topbar-border' => 'Borda da topbar',
        'ui-surface' => 'Superfície principal',
        'ui-surface-soft' => 'Superfície suave',
        'ui-surface-muted' => 'Superfície neutra',
        'ui-border' => 'Bordas',
        'ui-border-strong' => 'Bordas fortes',
        'ui-text' => 'Texto forte',
        'ui-text-soft' => 'Texto suave',
        'ui-text-faint' => 'Texto sutil',
        'ui-primary' => 'Primária',
        'ui-primary-strong' => 'Primária forte',
        'ui-primary-soft' => 'Primária suave',
        'ui-success' => 'Sucesso',
        'ui-success-soft' => 'Sucesso suave',
        'ui-warning' => 'Aviso',
        'ui-warning-soft' => 'Aviso suave',
        'ui-danger' => 'Perigo',
        'ui-danger-soft' => 'Perigo suave',
        'ui-state-published-bg' => 'Status publicado',
        'ui-state-draft-bg' => 'Status rascunho',
        'ui-state-archived-bg' => 'Status arquivado',
        'ui-focus-ring' => 'Anel de foco',
        'ui-hover-surface' => 'Hover de superfície',
        'ui-hero-start' => 'Hero início',
        'ui-hero-end' => 'Hero fim',
        'ui-hero-glow' => 'Brilho do hero',
        'ui-shadow-xs' => 'Sombra extra pequena',
        'ui-shadow-sm' => 'Sombra pequena',
        'ui-shadow-md' => 'Sombra média',
        'ui-shadow-lg' => 'Sombra grande',
        'ui-radius-sm' => 'Raio pequeno',
        'ui-radius-md' => 'Raio médio',
        'ui-radius-lg' => 'Raio grande',
        'ui-radius-xl' => 'Raio extra grande',
        'ui-spacing-card' => 'Espaçamento de card',
        'ui-spacing-section' => 'Espaçamento de seção',
    ];

    public const ASSET_DEFINITIONS = [
        'logo' => 'Logo institucional',
        'login_background' => 'Fundo do login',
        'hero_image' => 'Imagem hero institucional',
    ];

    public const CONFIG_SCHEMA = [
        'shell' => ['variant', 'density'],
        'site' => ['variant', 'hero_variant'],
        'auth' => ['variant', 'layout'],
        'flags' => ['use_gradient_hero', 'use_glass_topbar', 'emphasize_brand'],
        'notes' => ['internal'],
    ];

    public const CONFIG_DENSITIES = [
        'compact',
        'comfortable',
        'spacious',
    ];

    public const CONFIG_AUTH_LAYOUTS = [
        'split',
        'centered',
    ];

    public const CONFIG_SCOPE_KEY = 'scopes';
    public const LEGACY_CONFIG_SCOPE_KEY = 'application_scopes';

    public const STATUS_ALIASES = [
        'available' => self::STATUS_DISPONIVEL,
        'draft' => self::STATUS_RASCUNHO,
        'archived' => self::STATUS_ARQUIVADO,
    ];

    public const TOKEN_ALIASES = [
        'ui-primary' => ['button_primary_bg', 'brand_primary', 'accent'],
        'ui-primary-soft' => ['button_soft_bg', 'brand_secondary'],
        'ui-surface' => ['card_bg', 'surface'],
        'ui-surface-soft' => ['surface_alt'],
        'ui-text' => ['card_text', 'text_primary'],
        'ui-text-soft' => ['text_secondary'],
        'ui-border' => ['border'],
        'ui-success' => ['success'],
        'ui-warning' => ['warning'],
        'ui-danger' => ['danger'],
        'ui-sidebar-bg' => ['sidebar_bg'],
        'ui-sidebar-text' => ['sidebar_text'],
        'sidebar_surface' => ['ui-sidebar-bg', 'sidebar_bg'],
        'sidebar_text' => ['ui-sidebar-text', 'sidebar_text'],
        'sidebar_section_text' => ['ui-sidebar-text-soft', 'ui-sidebar-text'],
        'sidebar_item_text' => ['ui-sidebar-text', 'sidebar_text'],
        'sidebar_item_icon' => ['ui-sidebar-text-soft', 'ui-sidebar-text'],
        'sidebar_item_hover_bg' => ['ui-sidebar-hover-bg'],
        'sidebar_item_hover_text' => ['ui-sidebar-active-text', 'ui-sidebar-text'],
        'sidebar_item_active_bg' => ['ui-sidebar-active-bg'],
        'sidebar_item_active_text' => ['ui-sidebar-active-text', 'ui-sidebar-text'],
        'sidebar_item_active_icon' => ['ui-sidebar-active-text', 'ui-sidebar-text'],
        'ui-topbar-bg' => ['topbar_bg'],
        'ui-hero-start' => ['brand_primary'],
        'ui-hero-end' => ['accent'],
    ];

    public const ASSET_ALIASES = [
        'logo' => ['logo_primary', 'logo_dark'],
        'login_background' => ['auth_bg'],
        'hero_image' => ['hero_image'],
    ];

    public function scopeDisponiveis($query)
    {
        return $query->where('status', '<>', self::STATUS_ARQUIVADO);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getToken(string $key, mixed $default = null): mixed
    {
        return data_get($this->persistedTokens(), $key, $default);
    }

    public function getAssetPath(string $key): ?string
    {
        return data_get($this->persistedAssets(), $key);
    }

    public function getAssetUrl(string $key): ?string
    {
        $path = $this->getAssetPath($key);

        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        if (Str::startsWith($path, ['imagens/', 'images/'])) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }

    public function getPreviewImageUrlAttribute(): ?string
    {
        $path = $this->preview_image_path;

        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        if (Str::startsWith($path, ['imagens/', 'images/'])) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }

    protected function applicationScopes(): Attribute
    {
        return Attribute::make(
            get: fn (): array => $this->normalizedScopes(),
            set: function (?array $scopes): array {
                $normalized = $this->normalizeScopeValues($scopes);

                $mergedConfig = $this->mergeConfig([
                    self::CONFIG_SCOPE_KEY => $normalized,
                ]);

                $attributes = [
                    'config_json' => json_encode(
                        $mergedConfig,
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    ),
                ];

                if ($this->hasApplicationScopesColumn()) {
                    $attributes['application_scopes'] = json_encode(
                        $normalized,
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    );
                }

                return $attributes;
            },
        );
    }

    protected function applicationScopesLabel(): Attribute
    {
        return Attribute::get(function (): string {
            return collect($this->normalizedScopes())
                ->map(fn (string $scope) => self::scopeOptions()[$scope] ?? $scope)
                ->implode(', ');
        });
    }

    public function normalizedScopes(): array
    {
        $scopes = $this->normalizeScopeValues($this->rawApplicationScopes());

        if ($scopes === [self::SCOPE_GLOBAL]) {
            $scopes = $this->normalizeScopeValues(data_get($this->config_json ?? [], self::CONFIG_SCOPE_KEY));
        }

        if ($scopes === [self::SCOPE_GLOBAL]) {
            $scopes = $this->normalizeScopeValues(data_get($this->config_json ?? [], self::LEGACY_CONFIG_SCOPE_KEY));
        }

        return $scopes !== [] ? $scopes : [self::SCOPE_GLOBAL];
    }

    public function appliesTo(string $scope): bool
    {
        $scope = in_array($scope, self::SCOPES, true) ? $scope : self::SCOPE_GLOBAL;
        $scopes = $this->normalizedScopes();

        return in_array(self::SCOPE_GLOBAL, $scopes, true) || in_array($scope, $scopes, true);
    }

    public function isWithinSchedule(?\DateTimeInterface $now = null): bool
    {
        $now ??= now();

        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->lt($now)) {
            return false;
        }

        return true;
    }

    public function isExpired(?\DateTimeInterface $now = null): bool
    {
        $now ??= now();

        return (bool) ($this->ends_at && $this->ends_at->lt($now));
    }

    public function isAvailableFor(string $scope, ?\DateTimeInterface $now = null): bool
    {
        return $this->normalizedStatus() === self::STATUS_DISPONIVEL
            && $this->appliesTo($scope)
            && $this->isWithinSchedule($now);
    }

    public function normalizedStatus(): string
    {
        $status = is_string($this->status) ? trim($this->status) : null;

        return self::STATUS_ALIASES[$status] ?? $status ?? self::STATUS_RASCUNHO;
    }

    public static function scopeOptions(): array
    {
        return [
            self::SCOPE_GLOBAL => 'Global',
            self::SCOPE_CONSOLE => 'Console',
            self::SCOPE_SITE => 'Site',
            self::SCOPE_AUTH => 'Auth/Login',
        ];
    }

    public static function tokenDefinitions(): array
    {
        return self::TOKEN_DEFINITIONS;
    }

    public static function assetDefinitions(): array
    {
        return self::ASSET_DEFINITIONS;
    }

    public static function assetKeys(): array
    {
        return array_keys(self::ASSET_DEFINITIONS);
    }

    public static function baseThemes(): array
    {
        return self::BASE_THEMES;
    }

    public function resolvedConfig(): array
    {
        return is_array($this->config_json) ? $this->config_json : [];
    }

    public function rawTokens(): array
    {
        return is_array($this->getRawOriginal('tokens')) ? $this->getRawOriginal('tokens') : ($this->tokens ?? []);
    }

    public function persistedTokens(): array
    {
        $stored = is_array($this->tokens) ? $this->tokens : [];
        $resolved = [];

        foreach (self::tokenDefinitions() as $key => $label) {
            $value = $stored[$key] ?? null;

            if (! filled($value)) {
                foreach (self::TOKEN_ALIASES[$key] ?? [] as $legacyKey) {
                    if (filled($stored[$legacyKey] ?? null)) {
                        $value = $stored[$legacyKey];
                        break;
                    }
                }
            }

            if (filled($value)) {
                $resolved[$key] = $value;
            }
        }

        return $resolved;
    }

    public function persistedTokenValue(string $key): mixed
    {
        return data_get($this->persistedTokens(), $key);
    }

    public function hasPersistedTokenValue(string $key): bool
    {
        return filled($this->persistedTokenValue($key));
    }

    public function mergeManagedTokens(array $managedTokens): array
    {
        $stored = is_array($this->tokens) ? $this->tokens : [];

        foreach (self::tokenDefinitions() as $key => $label) {
            unset($stored[$key]);

            foreach (self::TOKEN_ALIASES[$key] ?? [] as $legacyKey) {
                unset($stored[$legacyKey]);
            }
        }

        foreach ($managedTokens as $key => $value) {
            if (array_key_exists($key, self::TOKEN_DEFINITIONS) && filled($value)) {
                $stored[$key] = trim((string) $value);
            }
        }

        return $stored;
    }

    public function persistedAssets(): array
    {
        $stored = is_array($this->assets) ? $this->assets : [];
        $resolved = [];

        foreach (self::assetDefinitions() as $key => $label) {
            $value = $stored[$key] ?? null;

            if (! filled($value)) {
                foreach (self::ASSET_ALIASES[$key] ?? [] as $legacyKey) {
                    if (filled($stored[$legacyKey] ?? null)) {
                        $value = $stored[$legacyKey];
                        break;
                    }
                }
            }

            if (filled($value)) {
                $resolved[$key] = $value;
            }
        }

        return $resolved;
    }

    public function persistedAssetPath(string $key): ?string
    {
        return data_get($this->persistedAssets(), $key);
    }

    public function hasPersistedAsset(string $key): bool
    {
        return filled($this->persistedAssetPath($key));
    }

    public function mergeManagedAssets(array $managedAssets): array
    {
        $stored = is_array($this->assets) ? $this->assets : [];

        foreach (self::assetDefinitions() as $key => $label) {
            unset($stored[$key]);

            foreach (self::ASSET_ALIASES[$key] ?? [] as $legacyKey) {
                unset($stored[$legacyKey]);
            }
        }

        foreach ($managedAssets as $key => $value) {
            if (array_key_exists($key, self::ASSET_DEFINITIONS) && filled($value)) {
                $stored[$key] = $value;
            }
        }

        return $stored;
    }

    public function configValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->resolvedConfig(), $key, $default);
    }

    public function mergedAssets(?Theme $fallbackTheme = null): array
    {
        $resolved = [];

        foreach (self::assetKeys() as $key) {
            $resolved[$key] = $this->getAssetUrl($key)
                ?: $fallbackTheme?->getAssetUrl($key)
                ?: asset(self::DEFAULT_ASSETS[$key]);
        }

        return $resolved;
    }

    public function resolvedTokens(): array
    {
        return collect($this->persistedTokens())
            ->filter(fn ($value, $key) => array_key_exists($key, self::TOKEN_DEFINITIONS) && filled($value))
            ->all();
    }

    private function normalizeScopeValues(?array $scopes): array
    {
        $normalized = collect($scopes ?? [])
            ->map(fn ($scope) => is_string($scope) ? trim($scope) : null)
            ->filter(fn ($scope) => in_array($scope, self::SCOPES, true))
            ->unique()
            ->values()
            ->all();

        return $normalized !== [] ? $normalized : [self::SCOPE_GLOBAL];
    }

    private function mergeConfig(array $overrides): array
    {
        $config = $this->resolvedConfig();

        foreach ($overrides as $key => $value) {
            if ($value === null) {
                unset($config[$key]);
                continue;
            }

            $config[$key] = $value;
        }

        return $config;
    }

    private function rawApplicationScopes(): ?array
    {
        if (! $this->hasApplicationScopesColumn()) {
            return null;
        }

        $value = $this->attributes['application_scopes'] ?? null;

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }

    private function hasApplicationScopesColumn(): bool
    {
        static $hasColumn;

        return $hasColumn ??= Schema::hasTable($this->getTable())
            && Schema::hasColumn($this->getTable(), 'application_scopes');
    }

    public static function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base) ?: 'tema';
        $candidate = $slug;
        $counter = 2;

        while (
            static::query()
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = "{$slug}-{$counter}";
            $counter++;
        }

        return $candidate;
    }
}
