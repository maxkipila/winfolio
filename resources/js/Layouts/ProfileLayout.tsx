import Img from '@/Components/Image'
import { ArrowRight, Basket, BellSimple, FacebookLogo, Files, InstagramLogo, Lifebuoy, LockKey, UserCircle, XLogo } from '@phosphor-icons/react'
import React, { ReactNode } from 'react'

interface Props {
    children: ReactNode
}

function ProfileLayout(props: Props) {
    const { children } = props

    return (
        <div className='flex'>
            <div className='flex-shrink-0 p-24px border-r border-[#E6E6E6] min-h-screen-no-header'>
                <div className='flex items-center gap-16px w-[345px]'>
                    <Img className='w-[84px] h-[84px]' src="/assets/img/user.png" />
                    <div>
                        <div className='font-bold text-xl'>Matěj Baránek</div>
                        <div className='font-bold'>@legomaniac</div>
                    </div>
                </div>
                <div className='w-full bg-[#F5F5F5] flex items-center justify-between px-8px py-12px mt-24px rounded-sm'>
                    <div className='flex gap-16px items-center'>
                        <div className='bg-white w-40px h-40px flex items-center justify-center rounded-full'>
                            <UserCircle size={24} />
                        </div>
                        <div className='font-bold'>Upravit profil</div>
                    </div>
                    <ArrowRight size={24} />
                </div>
                <div className='mt-12px border border-black p-2px flex'>
                    <div className='w-full bg-black font-bold text-center text-white text-lg'>CZ</div>
                    <div className='w-full bg-white font-bold text-center text-black text-lg'>ENG</div>
                </div>
                <div className='mt-40px text-xl font-bold'>Nové</div>
                <div className='w-full bg-[#F5F5F5] flex items-center justify-between px-8px py-12px mt-12px rounded-sm'>
                    <div className='flex gap-16px items-center'>
                        <div className='bg-white w-40px h-40px flex items-center justify-center rounded-full'>
                            <LockKey size={24} />
                        </div>
                        <div className='font-bold'>Zabezpečení</div>
                    </div>
                    <ArrowRight size={24} />
                </div>
                <div className='w-full bg-[#F5F5F5] flex items-center justify-between px-8px py-12px mt-12px rounded-sm'>
                    <div className='flex gap-16px items-center'>
                        <div className='bg-white w-40px h-40px flex items-center justify-center rounded-full'>
                            <Basket size={24} />
                        </div>
                        <div className='font-bold'>Obnovit nákupy</div>
                    </div>
                    <ArrowRight size={24} />
                </div>
                <div className='w-full bg-[#F5F5F5] flex items-center justify-between px-8px py-12px mt-12px rounded-sm'>
                    <div className='flex gap-16px items-center'>
                        <div className='bg-white w-40px h-40px flex items-center justify-center rounded-full'>
                            <BellSimple size={24} />
                        </div>
                        <div className='font-bold'>Notifikace</div>
                    </div>
                    <ArrowRight size={24} />
                </div>


                <div className='mt-40px text-xl font-bold'>Sociální sítě</div>
                <div className='w-full bg-[#F5F5F5] flex items-center justify-between px-8px py-12px mt-12px rounded-sm'>
                    <div className='flex gap-16px items-center'>
                        <div className='bg-white w-40px h-40px flex items-center justify-center rounded-full'>
                            <FacebookLogo size={24} />
                        </div>
                        <div className='font-bold'>Facebook</div>
                    </div>
                    <ArrowRight size={24} />
                </div>
                <div className='w-full bg-[#F5F5F5] flex items-center justify-between px-8px py-12px mt-12px rounded-sm'>
                    <div className='flex gap-16px items-center'>
                        <div className='bg-white w-40px h-40px flex items-center justify-center rounded-full'>
                            <InstagramLogo size={24} />
                        </div>
                        <div className='font-bold'>Instagram</div>
                    </div>
                    <ArrowRight size={24} />
                </div>
                <div className='w-full bg-[#F5F5F5] flex items-center justify-between px-8px py-12px mt-12px rounded-sm'>
                    <div className='flex gap-16px items-center'>
                        <div className='bg-white w-40px h-40px flex items-center justify-center rounded-full'>
                            <XLogo size={24} />
                        </div>
                        <div className='font-bold'>X</div>
                    </div>
                    <ArrowRight size={24} />
                </div>


                <div className='mt-40px text-xl font-bold'>Podpora</div>
                <div className='w-full bg-[#F5F5F5] flex items-center justify-between px-8px py-12px mt-12px rounded-sm'>
                    <div className='flex gap-16px items-center'>
                        <div className='bg-white w-40px h-40px flex items-center justify-center rounded-full'>
                            <Lifebuoy size={24} />
                        </div>
                        <div className='font-bold'>Získat pomoc </div>
                    </div>
                    <ArrowRight size={24} />
                </div>
                <div className='w-full bg-[#F5F5F5] flex items-center justify-between px-8px py-12px mt-12px rounded-sm'>
                    <div className='flex gap-16px items-center'>
                        <div className='bg-white w-40px h-40px flex items-center justify-center rounded-full'>
                            <Files size={24} />
                        </div>
                        <div className='font-bold'>VOP a GDPR</div>
                    </div>
                    <ArrowRight size={24} />
                </div>
            </div>
            <div className='w-full'>
                {children}
            </div>
        </div>
    )
}

export default ProfileLayout
