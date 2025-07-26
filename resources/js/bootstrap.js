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
    namespace: "Mralston.Payment.Events",
});
