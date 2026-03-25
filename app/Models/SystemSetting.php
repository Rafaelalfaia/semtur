<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SystemSetting extends Model
{
    public const SCOPE_THEME_COLUMNS = [
        Theme::SCOPE_CONSOLE => 'active_console_theme_id',
        Theme::SCOPE_SITE => 'active_site_theme_id',
        Theme::SCOPE_AUTH => 'active_auth_theme_id',
    ];

    protected $fillable = [
        'active_theme_id',
        'active_console_theme_id',
        'active_site_theme_id',
        'active_auth_theme_id',
    ];

    public function activeTheme()
    {
        return $this->belongsTo(Theme::class, 'active_theme_id');
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1], []);
    }

    public function activeConsoleTheme()
    {
        return $this->belongsTo(Theme::class, 'active_console_theme_id');
    }

    public function activeSiteTheme()
    {
        return $this->belongsTo(Theme::class, 'active_site_theme_id');
    }

    public function activeAuthTheme()
    {
        return $this->belongsTo(Theme::class, 'active_auth_theme_id');
    }

    public static function scopeThemeColumn(string $scope): ?string
    {
        return self::SCOPE_THEME_COLUMNS[$scope] ?? null;
    }

    public function themeIdForScope(string $scope): ?int
    {
        $column = self::scopeThemeColumn($scope);

        if (! $column || ! $this->hasScopeThemeColumn($column)) {
            return $this->active_theme_id ? (int) $this->active_theme_id : null;
        }

        $scopedThemeId = $this->{$column} ?? null;

        return $scopedThemeId
            ? (int) $scopedThemeId
            : ($this->active_theme_id ? (int) $this->active_theme_id : null);
    }

    public function setThemeIdForScope(string $scope, ?int $themeId): void
    {
        $column = self::scopeThemeColumn($scope);

        if (! $column || ! $this->hasScopeThemeColumn($column)) {
            $this->active_theme_id = $themeId;

            return;
        }

        $this->{$column} = $themeId;

        if ($scope === Theme::SCOPE_CONSOLE) {
            $this->active_theme_id = $themeId;
        }
    }

    private function hasScopeThemeColumn(string $column): bool
    {
        static $columns = [];

        if (! array_key_exists($column, $columns)) {
            $columns[$column] = Schema::hasTable($this->getTable())
                && Schema::hasColumn($this->getTable(), $column);
        }

        return $columns[$column];
    }
}
