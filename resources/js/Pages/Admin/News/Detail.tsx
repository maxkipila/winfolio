import Breadcrumbs from '@/Fragments/forms/Breadcrumbs'
import AdminLayout from '@/Layouts/AdminLayout'
import React from 'react'

type Props = {}

const Detail = (props: Props) => {
    return (
        <AdminLayout rightChild={true} title="Detail Setu | Winfolio">

            <div className="p-[16px] ">
                <Breadcrumbs
                    previous={{ name: 'Novinky a analýzy', href: route('admin.news.index') }}
                    current={`${name} `}
                />

            </div>
        </AdminLayout>
    )
}




export default Detail