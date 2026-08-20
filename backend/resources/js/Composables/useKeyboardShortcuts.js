import { onMounted, onUnmounted } from 'vue';

/**
 * Composable for managing application & POS keyboard shortcuts (SRP)
 */
export function useKeyboardShortcuts(shortcutMap = {}) {
    const handleKeydown = (event) => {
        const handler = shortcutMap[event.key];
        if (typeof handler === 'function') {
            handler(event);
        }
    };

    onMounted(() => {
        window.addEventListener('keydown', handleKeydown);
    });

    onUnmounted(() => {
        window.removeEventListener('keydown', handleKeydown);
    });
}
