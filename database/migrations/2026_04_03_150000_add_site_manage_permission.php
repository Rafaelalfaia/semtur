<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $permissionsTable = config('permission.table_names.permissions', 'permissions');
        $rolesTable = config('permission.table_names.roles', 'roles');
        $roleHasPermissionsTable = config('permission.table_names.role_has_permissions', 'role_has_permissions');

        if (! Schema::hasTable($permissionsTable) || ! Schema::hasTable($rolesTable) || ! Schema::hasTable($roleHasPermissionsTable)) {
            return;
        }

        $guard = config('auth.defaults.guard', 'web');

        $permissionId = DB::table($permissionsTable)
            ->where('name', 'site.manage')
            ->where('guard_name', $guard)
            ->value('id');

        if (! $permissionId) {
            $permissionId = DB::table($permissionsTable)->insertGetId([
                'name' => 'site.manage',
                'guard_name' => $guard,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $adminRoleId = DB::table($rolesTable)
            ->where('name', 'Admin')
            ->where('guard_name', $guard)
            ->value('id');

        if (! $adminRoleId) {
            return;
        }

        $exists = DB::table($roleHasPermissionsTable)
            ->where('permission_id', $permissionId)
            ->where('role_id', $adminRoleId)
            ->exists();

        if (! $exists) {
            DB::table($roleHasPermissionsTable)->insert([
                'permission_id' => $permissionId,
                'role_id' => $adminRoleId,
            ]);
        }
    }

    public function down(): void
    {
        $permissionsTable = config('permission.table_names.permissions', 'permissions');
        $roleHasPermissionsTable = config('permission.table_names.role_has_permissions', 'role_has_permissions');

        if (! Schema::hasTable($permissionsTable) || ! Schema::hasTable($roleHasPermissionsTable)) {
            return;
        }

        $guard = config('auth.defaults.guard', 'web');

        $permissionId = DB::table($permissionsTable)
            ->where('name', 'site.manage')
            ->where('guard_name', $guard)
            ->value('id');

        if (! $permissionId) {
            return;
        }

        DB::table($roleHasPermissionsTable)
            ->where('permission_id', $permissionId)
            ->delete();

        DB::table($permissionsTable)
            ->where('id', $permissionId)
            ->delete();
    }
};
