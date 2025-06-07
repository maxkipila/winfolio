import React, { Dispatch, ReactNode, SetStateAction, useCallback, useEffect } from 'react'
import { FormContext } from '../forms/FormContext'
import { router, useForm } from '@inertiajs/react';
import useLazyLoad from '@/hooks/useLazyLoad';
import { MetaBar } from '../MetaBar';



interface Props<T> {
    children: ReactNode
    Row: (props: T & { setItems: Dispatch<SetStateAction<T[]>>; }) => JSX.Element
    item_key: string
    id_key?: string
    title?: string | ReactNode
    absolute_items?: Array<T>
    hide_meta?: boolean
    custom?: string
    filters?: Record<string, string>
}

function Table<T>(props: Props<T>) {
    const { children, Row, custom, title, item_key, id_key = 'id', absolute_items, hide_meta, filters = {} } = props

    const [_itms, button, meta, form, setItems, setTransform] = useLazyLoad<T>(item_key, undefined, filters);

    const search = useForm<any>();

    const items = absolute_items ?? _itms;

    const updateFilters = () => {
        router.visit(button.href, {
            preserveState: true,
            preserveScroll: true,
            method: button.method,
            data: {
                ...button.data,
                page: 1,
                ...filters,
            },
            only: button.only
        });
    }

    useEffect(() => {
        if (Object.keys(filters ?? {}).length > 0)
            updateFilters();
    }, [filters])



    return (
        <>
            <div>
                {
                    title && (
                        typeof title == 'string'
                            ? <div className="text-xl  font-bold font-teko tracking-normal mb-24px">{title}</div>
                            : title
                    )
                }

                <div className={` p-24px border-2 text-sm font-medium border-black rounded-sm px-16px  max-w-limit w-full ${custom}`}>


                    <div className='border-collapse overflow-hidden flex-grow flex'>
                        <table className='flex-grow border-collapse'>
                            <thead>
                                <FormContext.Provider value={{ ...form, hasElement: false }}>
                                    <tr className=''>
                                        {children}
                                    </tr>
                                </FormContext.Provider>
                            </thead>
                            <tbody>
                                <FormContext.Provider value={{ ...search, hasElement: false }}>
                                    {
                                        items.map(p =>
                                            <Row key={`${item_key}-${p?.[id_key]}`} {...p} setItems={setItems} />
                                        )
                                    }
                                </FormContext.Provider>
                            </tbody>
                        </table>
                    </div>
                </div>
                {!hide_meta && <MetaBar {...meta} button={button} />}
            </div >
        </>
    )
}

export default Table
