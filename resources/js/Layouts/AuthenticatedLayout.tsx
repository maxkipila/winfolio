import { ModalsContext } from '@/Components/contexts/ModalsContext'
import PortfolioContextProvider from '@/Components/contexts/PortfolioContext'
import Img from '@/Components/Image'
import { t } from '@/Components/Translator'
import Modals, { MODALS } from '@/Fragments/Modals'
import { Link, router } from '@inertiajs/react'
import { BellSimple, Door, House, List, MagnifyingGlass, Plus, ShippingContainer, Sparkle, Star, User, X } from '@phosphor-icons/react'
import React, { ReactNode, useContext, useState } from 'react'


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
        console.log('logout')
        /* router.post(route('logout.account')) */
        router.post(route('logout.user.account'))
    }

    let [open, setOpen] = useState(false)
    let { open: _OpenModal, modal } = useContext(ModalsContext)

    return (

        <div className='font-teko '>
            <div className='relative'>
                <div className={`nMob:hidden fixed top-[60px] left-0 w-full bg-white h-screen z-max transform duration-300  ${open ? "" : "-translate-x-full"}`}>
                    <div className='flex flex-col p-24px'>
                        <MenuItem text={t("Dashboard")} link={route('dashboard')} active={route()?.current()?.includes('dashboard')} />
                        <MenuItem text={t("Catalogue")} link={route('catalog')} active={route()?.current()?.includes('catalog')} />
                        <MenuItem text={t("Chest")} link={route('chest')} active={route()?.current()?.includes('chest')} />
                        <MenuItem text={t("Awards")} link='#' />
                    </div>
                </div>
                <div className='nMob:hidden fixed bottom-0 left-0 flex w-full bg-white z-max'>
                    <Link href={route('dashboard')} className={`w-full py-12px flex justify-center items-center border-t-2 ${route()?.current()?.includes('dashboard') ? "border-black bg-[#FFB400]" : "border-[#DEDFE5] "}`}>
                        <House size={24} />
                    </Link>
                    <Link href={route('catalog')} className={`w-full py-12px flex justify-center items-center border-t-2 ${route()?.current()?.includes('catalog') ? "border-black bg-[#FFB400]" : "border-[#DEDFE5] "}`}>
                        <MagnifyingGlass size={24} />
                    </Link>
                    <Link href={route('chest')} className={`w-full py-12px flex justify-center items-center border-t-2 ${route()?.current()?.includes('chest') ? "border-black bg-[#FFB400]" : "border-[#DEDFE5] "}`}>
                        <ShippingContainer size={24} />
                    </Link>
                    <Link href={route('awards')} className={`w-full py-12px flex justify-center items-center border-t-2 ${route()?.current()?.includes('awards') ? "border-black bg-[#FFB400]" : "border-[#DEDFE5] "}`}>
                        <Star size={24} />
                    </Link>
                </div>
                <div className='flex fixed top-0 justify-between items-center px-24px mob:py-16px w-full border-b z-50  border-[#E6E6E6] bg-white z-max'>
                    <div className='flex items-center gap-24px mob:hidden'>

                        <Link href={route('dashboard')}>
                            <Img src="/assets/img/logo.svg" />
                        </Link>
                        <div onClick={() => { _OpenModal(MODALS.GET_PREMIUM) }} className='flex bg-[#FFB400] gap-8px rounded items-center px-12px py-4px cursor-pointer'>
                            <Sparkle size={24} />
                            <div className='font-bold'>Premium</div>
                        </div>
                    </div>
                    <div className='flex items-center gap-24px nMob:hidden w-full justify-between'>
                        <div onClick={() => { _OpenModal(MODALS.GET_PREMIUM) }} className='flex bg-[#FFB400] gap-8px rounded items-center px-12px py-4px'>
                            <Sparkle size={14} />
                            <div className='font-bold text-sm'>Premium</div>
                        </div>
                        <Img src="/assets/img/logo.svg" />
                        <div onClick={() => { _OpenModal(MODALS.NOTIFICATION) }} className='w-100px flex justify-end'>
                            <BellSimple size={24} />
                        </div>
                    </div>

                    <div className='flex gap-48px mob:hidden '>
                        <div className='flex gap-24px'>
                            <MenuItem text={t("Dashboard")} link={route('dashboard')} active={route()?.current()?.includes('dashboard')} />
                            <MenuItem text={t("Catalogue")} link={route('catalog')} active={route()?.current()?.includes('catalog')} />
                            <MenuItem text={t("Chest")} link={route('chest')} active={route()?.current()?.includes('chest')} />
                            <MenuItem text={t("Awards")} link={route('awards')} active={route()?.current()?.includes('awards')} />
                        </div>
                        <div className='flex gap-16px items-center z-max bg-white'>
                            {/* <MagnifyingGlass size={24} /> */}
                            <BellSimple className='cursor-pointer' onClick={() => { _OpenModal(MODALS.NOTIFICATION); }} size={24} />
                            {/* <Door className='cursor-pointer' onClick={() => { logout() }} size={24} /> */}
                            <Link href={route('profile.index')}>
                                <User size={24} />
                            </Link>
                        </div>
                    </div>
                </div>
                <div className='mt-[72px] mob:pb-50px relative'>
                    {
                        route()?.current()?.includes('chest') &&
                        <div onClick={() => { _OpenModal(MODALS.PORTFOLIO, false, { create_portfolio: true }) }} className='cursor-pointer fixed bottom-64px right-40px w-40px h-40px border-2 border-black bg-[#FFB400] rounded-full flex items-center justify-center'>
                            <Plus size={24} />
                        </div>
                    }

                    {children}

                </div>

            </div>
            <Modals />
        </div>

    )
}

export default AuthenticatedLayout
