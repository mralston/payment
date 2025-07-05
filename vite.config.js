import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            // The entry points for your package's assets
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            // Define the build directory for your package's assets
            // This is crucial to avoid conflicts with the main Laravel app's build
            buildDirectory: '../../public/vendor/mralston/finance/build',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    // If your package's Vite config isn't in the root of your Laravel app,
    // you might need to adjust the root.
    // root: __dirname, // This can be useful if your vite.config.js is not in the project root
});
