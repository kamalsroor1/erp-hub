import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

export function useDeleteHandler() {
    const isDeleting = ref(false);

    const deleteItem = (url, confirmMessage = 'هل أنت متأكد من الحذف؟', options = {}) => {
        if (confirm(confirmMessage)) {
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

    return {
        isDeleting,
        deleteItem,
    };
}
