import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    root: 'src', // if your index.html is in src
    plugins: [
        laravel({
            input: ['src/main.js'],
            refresh: true,
        }),
    ],
});
