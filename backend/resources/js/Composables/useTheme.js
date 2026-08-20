import { ref } from 'vue';
import axios from 'axios';

export function useTheme(defaultTheme = 'dark') {
    const currentTheme = ref(localStorage.getItem('theme_preference') || defaultTheme);

    const applyTheme = (theme) => {
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
            if (document.body) {
                document.body.classList.add('dark');
                document.body.classList.remove('light');
            }
        } else {
            document.documentElement.classList.add('light');
            document.documentElement.classList.remove('dark');
            if (document.body) {
                document.body.classList.add('light');
                document.body.classList.remove('dark');
            }
        }
    };

    const toggleTheme = () => {
        currentTheme.value = currentTheme.value === 'dark' ? 'light' : 'dark';
        applyTheme(currentTheme.value);
        try {
            localStorage.setItem('theme_preference', currentTheme.value);
        } catch (e) {}

        // Async sync with backend (0 UI latency)
        try {
            axios.post('/theme-toggle', { theme: currentTheme.value });
        } catch (e) {}
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
