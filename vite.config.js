import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin'; // Lipsea importul plugin-ului de Laravel
import tailwindcss from '@tailwindcss/vite'; // Lipsea importul pentru Tailwind

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true, 
        }),
        tailwindcss(),
    ],
    server: {
        host: 'localhost',
        hmr: {
            host: 'localhost',
        },
    },
});