import Img from '@/Components/Image'
import { FormContext } from '@/Fragments/forms/FormContext'
import Table from '@/Fragments/Table/Table'
import Td from '@/Fragments/Table/Td'
import Th from '@/Fragments/Table/Th'
import AdminLayout from '@/Layouts/AdminLayout'
import { Link } from '@inertiajs/react'
import React, { useContext, useEffect } from 'react'


interface Props {
    sets: Array<SetLego>
}

function Index(props: Props) {
    const { sets } = props



    return (
        <div className=''>
            <AdminLayout rightChild={false} title='Sety | Winfolio'>
                <div className=' w-full p-16px'>
                    <SetTable />
                </div>
            </AdminLayout>
        </div>
    )
}

export function SetTable({ absolute_items, hide_meta }: { absolute_items?: Array<SetLego>, hide_meta?: boolean }) {
    return (
        <Table<SetLego> title="Sety" item_key='sets' Row={Row} absolute_items={absolute_items}>
            <Th order_by='id'>ID</Th>
            <Th order_by='set_num'>Číslo setu</Th>
            <Th order_by='name'>Název setu</Th>
            <Th>Náhled</Th>
            <Th order_by='theme_id'>Série / Téma</Th>
            <Th order_by='num_parts'>Počet dílků</Th>
            <Th>Cena</Th>
            <Th order_by='year'>Rok vydání</Th>
        </Table>
    );
}
function Row(props: SetLego & { setItems: React.Dispatch<React.SetStateAction<SetLego[]>> }) {
    const { id, year, name, set_num, num_parts, theme_id, img_url } = props;

    return (
        <tr className='group hover:outline hover:outline-2 hover:outline-offset-[-2px] outline-black'>
            <Link href={route('admin.products.show.set', { product: id })}>
                <Td>{id}</Td>
            </Link>
            <Td>{set_num}</Td>
            <Link href={route('admin.products.show.set', { product: id })}>
                <Td>{name}</Td>
            </Link>
            <Td>
                <Link className='hover:underline' href='#'>
                    {img_url && <Img src={img_url} alt={name} className='w-32px h-32px object-contain' />}
                </Link>
            </Td>
            <Td>{theme_id}</Td>
            <Td>{num_parts}</Td>
            <Td>$ 560.00</Td>
            <Td>{year}</Td>
        </tr>
    );
}

export default Index
