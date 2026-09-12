<?php

namespace App\Support;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Every new tenant used to start with zero custom roles — the tenant's own
 * Admin had to create one before any other user could even be added (see
 * the "must create a custom role" banner in platform/users.blade.php and
 * roles/index.blade.php). This seeds a sensible starting role, "Agent",
 * covering the day-to-day CRM work a front-line sales rep needs on their own
 * records — leads, deals, contacts, companies, tasks, viewing orders and the
 * calendar — without delete/assign/manage-settings or anything
 * admin-level (users, roles, reports, campaigns, templates, master data,
 * org settings). It's a normal, editable role like any other the tenant
 * creates themselves — not protected like Admin — so they can rename,
 * reshape, or delete it freely.
 *
 * Must be called from inside PermissionTeam::run($tenantId, ...) (or with
 * PermissionTeam::set($tenantId) already active), same as the tenant's
 * Admin role creation right next to every call site of this — Spatie's
 * teams feature scopes the new Role row to whatever team is currently set.
 */
class DefaultTenantRoles
{
    private const AGENT_PERMISSIONS = [
        'leads.view', 'leads.create', 'leads.edit',
        'deals.view', 'deals.create', 'deals.edit',
        'companies.view', 'companies.create', 'companies.edit',
        'contacts.view', 'contacts.create', 'contacts.edit',
        'tasks.view', 'tasks.create', 'tasks.edit',
        'orders.view',
        'calendar.view',
    ];

    public static function seedAgentRole(): Role
    {
        $role = Role::findOrCreate('Agent', 'web');
        $role->syncPermissions(Permission::whereIn('name', self::AGENT_PERMISSIONS)->get());

        return $role;
    }
}
