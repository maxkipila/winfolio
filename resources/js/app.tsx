import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot, hydrateRoot } from 'react-dom/client';
import { ModalsProvider } from './Fragments/Modals';
import PortfolioContextProvider from './Components/contexts/PortfolioContext';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.tsx`,
            import.meta.glob('./Pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        if (import.meta.env.SSR) {
            hydrateRoot(el,
                <ModalsProvider>
                    <PortfolioContextProvider>
                        <App {...props} />
                    </PortfolioContextProvider>
                </ModalsProvider>
            );
            return;
        }

        createRoot(el).render(
            <ModalsProvider>
                <PortfolioContextProvider>
                    <App {...props} />
                </PortfolioContextProvider>
            </ModalsProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});
