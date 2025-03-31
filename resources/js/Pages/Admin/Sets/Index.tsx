import Img from '@/Components/Image'
import { FormContext } from '@/Fragments/forms/FormContext'
import Table from '@/Fragments/Table/Table'
import Td from '@/Fragments/Table/Td'
import Th from '@/Fragments/Table/Th'
import AdminLayout from '@/Layouts/AdminLayout'
import { Link } from '@inertiajs/react'
import React, { useContext, useEffect } from 'react'


interface Props {
    sets: Array<ProductLego>
    prices: Array<Prices>
}

function Index(props: Props) {
    const { } = props.sets

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

export function SetTable({ absolute_items, hide_meta }: { absolute_items?: Array<ProductLego>, hide_meta?: boolean }) {
    return (
        <Table<ProductLego> title="Sety" item_key='sets' Row={Row} absolute_items={absolute_items}>
            <Th order_by='id'>ID</Th>
            <Th>Nahled</Th>
            <Th order_by='set_num'>Číslo setu</Th>
            <Th order_by='name'>Název setu</Th>
            <Th>Série / Téma</Th>
            <Th>Rok vydání</Th>
            <Th>Počet dílků</Th>
            <div className='flex justify-end'>
                <Th>Cena</Th>

            </div>

        </Table>
    );
}
function Row(props: ProductLego & { setItems: React.Dispatch<React.SetStateAction<ProductLego[]>> }) {
    const { id, name, product_num, prices, year, num_parts, theme_id, img_url } = props;

    return (
        <tr className="odd:bg-[#F5F5F5] hover:outline hover:outline-2 hover:outline-offset-[-2px] outline-black">
            <Link href={route('admin.products.show.set', { product: id })}>
                <Td>{id}</Td>
            </Link>
            <Td>
                <Link className='hover:underline' href={route('admin.products.show.set', { product: id })}>
                    {img_url && <Img src={img_url} alt={name} className='w-32px h-32px' />}
                </Link>
            </Td>
            <Td>{product_num}</Td>
            <Link href={route('admin.products.show.set', { product: id })}>
                <Td>{name.length > 30 ? name.slice(0, 30) + '…' : name}</Td>
            </Link>

            <Td>{props.theme?.name}</Td>
            <Td>{year}</Td>
            <Td>{num_parts}</Td>
            <Td >$ {prices?.[0]?.value ?? '—'}</Td>
        </tr>
    );
}

export default Index
