import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

import { trans } from './helpers/trans';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'مخزني ERP';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) });
        vueApp.config.globalProperties.$t = trans;
        vueApp.config.globalProperties.trans = trans;
        return vueApp
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#10b981', // Emerald primary accent
        showSpinner: true,
    },
});
