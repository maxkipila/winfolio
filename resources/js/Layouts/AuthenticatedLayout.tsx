import Img from '@/Components/Image'
import { Link, router } from '@inertiajs/react'
import { BellSimple, Door, MagnifyingGlass, Sparkle } from '@phosphor-icons/react'
import React, { ReactNode } from 'react'


interface MenuItemProps {
    text: string,
    link: string,
    active?: boolean
}

function MenuItem(props: MenuItemProps) {
    const { text, link, active } = props
    return (
        <Link className={`py-27px border-b-2 font-bold ${active ? "border-black" : "border-white"}`} href={link}>{text}</Link>
    )
}


interface Props {
    children: ReactNode
}

function AuthenticatedLayout(props: Props) {
    const { children } = props
    function logout() {
        router.post(route('logout.account'))
    }
    return (
        <div className='relative'>
            <div className='flex fixed top-0 justify-between items-center px-24px w-full border-b border-[#E6E6E6] bg-white'>
                <div className='flex items-center gap-24px'>
                    <Img src="assets/img/logo.png" />
                    <div className='flex bg-[#F7AA1A] gap-8px rounded items-center px-12px py-4px'>
                        <Sparkle size={24} />
                        <div className='font-bold'>Premium</div>
                    </div>
                </div>
                <div className='flex gap-48px'>
                    <div className='flex gap-24px'>
                        <MenuItem text="Dashboard" link='#' active={route()?.current()?.includes('dashboard')} />
                        <MenuItem text="Catalogue" link='#' />
                        <MenuItem text="Chest" link='#' />
                        <MenuItem text="Awards" link='#' />
                    </div>
                    <div className='flex gap-16px items-center'>
                        <MagnifyingGlass size={24} />
                        <BellSimple size={24} />
                        <Door className='cursor-pointer' onClick={() => { logout() }} size={24} />
                    </div>
                </div>
            </div>
            <div className='mt-[72px]'>
                {children}
            </div>

        </div>
    )
}

export default AuthenticatedLayout
