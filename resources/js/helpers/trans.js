import { usePage } from '@inertiajs/vue3';

/**
 * Global translation helper for Vue 3 Inertia Components
 * Usage: trans('pos.title') or trans('super.dashboard')
 */
export function trans(key, replace = {}) {
    if (!key || typeof key !== 'string') return '';

    let translations = {};
    try {
        const page = usePage();
        if (page && page.props && page.props.translations) {
            translations = page.props.translations;
        }
    } catch (e) {
        // Fallback if called outside setup context
    }

    const parts = key.split('.');
    let value = translations;

    for (const part of parts) {
        if (value && typeof value === 'object' && value[part] !== undefined) {
            value = value[part];
        } else {
            return key; // Fallback to key if not found
        }
    }

    if (typeof value === 'string') {
        Object.keys(replace).forEach((placeholder) => {
            value = value.replace(`:${placeholder}`, replace[placeholder]);
        });
        return value;
    }

    return key;
}

export function useTrans() {
    return {
        t: trans,
        trans,
    };
}
