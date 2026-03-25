<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\Theme;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ThemeManager
{
    private ?array $activityLogColumns = null;

    public function __construct(
        private readonly ThemeResolver $resolver
    ) {
    }

    public function activate(Theme $theme): void
    {
        $this->activateForScope($theme, Theme::SCOPE_CONSOLE);
    }

    public function activateForScope(Theme $theme, string $scope): void
    {
        DB::transaction(function () use ($theme, $scope) {
            $setting = SystemSetting::current();
            $setting->setThemeIdForScope($scope, $theme->id);
            $setting->save();

            $this->logActivity($theme, Theme::LOG_ACTION_ACTIVATED, [
                'context' => $scope,
                'scopes' => $theme->normalizedScopes(),
            ]);
        });

        if ($scope === Theme::SCOPE_CONSOLE) {
            $this->clearPreview();
        }

        $this->resolver->forgetCache();
    }

    public function restoreDefault(): void
    {
        $this->restoreDefaultForScope(Theme::SCOPE_CONSOLE);
    }

    public function restoreDefaultForScope(string $scope): void
    {
        DB::transaction(function () use ($scope) {
            $setting = SystemSetting::current();
            $defaultTheme = Theme::query()
                ->where('is_default', true)
                ->orderByDesc('updated_at')
                ->get()
                ->first(fn (Theme $theme) => $theme->isAvailableFor($scope));

            $setting->setThemeIdForScope($scope, $defaultTheme?->id);
            $setting->save();

            if ($defaultTheme) {
                $this->logActivity($defaultTheme, Theme::LOG_ACTION_ACTIVATED, [
                    'context' => "restore_default:{$scope}",
                    'scopes' => $defaultTheme->normalizedScopes(),
                ]);
            }
        });

        if ($scope === Theme::SCOPE_CONSOLE) {
            $this->clearPreview();
        }

        $this->resolver->forgetCache();
    }

    public function archive(Theme $theme): void
    {
        DB::transaction(function () use ($theme) {
            $updates = [
                'status' => Theme::STATUS_ARQUIVADO,
            ];

            if (Schema::hasColumn('themes', 'updated_by')) {
                $updates['updated_by'] = auth()->id();
            }

            $theme->update($updates);

            $setting = SystemSetting::current();
            $replacementIds = [
                'legacy' => null,
                Theme::SCOPE_CONSOLE => null,
                Theme::SCOPE_SITE => null,
                Theme::SCOPE_AUTH => null,
            ];

            foreach ([Theme::SCOPE_CONSOLE, Theme::SCOPE_SITE, Theme::SCOPE_AUTH] as $scope) {
                $column = SystemSetting::scopeThemeColumn($scope);

                if ($column && (int) ($setting->{$column} ?? 0) === (int) $theme->id) {
                    $replacement = $this->defaultThemeForScope($scope, $theme->id);
                    $setting->setThemeIdForScope($scope, $replacement?->id);
                    $replacementIds[$scope] = $replacement?->id;
                }
            }

            if ((int) $setting->active_theme_id === (int) $theme->id) {
                $replacement = $this->defaultThemeForScope(Theme::SCOPE_CONSOLE, $theme->id);
                $setting->active_theme_id = $replacement?->id;
                $replacementIds['legacy'] = $replacement?->id;
            }

            $setting->save();

            $this->logActivity($theme, Theme::LOG_ACTION_ARCHIVED, [
                'replacement_theme_ids' => $replacementIds,
            ]);
        });

        if ((int) session(ThemeResolver::PREVIEW_SESSION_KEY) === (int) $theme->id) {
            $this->clearPreview();
        }

        $this->resolver->forgetCache();
    }

    public function setPreview(Theme $theme): void
    {
        session([ThemeResolver::PREVIEW_SESSION_KEY => $theme->id]);

        $this->logActivity($theme, Theme::LOG_ACTION_PREVIEW_STARTED, [
            'session_only' => true,
        ]);
    }

    public function clearPreview(): void
    {
        $previewId = (int) session(ThemeResolver::PREVIEW_SESSION_KEY);
        session()->forget(ThemeResolver::PREVIEW_SESSION_KEY);

        if ($previewId > 0) {
            $theme = Theme::query()->find($previewId);

            if ($theme) {
                $this->logActivity($theme, Theme::LOG_ACTION_PREVIEW_CLEARED, [
                    'session_only' => true,
                ]);
            }
        }
    }

    public function logActivity(Theme $theme, string $action, array $meta = []): void
    {
        if (! DB::getSchemaBuilder()->hasTable('theme_activity_logs')) {
            return;
        }

        $columns = $this->themeActivityLogColumns();
        $payload = [
            'theme_id' => $theme->id,
            'action' => $action,
            'created_at' => now(),
        ];

        $encodedMeta = empty($meta)
            ? null
            : json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (in_array('user_id', $columns, true)) {
            $payload['user_id'] = auth()->id();
        } elseif (in_array('performed_by', $columns, true)) {
            $payload['performed_by'] = auth()->id();
        }

        if (in_array('user_name_snapshot', $columns, true)) {
            $payload['user_name_snapshot'] = auth()->user()?->name;
        }

        if (in_array('metadata', $columns, true)) {
            $payload['metadata'] = $encodedMeta;
        } elseif (in_array('meta', $columns, true)) {
            $payload['meta'] = $encodedMeta;
        }

        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = now();
        }

        DB::table('theme_activity_logs')->insert(
            array_intersect_key($payload, array_flip($columns))
        );
    }

    private function themeActivityLogColumns(): array
    {
        return $this->activityLogColumns ??= DB::getSchemaBuilder()
            ->getColumnListing('theme_activity_logs');
    }

    private function defaultThemeForScope(string $scope, ?int $ignoreThemeId = null): ?Theme
    {
        return Theme::query()
            ->when($ignoreThemeId, fn ($query) => $query->whereKeyNot($ignoreThemeId))
            ->where('is_default', true)
            ->orderByDesc('updated_at')
            ->get()
            ->first(fn (Theme $theme) => $theme->isAvailableFor($scope));
    }
}
