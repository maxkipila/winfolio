import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import React from 'react'

interface Props {}

function Edit(props: Props) {
    const {} = props

    return (
        <AuthenticatedLayout>
            <div>Edit page for profile</div>
        </AuthenticatedLayout>
    )
}

export default Edit
