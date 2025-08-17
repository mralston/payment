import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import './bootstrap.js';
import { configureEcho } from "@laravel/echo-vue";

createInertiaApp({
    title: (title) => `${title}`,
    resolve: name => {
        // Dynamically import your Vue components from the 'Pages' directory
        // This assumes your components are located in resources/js/Pages
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        configureEcho({
            ...echoConfig,
            namespace: "Mralston.Payment.Events",
        });

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
