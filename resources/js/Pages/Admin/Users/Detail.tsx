import AdminLayout from '@/Layouts/AdminLayout'
import React from 'react'

type Props = {
    user: User
}

const Detail = (props: Props) => {
    return (
        <>
            <AdminLayout rightChild={false} title='Detail | Winfolio'>
                <div className="p-16px border-2 border-black rounded-sm bg-white w-full flex flex-col gap-16px">
                    {/* Karta s uživatelem */}
                    <div className="border-2 border-black">
                        {/* Horní řádek: avatar, jméno, přezdívka, datum registrace */}
                        <div className="flex items-center justify-between px-16px py-8px border-b-2 border-black">
                            <div className="flex items-center gap-12px">
                                {/* Avatar (nahraďte vlastní cestou k obrázku) */}
                                <img
                                    src="/assets/img/user.png"
                                    alt="avatar"
                                    className="w-64px h-64px border-2 border-black"
                                />
                                <div>
                                    <div className="font-bold text-lg">{props.user.first_name}{props.user.last_name}</div>
                                    <div className="text-gray-600">{props.user.email}</div>
                                </div>
                            </div>
                            <div className="text-sm text-gray-600">Registrován 10. 8. 2024</div>
                        </div>

                        {/* Spodní řádek: Telefon, Datum narození, Adresa */}
                        <div className="px-16px py-12px flex flex-wrap gap-24px">
                            <div>
                                <div className="text-gray-500">{props.user.prefix}</div>
                                <div className="font-bold">{props.user.phone}</div>
                            </div>
                            <div>
                                <div className="text-gray-500">Datum narození</div>
                                <div className="font-bold">29. 1. 1987</div>
                            </div>
                            <div>
                                <div className="text-gray-500">Adresa</div>
                                <div className="font-bold">Jindřichove 16, 745 09 Brno<br />Česká republika</div>
                            </div>
                        </div>
                    </div>


                    <div className="text-lg font-bold">Truhla</div>


                    <div className="overflow-x-auto">
                        <table className="w-full border-2 border-black">
                            <thead>
                                <tr className="border-b-2 border-black bg-gray-100">
                                    <th className="text-left py-8px px-12px">ID</th>
                                    <th className="text-left py-8px px-12px">Pojmenování</th>
                                    <th className="text-left py-8px px-12px">Rok</th>
                                    <th className="text-left py-8px px-12px">Cena</th>
                                    <th className="text-left py-8px px-12px">Stav</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr className="border-b border-black">
                                    <td className="py-8px px-12px">75313 AT-AT</td>
                                    <td className="py-8px px-12px">Star Wars // Ultimate Collector...</td>
                                    <td className="py-8px px-12px">2021</td>
                                    <td className="py-8px px-12px">$ 849.00</td>
                                    <td className="py-8px px-12px">Dobrý</td>
                                </tr>
                                {/* Další řádky dle potřeby */}
                            </tbody>
                        </table>
                    </div>

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