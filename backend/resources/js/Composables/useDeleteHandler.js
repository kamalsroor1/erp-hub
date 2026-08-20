import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { confirmDelete, confirmDialog } from '@/helpers/alert';

export function useDeleteHandler() {
    const isDeleting = ref(false);

    const deleteItem = async (url, itemName = 'هذا العنصر', customWarning = '', options = {}) => {
        const isConfirmed = await confirmDelete(itemName, customWarning);
        if (isConfirmed) {
            isDeleting.value = true;
            router.delete(url, {
                preserveScroll: true,
                onFinish: () => {
                    isDeleting.value = false;
                },
                ...options,
            });
        }
    };

    const confirmAndExecute = async (actionCallback, options = {}) => {
        const isConfirmed = await confirmDialog(options);
        if (isConfirmed && typeof actionCallback === 'function') {
            return actionCallback();
        }
        return false;
    };

    return {
        isDeleting,
        deleteItem,
        confirmAndExecute,
    };
}
