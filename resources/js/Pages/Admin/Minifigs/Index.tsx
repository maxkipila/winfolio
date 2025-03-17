import Img from '@/Components/Image'
import { FormContext } from '@/Fragments/forms/FormContext'

import Table from '@/Fragments/Table/Table'
import Td from '@/Fragments/Table/Td'
import Th from '@/Fragments/Table/Th'
import AdminLayout from '@/Layouts/AdminLayout'
import { Link } from '@inertiajs/react'
import { PencilSimple, Trash } from '@phosphor-icons/react'
import React, { useContext, useEffect } from 'react'


interface Props {
    minifigs: {
        data: Array<Minifig>;
    }
}

function Index(props: Props) {
    const { minifigs } = props



    return (
        <div className=''>
            <AdminLayout title='Minifigurky | Winfolio'>
                <div className=' w-full p-16px'>
                    <MinifigTable absolute_items={minifigs.data} />
                </div>
            </AdminLayout>
        </div>
    )
}

export function MinifigTable({ absolute_items, hide_meta }: { absolute_items?: Array<Minifig>, hide_meta?: boolean }) {
    return (
        <Table<Minifig> title="Minifigurky" item_key='minifigs' Row={Row} hide_meta={hide_meta} absolute_items={absolute_items}>
            <Th order_by='id'>ID</Th>
            <Th order_by='fig_num'>Číslo figurky</Th>
            <Th order_by='name'>Název figurky</Th>
            <Th>Náhled</Th>
            <Th>Počet dílků</Th>
            {/*  <Th>Rok vydání</Th> */}
        </Table>
    )
}

function Row(props: Minifig & { setItems: React.Dispatch<React.SetStateAction<Minifig[]>> }) {
    const { id, name, fig_num, num_parts, img_url } = props

    const { setData } = useContext(FormContext);

    return (
        <tr className='rounded-0  group hover:outline hover:outline-2 outline-black'>
            <Td>{id}</Td>
            <Td>{fig_num}</Td>
            <Td>{name}</Td>
            <Td>
                <Link className='hover:underline' href=/* {route('sets.show', { set: id })} */'#'>
                    {img_url && <Img src={img_url} alt={name} className='w-32px h-32px' />}
                </Link>
            </Td>
            <Td>{num_parts}</Td>
            {/* <Td>{theme_id}</Td>
            <Td>{year}</Td> */}
        </tr>
    );
}

export default Index
