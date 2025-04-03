import { Head, Link } from '@inertiajs/react'
import React, { ReactNode } from 'react'
import Header from './Header'
import Sidebar from './Sidebar'

type Props = {
    title?: string;
    children?: ReactNode;
    rightChild?: ReactNode;
    addButtonText?: ReactNode;
    customButtonClassName?: string;
    customButtonHref?: string;
}
const AdminLayout = ({ title = 'Winfolio', children, rightChild, addButtonText, customButtonHref, customButtonClassName }: Props) => {
    return (
        <div className="flex flex-col min-h-screen font-nunito">
            <Head title={title} />
            <Header customButtonHref={customButtonHref} addButtonText={addButtonText} customButtonClassName={customButtonClassName} rightChild={rightChild} />
            <div className='flex'>
                <Sidebar  /* auth={auth} */ />
                <main className='w-full m-48px'>
                    {children}
                </main>
            </div>
            {/* <Modals /> */}
        </div>
    )
}

export default AdminLayout