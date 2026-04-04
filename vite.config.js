import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    optimizeDeps: {
        include: ['ziggy-js'],
    },
    // [::1]:5173 のみだと 127.0.0.1:5173 が ERR_CONNECTION_REFUSED になることがあるため
    server: {
        host: true,
        port: 5173,
        strictPort: true,
    },
    plugins: [
        laravel({
            input: 'resources/js/app.ts',
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
});
