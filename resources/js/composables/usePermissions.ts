import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { AppPageProps } from '@/types';

export function usePermissions() {
    const page = usePage<AppPageProps>();

    const user = computed(() => page.props.auth.user);
    const roles = computed(() => user.value?.roles || []);
    const permissions = computed(() => user.value?.permissions || []);

    const hasRole = (role: string | string[]): boolean => {
        if (!user.value) return false;

        const rolesToCheck = Array.isArray(role) ? role : [role];
        return rolesToCheck.some(r => roles.value.includes(r));
    };

    const hasPermission = (permission: string | string[]): boolean => {
        if (!user.value) return false;

        const permissionsToCheck = Array.isArray(permission) ? permission : [permission];
        return permissionsToCheck.some(p => permissions.value.includes(p));
    };

    const hasAnyRole = (roles: string[]): boolean => {
        return hasRole(roles);
    };

    const hasAllRoles = (rolesToCheck: string[]): boolean => {
        if (!user.value) return false;
        return rolesToCheck.every(r => roles.value.includes(r));
    };

    const hasAnyPermission = (permissionsToCheck: string[]): boolean => {
        return hasPermission(permissionsToCheck);
    };

    const hasAllPermissions = (permissionsToCheck: string[]): boolean => {
        if (!user.value) return false;
        return permissionsToCheck.every(p => permissions.value.includes(p));
    };

    const isSuperAdmin = computed(() => hasRole('super-admin'));
    const isAdmin = computed(() => hasRole(['super-admin', 'admin']));

    return {
        user,
        roles,
        permissions,
        hasRole,
        hasPermission,
        hasAnyRole,
        hasAllRoles,
        hasAnyPermission,
        hasAllPermissions,
        isSuperAdmin,
        isAdmin,
    };
}
