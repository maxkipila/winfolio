import Img from '@/Components/Image'
import Breadcrumbs from '@/Fragments/forms/Breadcrumbs'
import { FormContext } from '@/Fragments/forms/FormContext'
import Table from '@/Fragments/Table/Table'
import Td from '@/Fragments/Table/Td'
import Th from '@/Fragments/Table/Th'
import AdminLayout from '@/Layouts/AdminLayout'
import { Link } from '@inertiajs/react'

import React, { useContext, useEffect } from 'react'

type Props = {
    user: User
    minifig: Array<Minifig>
}

/* function Usertable({ absolute_items, hide_meta }: { absolute_items?: Array<User>, hide_meta?: boolean }) {
    return (
        <Table<User> title="Truhla" item_key='users' Row={Row} absolute_items={absolute_items}>
            <Th order_by='id'>ID</Th>
            <Th>Set / Minifigurka</Th>
            <Th order_by='first_name'>ID setu/minifigurky</Th>
            <Th>Nazev setu/minifigurky</Th>
            <Th order_by='email'>Rok</Th>
            <Th order_by='email'>Retail</Th>
            <Th order_by='email'>Cena</Th>
            <Th order_by='email'>Stav</Th>

        </Table>
    )
} */
function JobbertRow(props: User & { received_payments_sum: number } & { setItems: React.Dispatch<React.SetStateAction<Array<User & { received_payments_sum: number }>>> }) {
    const { id, status, email, first_name, last_name, phone, prefix, thumbnail, received_payments_sum, setItems } = props;
    /* const { open, close } = useContext(ModalsContext) */
    const { setData } = useContext(FormContext);


    useEffect(() => {
        setData(d => ({ ...d, [`status-${id}`]: status }))
    }, [])

    return (
        <tr className='rounded group'>
            <Td><Link className='hover:underline' href={route('users.edit', { user: id })}>{id}</Link></Td>
            <Td><Link className='hover:underline' href={route('users.edit', { user: id })}>{first_name} {last_name}</Link></Td>
            <Td><Link className='hover:underline' href={route('users.edit', { user: id })}>{/* {job_offers_count} */}</Link></Td>
            <Td><Link className='hover:underline' href={route('users.edit', { user: id })}>{received_payments_sum} Kč</Link></Td>
            <Td><Link className='hover:underline' href={route('users.edit', { user: id })}>{Math.floor(received_payments_sum * 0.05 * 100) / 100} Kč</Link></Td>
            {/* <Td>
                <div className='flex gap-8px items-center justify-end'>
                    <Link href={route('users.edit', { user: id })}><PencilSimple /></Link>
                    <button onClick={(e) => removeItem(e, id)}><Trash className='text-app-input-error' /></button>
                </div>
            </Td> */}
        </tr>
    );
}

function Detail(props: Props) {

    const { first_name, email, street, street_2, city, country, psc, prefix, phone, nickname, day, month, year, id, last_name } = props.user

    return (
        <>
            <AdminLayout rightChild={false} title='Detail | Winfolio'>
                <Breadcrumbs previous={{ name: 'Uživatelé', href: route('admin.users.index') }} current={`${first_name} ${last_name}`} />
                <div className="p-16px mt-24px bg-gray w-full  gap-16px">
                    <div className="border-2 flex flex-col p-16px border-black">
                        <div className="flex  w-full items-center ">
                            <Img
                                src="/assets/img/user.png"
                                alt="avatar"
                                className="w-[80px] h-[80px]"
                            />
                            <div className="w-full flex-col flex p-[16px]">
                                <div className="flex flex-col  items-start mb-4px gap-[12px]">
                                    <div className="relative">
                                        <div className="font-bold text-lg">{first_name} {last_name}</div>
                                        <div className="text-[#4D4D4D] text-[14px] font-bold leading-[20px]">@{nickname}</div>
                                    </div>

                                </div>
                                <div className='border-b border-[#D0D4DB] w-full' />

                                <div className='flex flex-row justify-between mt-8px  border-black'>
                                    <div className="text-[14px] font-medium leading-[20px] text-[#4D4D4D]">Registrován 10. 8. 2024</div>
                                    <div className="text-[#4D4D4D]">ID: 1</div>
                                </div>
                            </div>


                        </div>
                    </div>
                    <div className="border-2 border-black border-t-0 py-[12px] px-[16px] w-full flex flex-row">
                        <div className="w-1/3">
                            <div className="text-[14px] font-medium leading-[20px] text-[#4D4D4D]">Telefon</div>
                            <div className="font-bold">{prefix} {phone}</div>
                        </div>
                        <div className="w-1/3">
                            <div className="text-[14px] font-medium leading-[20px] text-[#4D4D4D]">Datum narození</div>
                            <div className="font-bold">{day}.{month}.{year}</div>
                        </div>
                        <div className="w-1/3">
                            <div className="text-[14px] font-medium leading-[20px] text-[#4D4D4D]">Adresa</div>
                            <div className="font-bold">{street} {psc} {city} <br />{country}</div>
                        </div>
                    </div>


                    {/* <div className="text-xl font-teko font-bold mb-16px mt-40px">Truhla</div> */}

                    <Table<User & { received_payments_sum: number }> item_key='users' Row={JobbertRow}>
                        <Th order_by='id'>ID</Th>
                        <Th order_by='first_name'>Uživatel</Th>
                        <Th>Jobs</Th>
                        <Th>Tržba</Th>
                        <Th>Provize</Th>
                    </Table>


                    {/* Tlačítka dole */}
                    <div className="flex flex-wrap gap-12px justify-end">
                        <button className="border-2 border-black px-16px py-8px font-bold bg-white">
                            Poslat mail se změnou hesla
                        </button>
                        <button className="border-2 border-black px-16px py-8px font-bold bg-white">
                            Deaktivovat účet
                        </button>
                        <button className="border-2 border-black px-16px py-8px font-bold text-red-600 bg-white">
                            Smazat účet
                        </button>
                    </div>
                </div>
            </AdminLayout>
        </>
    )
}

export default Detail