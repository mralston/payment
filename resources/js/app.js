import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import './bootstrap';

createInertiaApp({
    title: (title) => `${title}`,
    resolve: name => {
        // Dynamically import your Vue components from the 'Pages' directory
        // This assumes your components are located in resources/js/Pages
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
