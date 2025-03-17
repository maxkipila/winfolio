import { FormContext } from '@/Fragments/forms/FormContext'
import OrderBy from '@/Fragments/forms/inputs/OrderBy'
import Toggle from '@/Fragments/forms/inputs/Toggle'
import { MetaBar } from '@/Fragments/MetaBar'
import Table from '@/Fragments/Table/Table'
import Td from '@/Fragments/Table/Td'
import Th from '@/Fragments/Table/Th'
import useLazyLoad from '@/hooks/useLazyLoad'
import usePageProps from '@/hooks/usePageProps'
import AdminLayout from '@/Layouts/AdminLayout'
import { Link } from '@inertiajs/react'
import { PencilSimple, Trash } from '@phosphor-icons/react'
import React, { useContext, useEffect } from 'react'


interface Props {
    users: Array<User>
}


function Dashboard(props: Props) {
    const { } = props



    return (
        <AdminLayout title='Dashboard | Winfolio'>
            <Usertable />
        </AdminLayout>
    )
}

export function Usertable({ absolute_items, hide_meta }: { absolute_items?: Array<User>, hide_meta?: boolean }) {
    return (
        <Table<User> title="Dashboard" item_key='users' Row={Row} absolute_items={absolute_items}>
            <Th></Th>
            {/*   <Th order_by='id'>ID</Th>
            <Th>Jméno a příjmení</Th>
            <Th>Subscription</Th>
            <Th order_by='first_name'>Username</Th>
            <Th order_by='email'>E-mail</Th> */}
        </Table>
    )
}

function Row(props: User & { setItems: React.Dispatch<React.SetStateAction<User[]>> }) {
    const { id, first_name, last_name, email, phone, setItems } = props;
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
        <tr className='rounded group hover:bg-[#CCEEF0] '>
            {/*             <Td><Link className='hover:underline' href={route('users.edit', { user: id })}>{id}</Link></Td>
            <Td><Link className='hover:underline' href={route('users.edit', { user: id })}>Gold</Link></Td>
            <Td><Link className='hover:underline text-app-button-light' href={route('users.edit', { user: id })}>{first_name} {last_name}</Link></Td>
            <Td><Link className='hover:underline' href={route('users.edit', { user: id })}>{email}</Link></Td> */}
            {/* <Td>{prefix} {phone}</Td> */}
            {/*   <Td>{props.received_payments_sum} Kč</Td> */}
            {/*  <Td>{Math.floor((props.received_payments_sum ?? 0) * 0.05 * 100) / 100} Kč</Td> */}
            {/* <Td><Toggle admin name={`status-${id}`} disabled /> </Td> */}
            {/*  <Td>
                <div className='flex gap-8px items-center justify-end'>
                    <Link href={route('users.edit', { user: id })}><PencilSimple /></Link>
                    <button onClick={(e) => removeItem(e, id)}><Trash className='text-app-input-error' /></button>
                </div>
            </Td> */}
        </tr>
    );
}

export default Dashboard
