import { FormContext } from '@/Fragments/forms/FormContext'
import Toggle from '@/Fragments/forms/inputs/Toggle'
import Table from '@/Fragments/Table/Table'
import Td from '@/Fragments/Table/Td'
import Th from '@/Fragments/Table/Th'
import AdminLayout from '@/Layouts/AdminLayout'
import { Link } from '@inertiajs/react'
import { PencilSimple, Trash } from '@phosphor-icons/react'
import React, { useContext, useEffect } from 'react'


interface Props {
    news: Array<News>
}


function Index(props: Props) {
    const { } = props



    return (
        <AdminLayout rightChild={false} title='Novinky a analýzy | Winfolio'>
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
    const { id, title, category, content, is_active, status } = props;
    /*     const { open, close } = useContext(ModalsContext) */
    const { setData } = useContext(FormContext);

    /*   const removeItem = (e, id) => {
          e.preventDefault();
  
          open(MODALS.CONFIRM, false, {
              title: "Potvrdit smazání",
              message: "Opravdu chcete smazat Uživatele?",
              buttons: <DefaultButtons
                  href={route('users.destroy', { user: id })}
                  onCancel={close}
                  onSuccess={() => {
                      setItems(pr => pr.filter(f => f.id != id));
                      close();
                  }}
              />
          })
      } */

    useEffect(() => {
        setData(d => ({ ...d, [`status-${id}`]: status }))
    }, [])

    return (
        <tr className='odd:bg-[#F5F5F5]  hover:outline hover:outline-2 hover:outline-offset-[-2px] outline-black'>
            <Td><Link className='hover:underline' href={'#'}>{id}</Link></Td>
            <Td><Link className='hover:underline' href={'#'}>{title}</Link></Td>
            <Td><Link className='hover:underline ' href={'#'}>{category}</Link></Td>
            <Td><Link className='hover:underline' href={'#'}>{content?.split('.')[0]}...</Link></Td>
            <Td><Link className='hover:underline' href={'#'}>{is_active}</Link></Td>
            <Td><Toggle admin name={`status-${id}`} disabled /> </Td>
            {/*   <Td>{props.received_payments_sum} Kč</Td> */}
            {/*  <Td>{Math.floor((props.received_payments_sum ?? 0) * 0.05 * 100) / 100} Kč</Td> */}
            {/* <Td><Toggle admin name={`status-${id}`} disabled /> </Td> */}

        </tr>
    );
}

export default Index
