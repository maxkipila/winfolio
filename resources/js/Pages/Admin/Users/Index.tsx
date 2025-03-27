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
import { subscribe } from 'diagnostics_channel'
import React, { useContext, useEffect } from 'react'


interface Props {
    users: Array<User>
    subscriptions: Array<Subscription>
}


function Index(props: Props) {
    const { } = props



    return (
        <AdminLayout rightChild={false} title='Uživatelé | Winfolio'>
            <Usertable />
        </AdminLayout>
    )
}

export function Usertable({ absolute_items, hide_meta }: { absolute_items?: Array<User>, hide_meta?: boolean }) {
    return (
        <Table<User> title="Uživatelé" item_key='users' Row={Row} absolute_items={absolute_items}>
            <Th order_by='id'>ID</Th>
            <Th>Jméno a příjmení</Th>
            <Th order_by='first_name'>Přezdívka</Th>
            <Th>Předplatné</Th>
            <Th order_by='email'>E-mail</Th>

        </Table>
    )
}

function Row(props: User & { setItems: React.Dispatch<React.SetStateAction<User[]>> }) {
    const { id, first_name, last_name, email, nickname, phone, setItems, subscriptions } = props;
    /*     const { open, close } = useContext(ModalsContext) */
    const { setData } = useContext(FormContext);
    const userSubscriptions = props.subscriptions ?? [];
    // If subscriptions is an array in the user object (assuming props structure is {data:[{subscriptions: [...]}]})
    // For displaying in the table, we can join subscription names or IDs together

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
        <tr className='group hover:outline hover:outline-2 hover:outline-offset-[-2px] outline-black'>
            <Td><Link className='hover:underline' href={route('admin.users.edit', { user: id })}>{id}</Link></Td>
            <Td><Link className='hover:underline' href={route('admin.users.edit', { user: id })}> <div className='underline'>{first_name} {last_name}</div> </Link></Td>
            <Td><Link className='hover:underline ' href={route('admin.users.edit', { user: id })}>{nickname}</Link></Td>
            <Td><Link className='hover:underline' href={route('admin.users.edit', { user: id })}>{userSubscriptions.map(sub => sub.name).join(', ')}</Link></Td>
            <Td><Link className='hover:underline' href={route('admin.users.edit', { user: id })}>{email}</Link></Td>
            <Td><Link className='hover:underline' href={route('admin.users.edit', { user: id })}></Link></Td>
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

export default Index
