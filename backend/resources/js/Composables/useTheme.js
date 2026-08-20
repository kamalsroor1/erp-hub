import { ref } from 'vue';
import axios from 'axios';

export function useTheme(defaultTheme = 'dark', defaultColor = 'amber') {
    const currentTheme = ref(localStorage.getItem('theme_preference') || defaultTheme);
    const currentColor = ref(localStorage.getItem('system_theme_color') || defaultColor);

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

    const applyColorTheme = (color) => {
        if (!color) return;
        currentColor.value = color;
        document.documentElement.setAttribute('data-theme-color', color);
        if (document.body) {
            document.body.setAttribute('data-theme-color', color);
        }
        try {
            localStorage.setItem('system_theme_color', color);
        } catch (e) {}
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

    const initTheme = (initialColor) => {
        applyTheme(currentTheme.value);
        if (initialColor) {
            applyColorTheme(initialColor);
        } else {
            applyColorTheme(currentColor.value);
        }
    };

    return {
        currentTheme,
        currentColor,
        toggleTheme,
        applyColorTheme,
        initTheme,
        applyTheme,
    };
}
