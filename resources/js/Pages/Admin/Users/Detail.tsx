import Img from '@/Components/Image'
import PrimaryButton from '@/Components/PrimaryButton'
import SecondaryButton from '@/Components/SecondaryButton'
import Breadcrumbs from '@/Fragments/forms/Breadcrumbs'
import CustomButton from '@/Fragments/forms/Buttons/CustomButton'
import { FormContext } from '@/Fragments/forms/FormContext'
import Table from '@/Fragments/Table/Table'
import Td from '@/Fragments/Table/Td'
import Th from '@/Fragments/Table/Th'
import AdminLayout from '@/Layouts/AdminLayout'
import { Link } from '@inertiajs/react'
import { TrashSimple } from '@phosphor-icons/react'

import React, { useContext, useEffect } from 'react'


interface TruhlaProps {
    products: ProductLego[];

}

export function TruhlaTable({ products }: TruhlaProps) {
    return (
        <table className="table-auto  font-nunito w-full border-2 border-black">
            <thead>
                <tr className=''>
                    <th className="border px-4px py-2px">ID</th>
                    <th className="border px-4px py-2px">Pojmenovat</th>
                    <th className="border px-4px py-2px">Pojmenovat</th>
                    <th className="border px-4px py-2px">Rok</th>
                    <th className="border px-4px py-2px">Retail</th>
                    <th className="border px-4px py-2px">Value</th>
                    <th className="border px-4px py-2px">Stav</th>
                </tr>
            </thead>
            <tbody>
                {products.map((prod) => (
                    <tr key={prod.id} className="odd:bg-gray-100 ">
                        <td className="border px-4px py-2px">{prod.id}</td>
                        <td className="border px-4px py-2px">{prod.product_num}</td>
                        <td className="border px-4px py-2px">{prod.name}</td>
                        <td className="border px-4px py-2px">{prod.year}</td>
                        <td className="border px-4px py-2px">{prod.retail}</td>
                        <td className="border px-4px py-2px">{prod.value}</td>
                        <td className="border px-4px py-2px">{prod.condition}</td>
                    </tr>
                ))}
            </tbody>
        </table>
    );
}

type Props = {
    user: User
    minifig: Array<Minifig>
    products: Array<ProductLego>
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
function ChestRow(props: ProductLego & { sent_payments_sum: number } & { setItems: React.Dispatch<React.SetStateAction<Array<ProductLego & { products: number }>>> }) {
    const { id, thumbnail, sent_payments_sum, setItems } = props;
    /* const { open, close } = useContext(ModalsContext) */
    const { setData } = useContext(FormContext);

    useEffect(() => {
        setData(d => ({ ...d, [`status-${id}`]: status }))
    }, [])

    return (
        <tr className='odd:bg-[#F5F5F5] hover:outline hover:outline-2 hover:outline-offset-[-2px] outline-black w-full '>
            <Td><Link className='hover:underline' href={route('admin.users.show', { user: id })}>{id}</Link></Td>
            <Td><Link className='hover:underline' href={route('admin.users.show', { user: id })}></Link></Td>
            <Td><Link className='hover:underline' href={route('admin.users.show', { user: id })}>{/* {Math.floor((sent_payments_sum ?? 0) * 0.05 * 100) / 100} */} {/* Kč */}</Link></Td>
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
                                src={props.user.thumbnail || "/assets/img/user.png"}
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
                                    <div className="text-[14px] font-medium leading-[20px] text-[#4D4D4D]">Registrován {new Date(props.user.created_at).toLocaleDateString('cs-CZ')}</div>
                                    <div className="text-[#4D4D4D]">ID: {id}</div>
                                </div>
                            </div>


                        </div>
                    </div>
                    <div className="border-2 border-black  border-t-0 py-[12px] px-[16px] w-full flex flex-row">
                        <div className="w-1/3">
                            <div className="text-[14px] font-medium leading-[20px] text-[#4D4D4D]">Telefon</div>
                            <div className="font-bold mt-[6px]">{prefix} {phone}</div>
                        </div>
                        <div className="w-1/3">
                            <div className="text-[14px]  font-medium leading-[20px] text-[#4D4D4D]">Datum narození</div>
                            <div className="font-bold mt-[6px]">{day}. {month}. {year}</div>
                        </div>
                        <div className="w-1/3">
                            <div className="text-[14px] font-medium leading-[20px] text-[#4D4D4D]">Adresa</div>
                            <div className="font-bold mt-[6px]">{street} {street_2}, {psc} {city} <br />{country}</div>
                        </div>
                    </div>


                    <div className="text-xl font-teko font-bold mb-16px mt-40px">Truhla</div>

                    <div className=''>

                        <TruhlaTable products={props.products.map(p => ({
                            ...p,
                            retail: p.retail?.toString() ?? '',
                            value: p.value?.toString() ?? '',
                            condition: p.condition ?? ''
                        }))} />
                    </div>

                    <div className="flex w-full gap-12px mt-24px  items-center ">
                        <div className='w-1/3'>
                            <CustomButton className="border-2 w-full flex items-center justify-center border-black py-8px px-24px font-bold bg-white">
                                Poslat mail se změnou hesla
                            </CustomButton>
                        </div>
                        <div className='w-1/3'>
                            <CustomButton className="border-2 w-full flex items-center justify-center border-black py-8px px-24px font-bold bg-white">
                                Deaktivovat účet
                            </CustomButton>
                        </div>
                        <div className='w-1/3 flex items-center'>
                            <CustomButton className="border-2 w-full flex items-center justify-center border-[#E66C6C] text-[#E66C6C] py-8px px-24px font-bold bg-white">
                                <TrashSimple weight='bold' className='text-[#E66C6C] mr-2' />
                                <span className='text-[#E66C6C]'>Smazat účet</span>
                            </CustomButton>
                        </div>
                    </div>

                </div>
            </AdminLayout>
        </>
    )
}

export default Detail