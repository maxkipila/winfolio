import Form from '@/Fragments/forms/Form'
import { FormContext } from '@/Fragments/forms/FormContext'
import Toggle from '@/Fragments/forms/inputs/Toggle'
import Table from '@/Fragments/Table/Table'
import Td from '@/Fragments/Table/Td'
import Th from '@/Fragments/Table/Th'
import AdminLayout from '@/Layouts/AdminLayout'
import { Link, useForm } from '@inertiajs/react'
import { PencilSimple, Trash } from '@phosphor-icons/react'
import React, { useContext, useEffect } from 'react'


interface Props {
    news: Array<News>
    id: number
}


function Index(props: Props) {
    const { } = props



    return (
        <AdminLayout
            customButtonHref={route('admin.news.create')}
            addButtonText="Přidat příspěvek"
            title='Novinky a analýzy | Winfolio'>
            <NewsTable />
        </AdminLayout>
    )
}

export function NewsTable({ absolute_items, hide_meta }: { absolute_items?: Array<News>, hide_meta?: boolean }) {
    return (
        <Table<News> title="Novinky a analýzy" item_key='news' Row={Row} absolute_items={absolute_items}>
            <Th order_by='id'>ID</Th>
            <Th>Titulek</Th>
            <Th>Kategorie</Th>
            <Th order_by='content'>Popis</Th>
            <Th ></Th>
            <Th order_by='is_active'>Stav</Th>
        </Table>
    )
}

function Row(props: News & { setItems: React.Dispatch<React.SetStateAction<News[]>> }) {
    const { id, title, category, content, is_active } = props;

    const form = useForm({
        is_active: is_active,
    });
    const { data, post, isDirty, setData } = form;

    useEffect(() => {
        setData(d => ({ ...d, is_active: is_active }))
    }, [])
    useEffect(() => {
        if (isDirty) {
            post(route('admin.news.update', { news: id }))
        }
    }, [data])

    return (
        <tr className='odd:bg-[#F5F5F5]   hover:outline hover:outline-2 hover:outline-offset-[-2px] outline-black'>
            {/* Wrap all cells in a fragment */}
            <>
                <Td><Link className='hover:underline' href={route('admin.news.edit', { news: id })}>{id}</Link></Td>
                <Td><Link className='hover:underline' href={route('admin.news.edit', { news: id })}>{title}</Link></Td>
                <Td><Link className='hover:underline ' href={route('admin.news.edit', { news: id })}>{category}</Link></Td>
                <Td><Link className='hover:underline' href={route('admin.news.edit', { news: id })}>{content?.split('.')[0]}...</Link></Td>
                <Td><Link className='hover:underline' href={route('admin.news.edit', { news: id })}>{is_active}</Link></Td>
                <Td >
                    <div className='flex '>
                        <Form className='' form={form}>
                            <Toggle noPadding className='' name={`is_active`} />
                        </Form>
                    </div>
                </Td>
                {/*   <Td>{props.received_payments_sum} Kč</Td> */}
                {/*  <Td>{Math.floor((props.received_payments_sum ?? 0) * 0.05 * 100) / 100} Kč</Td> */}
                {/* <Td><Toggle admin name={`status-${id}`} disabled /> </Td> */}
            </>
        </tr>
    );
}

export default Index
