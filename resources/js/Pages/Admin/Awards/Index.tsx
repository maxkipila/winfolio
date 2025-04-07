import Img from '@/Components/Image'
import { FormContext } from '@/Fragments/forms/FormContext'
import Table from '@/Fragments/Table/Table'
import Td from '@/Fragments/Table/Td'
import Th from '@/Fragments/Table/Th'
import AdminLayout from '@/Layouts/AdminLayout'
import { Link } from '@inertiajs/react'
import React, { useContext, useEffect } from 'react'


interface Props {
    awards: Array<Award>
}

function Index(props: Props) {
    const { } = props.awards

    return (
        <AdminLayout
            customButtonHref={route('admin.awards.create')}
            addButtonText="Přidat nové ocenění"
            title='Ocenění | Winfolio'>
            <AwardTable />
        </AdminLayout>
    )
}

export function AwardTable({ absolute_items, hide_meta }: { absolute_items?: Array<Award>, hide_meta?: boolean }) {
    return (
        <Table<Award> title="Ocenění" item_key='awards' Row={Row} absolute_items={absolute_items}>
            <Th order_by='id'>ID</Th>
            <Th order_by='name'>Název ocenění</Th>
            <Th >Popis</Th>


        </Table>
    );
}
function Row(props: Award & { setItems: React.Dispatch<React.SetStateAction<Award[]>> }) {
    const { id, name, description } = props;


    return (
        <tr className="odd:bg-[#F5F5F5] hover:outline hover:outline-2 hover:outline-offset-[-2px] outline-black">
            <Td ><Link href={route('admin.awards.edit', { award: id })}>{id}</Link></Td>
            <Td><Link href={route('admin.awards.edit', { award: id })}>{name}</Link></Td>
            <Td><Link href={route('admin.awards.edit', { award: id })}>{description}</Link></Td>
            <Td></Td>





        </tr>
    );
}

export default Index
