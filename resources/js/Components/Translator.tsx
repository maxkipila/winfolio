import { locale as app_locale } from '@/app';
import usePageProps from '@/hooks/usePageProps';
import translations from 'assets/translations.json';
//If you have error here, you have been lazy and didn't run composer after pulling changes, you bad boy.
//Fix by running `composer dump-autoload` or `php artisan translations:generate` or by making a change to any of the files in lang folder, while `yarn dev` is running.

interface Props {
    children: string
    data?: Record<string, any>
}


export function t(string: string, data?: Record<string, string>, prefix = 'app', _locale = null) {

    const locale = _locale ?? app_locale

    try {
        let translation: string = (translations[`${locale}.${prefix}.${string}`]) ?? string;

        for (const [key, value] of Object.entries(data ?? {})) {
            if (value)
                translation = translation.replace(`:${key}`, value);
        }

        return translation;

    } catch (error) {
        return string
    }

}

export function _(props: Props) {
    const { locale } = usePageProps<{ locale: string }>();
    return (
        t(props.children, props.data, 'app', locale)
    )
};