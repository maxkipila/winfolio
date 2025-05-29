import Img from '@/Components/Image'
import { t } from '@/Components/Translator'
import { Button } from '@/Fragments/UI/Button'
import usePageProps from '@/hooks/usePageProps'
import { Link, router } from '@inertiajs/react'
import { ArrowRight, Basket, BellSimple, FacebookLogo, Files, InstagramLogo, Lifebuoy, List, LockKey, UserCircle, X, XLogo } from '@phosphor-icons/react'
import React, { ReactNode, useState } from 'react'

interface Props {
    children: ReactNode
}

function ProfileLayout(props: Props) {
    const { children } = props
    let [open, setOpen] = useState(false)
    const { auth, locale } = usePageProps<{ auth: { user: User }, locale: string }>();
    function logout() {
            console.log('logout')
            /* router.post(route('logout.account')) */
            router.post(route('logout.user.account'))
        }
    return (
        <div className='nMob:flex mob:relative'>
            <div className={`flex-shrink-0 p-24px mob:py-12px nMob:border-r nMob:border-[#E6E6E6] min-h-screen-no-header mob:fixed mob:top-[60px] mob:z-50 mob:bg-white mob:overflow-y-scroll mob:h-screen-no-header mob:w-full mob:transform mob:duration-300 ${open ? "" : "mob:-translate-x-full"}`}>
                <div className='flex justify-start nMob:hidden mb-24px'>
                    <X onClick={() => { setOpen((p) => !p) }} size={24} />
                </div>
                <div className='flex items-center gap-16px w-[345px]'>
                    <Img className='w-[84px] h-[84px]' src="/assets/img/profile-photo.png" />
                    <div>
                        <div className='font-bold text-xl'>{auth?.user?.first_name} {auth?.user?.last_name}</div>
                        <div className='font-bold'>{auth?.user?.nickname}</div>
                    </div>
                </div>
                <div className='w-full bg-[#F5F5F5] flex items-center justify-between px-8px py-12px mt-24px rounded-sm'>
                    <div className='flex gap-16px items-center'>
                        <div className='bg-white w-40px h-40px flex items-center justify-center rounded-full'>
                            <UserCircle size={24} />
                        </div>
                        <div className='font-bold'>{t('Upravit profil')}</div>
                    </div>
                    <ArrowRight size={24} />
                </div>
                <div className='mt-12px border border-black p-2px flex'>
                    <Link href={route('profile.index', {locale:'cs'})} className={`w-full font-bold text-center ${locale == "cs"?"bg-black text-white":"bg-white text-black"} text-lg`}>CZ</Link>
                    <Link href={route('profile.index', {locale:'en'})} className={`w-full font-bold text-center ${locale == "en"?"bg-black text-white":"bg-white text-black"}  text-lg`}>ENG</Link>
                </div>
                <div className='mt-40px text-xl font-bold'>{t('Nové')}</div>
                <div className='w-full bg-[#F5F5F5] flex items-center justify-between px-8px py-12px mt-12px rounded-sm'>
                    <div className='flex gap-16px items-center'>
                        <div className='bg-white w-40px h-40px flex items-center justify-center rounded-full'>
                            <LockKey size={24} />
                        </div>
                        <div className='font-bold'>{t('Zabezpečení')}</div>
                    </div>
                    <ArrowRight size={24} />
                </div>
                <div className='w-full bg-[#F5F5F5] flex items-center justify-between px-8px py-12px mt-12px rounded-sm'>
                    <div className='flex gap-16px items-center'>
                        <div className='bg-white w-40px h-40px flex items-center justify-center rounded-full'>
                            <Basket size={24} />
                        </div>
                        <div className='font-bold'>{t('Obnovit nákupy')}</div>
                    </div>
                    <ArrowRight size={24} />
                </div>
                <Link href={route('profile.notifications')} className='w-full bg-[#F5F5F5] flex items-center justify-between px-8px py-12px mt-12px rounded-sm'>
                    <div className='flex gap-16px items-center'>
                        <div className='bg-white w-40px h-40px flex items-center justify-center rounded-full'>
                            <BellSimple size={24} />
                        </div>
                        <div className='font-bold'>{t('Notifikace')}</div>
                    </div>
                    <ArrowRight size={24} />
                </Link>


                <div className='mt-40px text-xl font-bold'>{t('Sociální sítě')}</div>
                <a href="https://www.facebook.com/people/Winfolio/61576678209102/" target="_blank" className='w-full bg-[#F5F5F5] flex items-center justify-between px-8px py-12px mt-12px rounded-sm'>
                    <div className='flex gap-16px items-center'>
                        <div className='bg-white w-40px h-40px flex items-center justify-center rounded-full'>
                            <FacebookLogo size={24} />
                        </div>
                        <div className='font-bold'>Facebook</div>
                    </div>
                    <ArrowRight size={24} />
                </a>
                <a href="https://www.instagram.com/winfolio" traget="_blank" className='w-full bg-[#F5F5F5] flex items-center justify-between px-8px py-12px mt-12px rounded-sm'>
                    <div className='flex gap-16px items-center'>
                        <div className='bg-white w-40px h-40px flex items-center justify-center rounded-full'>
                            <InstagramLogo size={24} />
                        </div>
                        <div className='font-bold'>Instagram</div>
                    </div>
                    <ArrowRight size={24} />
                </a>
                <a href="https://x.com/winfolio_" target='_blank' className='w-full bg-[#F5F5F5] flex items-center justify-between px-8px py-12px mt-12px rounded-sm'>
                    <div className='flex gap-16px items-center'>
                        <div className='bg-white w-40px h-40px flex items-center justify-center rounded-full'>
                            <XLogo size={24} />
                        </div>
                        <div className='font-bold'>X</div>
                    </div>
                    <ArrowRight size={24} />
                </a>


                <div className='mt-40px text-xl font-bold'>{t('Podpora')}</div>
                <div className='w-full bg-[#F5F5F5] flex items-center justify-between px-8px py-12px mt-12px rounded-sm'>
                    <div className='flex gap-16px items-center'>
                        <div className='bg-white w-40px h-40px flex items-center justify-center rounded-full'>
                            <Lifebuoy size={24} />
                        </div>
                        <div className='font-bold'>{t('Získat pomoc')}</div>
                    </div>
                    <ArrowRight size={24} />
                </div>
                <div className='w-full mb-24px bg-[#F5F5F5] flex items-center justify-between px-8px py-12px mt-12px rounded-sm'>
                    <div className='flex gap-16px items-center'>
                        <div className='bg-white w-40px h-40px flex items-center justify-center rounded-full'>
                            <Files size={24} />
                        </div>
                        <div className='font-bold'>{t('VOP a GDPR')}</div>
                    </div>
                    <ArrowRight size={24} />
                </div>
                <Button onClick={(e)=>{e.preventDefault(); logout();}} href="#">Odhlásit se</Button>
            </div>
            <div className='w-full'>
                <div className='flex justify-start px-24px nMob:hidden'>
                    <List onClick={() => { setOpen((p) => !p) }} size={24} />
                </div>
                <div className='w-full'>
                    {children}
                </div>
            </div>
        </div>
    )
}

export default ProfileLayout
