import { Head, Link } from '@inertiajs/react'
import React, { ReactNode } from 'react'
import Header from './Header'
import Sidebar from './Sidebar'

type Props = {
    title?: string;
    children?: ReactNode;
    rightChild?: ReactNode;
}
const AdminLayout = ({ title = 'Winfolio', children, rightChild }: Props) => {
    return (
        <div className='flex font-nunito gap-16px flex-col font-satoshi'>
            <Head title={title} />
            <Header rightChild={rightChild} />
            <div className='flex-grow flex flex-row gap-16px w-full'>
                <Sidebar /* auth={auth} */ />
                <main className='w-full m-48px'>
                    {children}
                </main>
            </div>
            {/* <Modals /> */}
        </div>
    )
}

export default AdminLayout