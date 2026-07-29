import { createInertiaApp } from '@inertiajs/vue3';
import 'boxicons/css/boxicons.min.css';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import Swal from 'sweetalert2';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { route, ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './Backend/composables/useAppearance';



import { configureEcho } from '@laravel/echo-vue';
import store from './store';

configureEcho({
    broadcaster: 'reverb',
});

const appName = import.meta.env.VITE_APP_NAME || 'DezeStore';

createInertiaApp({
    title: (title) => {
        if (!title) {
            return appName;
        }

        return title.includes(appName) ? title : `${title} - ${appName}`;
    },
    resolve: (name) => {
        if (name.startsWith('Frontend/')) {
            return resolvePageComponent(
                `./Frontend/pages/${name.replace('Frontend/', '')}.vue`,
                import.meta.glob<DefineComponent>('./Frontend/pages/**/*.vue'),
            );
        } else {
            return resolvePageComponent(
                `./Backend/pages/${name}.vue`,
                import.meta.glob<DefineComponent>('./Backend/pages/**/*.vue'),
            );
        }
    },
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) });

        vueApp.use(plugin);
        vueApp.use(ZiggyVue, Ziggy);

        vueApp.use(store);


        vueApp.config.globalProperties.hasPermission = function (str: string) {
            const permissions = this.$page?.props?.permission;
            // console.log('Checking permission:', str, permissions);
            if (!permissions) return false;
            return permissions[str] ?? false;
        };

        vueApp.config.globalProperties.showMessage = function (title = '') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: false,
                background: '#000',
                color: '#fff',
                width: 'auto',
                padding: '0.4rem 0.6rem',
                showCloseButton: true,

                customClass: {
                    popup: 'minimal-toast',
                    title: 'minimal-toast-title',
                    closeButton: 'minimal-toast-close',
                },

                didOpen: (toast) => {
                    const closeBtn = toast.querySelector('.swal2-close');
                    const titleEl = toast.querySelector('.swal2-title');

                    if (closeBtn && titleEl) {
                        // Move close button AFTER title
                        titleEl.after(closeBtn);
                    }
                },
            });

            Toast.fire({ title });
        };

        vueApp.mount(el);
    },
    progress: {
        color: '#38bdf8',
        includeCSS: true,
        showSpinner: false,
        delay: 100,
    },
});

initializeTheme();
window.route = route;
