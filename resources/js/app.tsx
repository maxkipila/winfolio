import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot, hydrateRoot } from 'react-dom/client';
import { ModalsProvider } from './Fragments/Modals';
import PortfolioContextProvider from './Components/contexts/PortfolioContext';
// import moment from 'moment'
// import 'moment/locale/cs' // or any other locale you want

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
export let locale = 'en'
router.on('navigate', (e) => {

    locale = (e?.detail?.page?.props?.locale as string) ?? 'en'
})
createInertiaApp({
    title: (title) => `${title}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.tsx`,
            import.meta.glob('./Pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        if (import.meta.env.SSR) {
            hydrateRoot(el,
                <ModalsProvider>
                    <PortfolioContextProvider pageProps={props}>
                        <App {...props} />
                    </PortfolioContextProvider>
                </ModalsProvider>
            );
            return;
        }

        createRoot(el).render(
            <ModalsProvider>
                <PortfolioContextProvider pageProps={props}>
                    <App {...props} />
                </PortfolioContextProvider>
            </ModalsProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});
