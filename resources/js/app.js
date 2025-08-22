import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/inertia-vue3";
import Echo from "laravel-echo";
import Pusher from "pusher-js";

createInertiaApp({
    resolve: (name) => import(`./Pages/${name}.vue`),
    setup({ el, app, props, plugin }) {
        createApp({ render: () => h(app, props) })
            .use(plugin)
            .mount(el);
    },
});

console.log("Connecting to Echo...");
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});
