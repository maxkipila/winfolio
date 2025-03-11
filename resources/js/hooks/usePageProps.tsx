// import { Page } from "@inertiajs/inertia";
// import { usePage } from "@inertiajs/inertia-react";

import { usePage } from "@inertiajs/react";

export default function usePageProps<T>()
: T
{
    const page = usePage<any & {props: T}>();

    return page.props;
}