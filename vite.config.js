import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import { viteStaticCopy } from 'vite-plugin-static-copy'
import path from "path";

export default defineConfig(({ command, mode }) => {

    const env = loadEnv(mode, process.cwd(), '');

    return {
        resolve: {
            alias: {
                "@": path.resolve(__dirname, "resources/js"),
                "app": path.resolve(__dirname, "resources/js"),
                "components": path.resolve(__dirname, "resources/js/Components"),
                "fragments": path.resolve(__dirname, "resources/js/Fragments"),
                "utils": path.resolve(__dirname, "resources/utils"),
                "assets": path.resolve(__dirname, "resources/assets"),
                "icons": path.resolve(__dirname, "resources/assets/icons"),
                "ziggy": path.resolve(__dirname, "vendor/tightenco/ziggy/src/js/index.js")
            },
        },
        plugins: [
            laravel({
                input: [
                    'resources/js/app.tsx',
                    'resources/sass/app.scss',
                ],
                ssr: 'resources/js/ssr.tsx',
                refresh: ['resources/views/**'],
            }),
            react(),
            viteStaticCopy({
                targets: [

                    ((env?.IS_STAGE ?? 'false') == 'true') ?
                        {
                            src: 'resources/assets/robots-stage.txt',
                            dest: '../',
                            overwrite: true,
                            rename: 'robots.txt'

                        }
                        :
                        {
                            src: 'resources/assets/robots-live.txt',
                            dest: '../',
                            overwrite: true,
                            rename: 'robots.txt'
                        }

                ]
            })
        ],
        ssr: {
            noExternal: ['@inertiajs/server'],
        },
        server: {
            host: "0.0.0.0",
            hmr: {
                host: "localhost"
            },
            // watch: {
            //     usePolling: true
            // }
        },

    }
}
);
