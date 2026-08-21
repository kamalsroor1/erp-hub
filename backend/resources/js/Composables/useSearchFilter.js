import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

/**
 * useSearchFilter - Reusable Composable for managing search queries and filters with Inertia router.
 * 
 * @param {string} routeName - The endpoint URL to send filter requests to (e.g. '/invoices')
 * @param {Object} initialFilters - Initial filter values
 * @param {Object} options - Configuration options (debounceMs, preserveScroll, preserveState, only)
 */
export function useSearchFilter(routeName, initialFilters = {}, options = {}) {
    const {
        debounceMs = 300,
        preserveScroll = true,
        preserveState = true,
        only = []
    } = options;

    const filters = ref({ ...initialFilters });
    const isFiltering = ref(false);
    let debounceTimer = null;

    const applyFilters = () => {
        isFiltering.value = true;

        const cleanParams = {};
        for (const [key, value] of Object.entries(filters.value)) {
            if (value !== '' && value !== null && value !== undefined && value !== 'all') {
                cleanParams[key] = value;
            }
        }

        const visitOptions = {
            preserveScroll,
            preserveState,
            onFinish: () => {
                isFiltering.value = false;
            }
        };

        if (only.length > 0) {
            visitOptions.only = only;
        }

        router.get(routeName, cleanParams, visitOptions);
    };

    const debouncedApply = () => {
        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            applyFilters();
        }, debounceMs);
    };

    const resetFilters = (defaultValues = {}) => {
        filters.value = { ...defaultValues };
        applyFilters();
    };

    return {
        filters,
        isFiltering,
        applyFilters,
        debouncedApply,
        resetFilters
    };
}
