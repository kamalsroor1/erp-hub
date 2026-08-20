import { usePage } from '@inertiajs/vue3';

/**
 * Check if the authenticated user has a specific permission
 */
export function can(permission) {
    const page = usePage();
    const user = page.props.auth?.user;

    if (!user) return false;

    // Admin has full access to everything
    const roles = Array.isArray(user.roles) ? user.roles : [];
    if (roles.includes('admin') || roles.includes('Super Admin')) {
        return true;
    }

    const permissions = Array.isArray(user.permissions) ? user.permissions : [];
    return permissions.includes(permission);
}

/**
 * Check if the user has a specific role
 */
export function hasRole(role) {
    const page = usePage();
    const user = page.props.auth?.user;

    if (!user) return false;

    const roles = Array.isArray(user.roles) ? user.roles : [];
    return roles.includes(role);
}

/**
 * Check if the user has any of the given permissions
 */
export function canAny(permissionList = []) {
    return permissionList.some(p => can(p));
}

/**
 * Check if the user has any of the given roles
 */
export function hasAnyRole(roleList = []) {
    return roleList.some(r => hasRole(r));
}
