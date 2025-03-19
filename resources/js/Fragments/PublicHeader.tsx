import Img from '@/Components/Image'
import { Link } from '@inertiajs/react'
import React from 'react'

interface Props {}

function PublicHeader(props: Props) {
    const {} = props

    return (
        <div className='py-16px flex justify-between items-center px-24px border-b-2 border-black'>
            <div></div>
            <Img src="/assets/img/logo.png" />
            <div className='flex gap-12px'>
                <Link className='font-bold text-lg' href={route('login')}>Log in</Link>
            </div>
        </div>
    )
}

export default PublicHeader
