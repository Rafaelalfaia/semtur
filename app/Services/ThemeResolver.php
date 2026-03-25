<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ThemeResolver
{
    public const PREVIEW_SESSION_KEY = 'console.theme.preview_id';

    public function payload(?User $user = null, ?string $context = null): array
    {
        $context ??= $this->contextFromRequest($user);
        $activeTheme = $this->activeTheme($context);
        $previewTheme = $this->previewThemeFor($user, $context);
        $theme = $previewTheme ?? $activeTheme;
        $hasCustomConsoleTheme = $context === Theme::SCOPE_CONSOLE
            && $theme
            && $this->hasCustomConsoleTheme($theme);

        return [
            'context' => $context,
            'theme' => $theme,
            'activeTheme' => $activeTheme,
            'previewTheme' => $previewTheme,
            'isPreview' => $previewTheme && (! $activeTheme || $previewTheme->id !== $activeTheme->id),
            'dataTheme' => $this->dataTheme($theme),
            'cssVariables' => $this->cssVariables($theme),
            'assets' => $this->resolvedAssets($theme, $activeTheme),
            'hasCustomConsoleTheme' => $hasCustomConsoleTheme,
        ];
    }

    public function activeTheme(?string $context = null): ?Theme
    {
        if (! $this->themeTablesExist()) {
            return null;
        }

        $context ??= $this->contextFromRequest();
        $cacheKey = "themes.active.{$context}";

        return Cache::remember($cacheKey, 60, function () use ($context) {
            $setting = SystemSetting::current()->load([
                'activeTheme',
                'activeConsoleTheme',
                'activeSiteTheme',
                'activeAuthTheme',
            ]);

            foreach ($this->activeThemeCandidatesForContext($setting, $context) as $activeTheme) {
                if ($activeTheme && $activeTheme->isAvailableFor($context)) {
                    return $activeTheme;
                }
            }

            $defaultTheme = Theme::query()
                ->where('is_default', true)
                ->orderByDesc('updated_at')
                ->get()
                ->first(fn (Theme $theme) => $theme->isAvailableFor($context));

            if ($defaultTheme) {
                return $defaultTheme;
            }

            return null;
        });
    }

    public function previewThemeFor(?User $user = null, ?string $context = null): ?Theme
    {
        $user ??= auth()->user();
        $context ??= $this->contextFromRequest($user);

        if (! $user || ! $user->can('themes.preview') || ! $this->themeTablesExist()) {
            return null;
        }

        $previewId = (int) session(self::PREVIEW_SESSION_KEY);

        if ($previewId <= 0) {
            return null;
        }

        return Theme::query()
            ->whereKey($previewId)
            ->get()
            ->first(fn (Theme $theme) => $theme->normalizedStatus() !== Theme::STATUS_ARQUIVADO && $theme->appliesTo($context));
    }

    public function dataTheme(?Theme $theme = null): string
    {
        $baseTheme = $theme?->base_theme ?: 'default';

        return Str::slug($baseTheme) ?: 'default';
    }

    public function cssVariables(?Theme $theme = null): array
    {
        return collect($theme?->resolvedTokens() ?? [])
            ->mapWithKeys(function ($value, $key) {
                $variable = match ($key) {
                    'sidebar_surface' => '--ui-sidebar-surface',
                    'sidebar_text' => '--ui-sidebar-text-strong',
                    'sidebar_section_text' => '--ui-sidebar-section-text',
                    'sidebar_item_bg' => '--ui-sidebar-item-bg',
                    'sidebar_item_text' => '--ui-sidebar-item-text',
                    'sidebar_item_icon' => '--ui-sidebar-item-icon',
                    'sidebar_item_hover_bg' => '--ui-sidebar-item-hover-bg',
                    'sidebar_item_hover_text' => '--ui-sidebar-item-hover-text',
                    'sidebar_item_active_bg' => '--ui-sidebar-item-active-bg',
                    'sidebar_item_active_text' => '--ui-sidebar-item-active-text',
                    'sidebar_item_active_icon' => '--ui-sidebar-item-active-icon',
                    default => "--{$key}",
                };

                return [$variable => $value];
            })
            ->all();
    }

    public function resolvedAssets(?Theme $theme = null, ?Theme $fallbackTheme = null): array
    {
        $assets = [];

        foreach (Theme::assetKeys() as $key) {
            $assets[$key] = $theme?->mergedAssets($fallbackTheme)[$key]
                ?? $fallbackTheme?->mergedAssets()[$key]
                ?? asset(Theme::DEFAULT_ASSETS[$key]);
        }

        return $assets;
    }

    public function asset(string $key, ?Theme $theme = null, ?string $context = null): string
    {
        $payload = $this->payload(auth()->user(), $context);
        $active = $payload['activeTheme'] ?? null;
        $fallbackKey = array_key_exists($key, Theme::DEFAULT_ASSETS) ? $key : 'logo';

        return $this->resolvedAssets($theme ?? ($payload['theme'] ?? null), $active)[$key]
            ?? asset(Theme::DEFAULT_ASSETS[$fallbackKey]);
    }

    public function forgetCache(): void
    {
        foreach (Theme::SCOPES as $context) {
            Cache::forget("themes.active.{$context}");
        }
    }

    public function contextFromRequest(?User $user = null): string
    {
        $user ??= auth()->user();

        if (Request::routeIs('login', 'register', 'password.*', 'verification.*')) {
            return Theme::SCOPE_AUTH;
        }

        if (Request::is('login', 'register', 'forgot-password', 'reset-password*')) {
            return Theme::SCOPE_AUTH;
        }

        if (Request::is('admin*', 'coordenador*', 'tecnico*')) {
            return Theme::SCOPE_CONSOLE;
        }

        if ($user && $user->hasAnyRole(['Admin', 'Coordenador', 'Tecnico']) && Request::is('profile')) {
            return Theme::SCOPE_CONSOLE;
        }

        return Theme::SCOPE_SITE;
    }

    private function themeTablesExist(): bool
    {
        return Schema::hasTable('themes') && Schema::hasTable('system_settings');
    }

    private function activeThemeCandidatesForContext(SystemSetting $setting, string $context): array
    {
        $candidates = [];
        $scopedRelation = match ($context) {
            Theme::SCOPE_CONSOLE => 'activeConsoleTheme',
            Theme::SCOPE_SITE => 'activeSiteTheme',
            Theme::SCOPE_AUTH => 'activeAuthTheme',
            default => null,
        };

        if ($scopedRelation) {
            $scopedTheme = $setting->getRelationValue($scopedRelation);

            if ($scopedTheme) {
                $candidates[] = $scopedTheme;
            }
        }

        if ($setting->activeTheme) {
            $candidates[] = $setting->activeTheme;
        }

        return collect($candidates)
            ->filter()
            ->unique(fn (Theme $theme) => $theme->id)
            ->values()
            ->all();
    }

    private function hasCustomConsoleTheme(Theme $theme): bool
    {
        return $theme->appliesTo(Theme::SCOPE_CONSOLE)
            && (
                $theme->persistedTokens() !== []
                || $theme->persistedAssets() !== []
                || $theme->base_theme !== 'default'
            );
    }
}
