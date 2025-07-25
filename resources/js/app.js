import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { configureEcho } from "@laravel/echo-vue";

configureEcho({
    broadcaster: import.meta.env.VITE_BROADCAST_CONNECTION,
    key: import.meta.env.VITE_BROADCAST_APP_KEY,
    cluster: import.meta.env.VITE_BROADCAST_APP_CLUSTER,
    forceTLS: true,
    wsHost: import.meta.env.VITE_BROADCAST_HOST,
    wsPort: import.meta.env.VITE_BROADCAST_PORT,
    wssPort: import.meta.env.VITE_BROADCAST_PORT,
    enabledTransports: ["ws", "wss"],
});

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
