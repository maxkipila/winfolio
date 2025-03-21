import Img from '@/Components/Image'
import { Link, router } from '@inertiajs/react'
import { BellSimple, Door, House, List, MagnifyingGlass, ShippingContainer, Sparkle, Star, User, X } from '@phosphor-icons/react'
import React, { ReactNode, useState } from 'react'


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
    let [open, setOpen] = useState(false)
    return (
        <div className='relative font-teko'>
            <div className={`nMob:hidden fixed top-[60px] left-0 w-full bg-white h-screen z-max transform duration-300  ${open ? "" : "-translate-x-full"}`}>
                <div className='flex flex-col p-24px'>
                    <MenuItem text="Dashboard" link={route('dashboard')} active={route()?.current()?.includes('dashboard')} />
                    <MenuItem text="Catalogue" link={route('catalog')} active={route()?.current()?.includes('catalog')} />
                    <MenuItem text="Chest" link={route('chest')} active={route()?.current()?.includes('chest')} />
                    <MenuItem text="Awards" link='#' />
                </div>
            </div>
            <div className='nMob:hidden fixed bottom-0 left-0 flex w-full bg-white'>
                <Link href={route('dashboard')} className={`w-full py-12px flex justify-center items-center border-t-2 ${route()?.current()?.includes('dashboard') ? "border-black bg-[#F7AA1A]" : "border-[#DEDFE5] "}`}>
                    <House size={24} />
                </Link>
                <Link href={route('catalog')} className={`w-full py-12px flex justify-center items-center border-t-2 ${route()?.current()?.includes('catalog') ? "border-black bg-[#F7AA1A]" : "border-[#DEDFE5] "}`}>
                    <MagnifyingGlass size={24} />
                </Link>
                <Link href={route('chest')} className={`w-full py-12px flex justify-center items-center border-t-2 ${route()?.current()?.includes('chest') ? "border-black bg-[#F7AA1A]" : "border-[#DEDFE5] "}`}>
                    <ShippingContainer size={24} />
                </Link>
                <Link href={route('dashboard')} className={`w-full py-12px flex justify-center items-center border-t-2 ${route()?.current()?.includes('awards') ? "border-black bg-[#F7AA1A]" : "border-[#DEDFE5] "}`}>
                    <Star size={24} />
                </Link>
            </div>
            <div className='flex fixed top-0 justify-between items-center px-24px mob:py-16px w-full border-b z-50  border-[#E6E6E6] bg-white'>
                <div className='flex items-center gap-24px mob:hidden'>

                    <Img src="/assets/img/logo.png" />
                    <div className='flex bg-[#F7AA1A] gap-8px rounded items-center px-12px py-4px'>
                        <Sparkle size={24} />
                        <div className='font-bold'>Premium</div>
                    </div>
                </div>
                <div className='flex items-center gap-24px nMob:hidden w-full justify-between'>
                    <div className='flex bg-[#F7AA1A] gap-8px rounded items-center px-12px py-4px'>
                        <Sparkle size={14} />
                        <div className='font-bold text-sm'>Premium</div>
                    </div>
                    <Img src="/assets/img/logo.png" />
                    <div className='w-100px flex justify-end'>
                        <BellSimple size={24} />
                    </div>
                </div>

                <div className='flex gap-48px mob:hidden'>
                    <div className='flex gap-24px'>
                        <MenuItem text="Dashboard" link={route('dashboard')} active={route()?.current()?.includes('dashboard')} />
                        <MenuItem text="Catalogue" link={route('catalog')} active={route()?.current()?.includes('catalog')} />
                        <MenuItem text="Chest" link={route('chest')} active={route()?.current()?.includes('chest')} />
                        <MenuItem text="Awards" link='#' />
                    </div>
                    <div className='flex gap-16px items-center'>
                        <MagnifyingGlass size={24} />
                        <BellSimple size={24} />
                        <Door className='cursor-pointer' onClick={() => { logout() }} size={24} />
                        <Link href={route('profile.index')}>
                            <User size={24} />
                        </Link>
                    </div>
                </div>
            </div>
            <div className='mt-[72px] mob:pb-50px'>
                {children}
            </div>

        </div>
    )
}

export default AuthenticatedLayout
