import React, { Dispatch, ReactNode, SetStateAction } from 'react'
import { FormContext } from '../forms/FormContext'
import { useForm } from '@inertiajs/react';
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
}

function Table<T>(props: Props<T>) {
    const { children, Row, title, item_key, id_key = 'id', absolute_items, hide_meta } = props

    const [_itms, button, meta, form, setItems] = useLazyLoad<T>(item_key);

    const search = useForm<any>();

    const items = absolute_items ?? _itms;


    return (
        <>
            <div className=' py-16px px-16px rounded-md max-w-limit w-full'>
                {
                    title && (
                        typeof title == 'string'
                            ? <div className='text-2xl font-bold mb-16px'>{title}</div>
                            : title
                    )
                }
                <div className='w-full h-1px border-black border-2 bg-[#E5E9E9] mb-16px'></div>
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
        </>
    )
}

export default Table
