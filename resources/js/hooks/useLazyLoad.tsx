import { router as Inertia } from '@inertiajs/react';
import { useForm } from '@inertiajs/react';

import React, { useEffect, useRef, useState } from 'react'
import usePageProps from './usePageProps';

// Helper function to limit a value between min and max
const limit = (value: number, min: number, max: number): number => {
    return Math.max(min, Math.min(max, value));
};

export default function useLazyLoad<T>(key: string, _with: Array<string> = [], defaultValues: Record<string, any> = {}, fKeys = { sort: 'sort', paginate: 'paginate', page: 'page' }): [Array<T>, LazyButton, MetaType, any, React.Dispatch<React.SetStateAction<T[]>>, React.Dispatch<any>] {

    const initialRender = useRef(true);

    const { [key]: item } = usePageProps<{ [key: string]: { data: Array<T>, meta: MetaType } }>();
    let { meta, data } = item ?? { meta: { next: 0, total: 0, current_page: 0, from: 0, last_page: 0, per_page: 0, to: 0 }, data: null };

    const form = useForm({
        [fKeys.paginate]: meta?.per_page ?? 10,
        ...defaultValues
    });

    const [transform, setTransform] = useState(() => (data) => data);


    const { data: filters } = form;

    const [items, setitems] = useState([] as Array<T>);

    useEffect(() => {
        if (data) {
            setitems(itms => [...(meta?.current_page > 1 ? itms : []), ...data])
        }
    }, [data, meta])

    useEffect(() => {

        if (initialRender.current === true) {
            initialRender.current = false
            return;
        }
        Inertia.visit("?", {
            only: [key, ..._with],
            preserveScroll: true,
            preserveState: true,
            method: "post",
            data: transform({
                ...filters,
                [fKeys.page]: 1,
                [fKeys.paginate]: filters["paginate"],
                [fKeys.sort]: (filters['order_by']) ? [
                    { name: filters['order_by'], order: filters['order'] ?? 'asc' }
                ] : null
            }),
        })

    }, [filters])

    if (!item || !meta)
        return [
            [],
            {
                href: '',
                only: [key, ..._with],
                preserveScroll: true,
                preserveState: true,
                method: "post",
                data: {},
                as: "button",
                className: meta?.current_page == meta?.last_page ? "hidden pointer-events-none" : undefined
            },
            meta,
            form,
            () => { },
            () => { }
        ];

    else
        meta.next = limit(meta.total - meta.to, 0, meta.per_page);

    return [
        items,
        {
            href: '',
            only: [key, ..._with],
            preserveScroll: true,
            preserveState: true,
            method: "post",
            data: transform({
                ...filters,
                [fKeys.page]: meta?.current_page + 1,
                [fKeys.paginate]: filters["paginate"],
                [fKeys.sort]: (filters['order_by']) ? [
                    { name: filters['order_by'], order: filters['order'] ?? 'asc' }
                ] : undefined
            }),
            as: "button",
            className: meta?.current_page == meta?.last_page ? "hidden pointer-events-none" : undefined
        },
        meta,
        form,
        setitems,
        setTransform
    ]
}
