import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

export function useTheme(defaultTheme = 'dark') {
    const currentTheme = ref(localStorage.getItem('theme_preference') || defaultTheme);

    const applyTheme = (theme) => {
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
        } else {
            document.documentElement.classList.add('light');
            document.documentElement.classList.remove('dark');
        }
    };

    const toggleTheme = () => {
        currentTheme.value = currentTheme.value === 'dark' ? 'light' : 'dark';
        applyTheme(currentTheme.value);
        localStorage.setItem('theme_preference', currentTheme.value);

        // Sync with backend
        router.post('/theme-toggle', { theme: currentTheme.value }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const initTheme = () => {
        applyTheme(currentTheme.value);
    };

    return {
        currentTheme,
        toggleTheme,
        initTheme,
        applyTheme,
    };
}
