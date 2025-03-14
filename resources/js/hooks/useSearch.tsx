import { router as Inertia } from '@inertiajs/react';
import { useForm, usePage } from '@inertiajs/react';
import React, { useEffect, useRef, useState } from 'react'
import usePageProps from './usePageProps';
import { InertiaFormProps } from 'node_modules/@inertiajs/react/types/useForm';
import { useDebouncedCallback } from './useDebounceCallback';

interface LazyButton {

    only: Array<string>,
    preserveScroll: boolean,
    preserveState: boolean,
    method: "post" | "get",
    data: { page: number, paginate: number, sort?: Array<{ name: string, order: 'ASC' | 'DESC' }> },
    as: "button",
    className: string | undefined
}

let cancelToken;

export default function useSearch<T>(key: string, locale = 'cs', delay = 300, method = "post", canCancel = false, form = null as any, withKeys = [] as any): [Array<T>, InertiaFormProps<any>] {

    const initialRender = useRef(true);

    const { [key]: items } = usePageProps<{ [key: string]: Array<T> }>();

    form = form ?? useForm({ q: "", locale: locale });

    // const form = useForm({ q: "", locale: locale });

    const { data: filters, submit, isDirty } = form;
    const [shouldCancel, setshouldCancel] = useState(false)

    function cancelVisit(e) {
        if (e.key == "Enter" && canCancel && filters['q']) {
            e.preventDefault();
            setshouldCancel(() => {
                cancelToken = { cancel: () => { } };
                return true;
            });
        }
    }

    const fnc = useDebouncedCallback((query) => {
        submit(method, "", {
            only: [key, ...withKeys],
            preserveScroll: true,
            preserveState: true,
            onCancelToken: (cn) => cancelToken = cn,
        });

    }, delay);

    useEffect(() => {
        if (shouldCancel && cancelToken) {
            cancelToken?.cancel();
            Inertia.visit(route('e-shop.search', { q: filters['q'], locale: filters['locale'] }))
        }
    }, [cancelToken])

    useEffect(() => {
        try {
            window.addEventListener('keydown', cancelVisit)

            return () => {
                window.removeEventListener('keydown', cancelVisit)
            }
        } catch (error) { }
    }, [filters])

    useEffect(() => {

        if (initialRender.current === true) {
            initialRender.current = false
            return;
        }

        setshouldCancel(false);
        fnc(filters['q']);

    }, [filters])

    return [
        items,
        form
    ]
}
