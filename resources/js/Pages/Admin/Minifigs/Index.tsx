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
    minifigs: Array<ProductLego>
}

function Index(props: Props) {
    const { minifigs } = props



    return (
        <div className=''>
            <AdminLayout rightChild={false} title='Minifigurky | Winfolio'>
                <div className=' w-full p-16px'>
                    <MinifigTable />
                </div>
            </AdminLayout>
        </div>
    )
}

export function MinifigTable({ absolute_items, hide_meta }: { absolute_items?: Array<ProductLego>, hide_meta?: boolean }) {
    return (
        <Table<ProductLego> title="Minifigurky" item_key='minifigs' Row={Row} absolute_items={absolute_items}>
            <Th order_by='id'>ID</Th>
            <Th order_by='fig_num'>Číslo figurky</Th>
            <Th order_by='name'>Název figurky</Th>
            <Th>Náhled</Th>
            <Th>Počet dílků</Th>
            {/*  <Th>Rok vydání</Th> */}
        </Table>
    )
}

function Row(props: ProductLego & { setItems: React.Dispatch<React.SetStateAction<ProductLego[]>> }) {
    const { id, name, product_num, num_parts, img_url } = props

    const { setData } = useContext(FormContext);

    return (
        <tr className='odd:bg-[#F5F5F5]  hover:outline hover:outline-2 hover:outline-offset-[-2px] outline-black'>
            <Link href={route('admin.products.show.minifig', { product: id })} >
                <Td>{id}</Td>
            </Link>
            <Td>{product_num}</Td>

            <Link href={route('admin.products.show.minifig', { product: id })} >
                <Td>{name}</Td>
            </Link>
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
