import { Head, Link } from '@inertiajs/react'

import { title } from 'process'
import React, { ReactNode } from 'react'
import Header from './Header'
import Sidebar from './Sidebar'
import Img from '@/Components/Image'

type Props = {
    title?: string;
    children?: ReactNode;
    rightChild?: ReactNode;
}
const AdminLayout = ({ title = 'Winfolio', children, rightChild }: Props) => {
    return (
        <div className='flex gap-16px font-satoshi'>
            <Sidebar /* auth={auth} */ />
            <Head title={title} />
            <div className='flex-grow flex flex-col mt-32px gap-16px'>

                <Header rightChild={rightChild} />
                <main className='flex flex-col flex-grow overflow-auto gap-16px items-center'>
                    {children}
                </main>
            </div>
            {/* <Modals /> */}
        </div>
    )
}

export default AdminLayout