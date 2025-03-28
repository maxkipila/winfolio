import { ModalsContext } from '@/Components/contexts/ModalsContext'
import Img from '@/Components/Image'
import usePageProps from '@/hooks/usePageProps'
import { Check, Clock, CreditCard, X } from '@phosphor-icons/react'
import React, { useContext } from 'react'
import { Button } from './UI/Button'

interface Props { }

function BuyPremiumModal(props: Props) {
    const { } = props
    let { close } = useContext(ModalsContext)
    const { auth } = usePageProps<{ auth: { user: User } }>();
    return (
        <div onClick={() => { close() }} className="bg-black bg-opacity-80 fixed top-0 left-0 w-full h-screen items-center justify-center mob:block mob:max-h-full flex z-max p-24px mob:pb-0">
            <div onClick={(e) => { e.stopPropagation(); }} className='bg-white border-2 border-black min-w-[480px] mob:min-w-0 mob:w-full mob:max-h-90vh overflow-y-auto'>
                <div className='flex items-end justify-end'>
                    <div onClick={() => { close() }} className='w-40px h-40px bg-black flex items-center justify-center'>
                        <X color='white' size={24} />
                    </div>
                </div>
                <div className='p-48px'>
                    <div className='bg-[#ED2E1B] text-white flex items-center gap-8px px-8px py-6px rounded-sm max-w-[158px]'>
                        <Clock size={24} />
                        <div className='font-bold'>Omezená nabídka</div>
                    </div>
                    <div className='flex justify-between items-center mt-12px'>
                        <div>
                            <div className='font-bold text-6xl'>{auth.user.first_name},</div>
                            <div className='font-nunito font-semibold'>získejte premium</div>
                        </div>
                        <Img className='w-[84px] h-[84px] object-cover' src="/assets/img/user.png" />
                    </div>
                    <div className='mt-18px'>
                        <div className='flex items-center gap-8px'>
                            <div className='bg-[#F7AA1A] h-24px w-24px flex items-center justify-center rounded-full'>
                                <Check size={16} />
                            </div>
                            <div className='font-nunito font-semibold'>Název první výhody</div>
                        </div>
                        <div className='flex items-center gap-8px mt-8px'>
                            <div className='bg-[#F7AA1A] h-24px w-24px flex items-center justify-center rounded-full'>
                                <Check size={16} />
                            </div>
                            <div className='font-nunito font-semibold'>Název první výhody</div>
                        </div>
                    </div>
                    <Img className='mt-18px mx-auto' src="/assets/img/price-badge.png" />
                    <div className='mt-18px mb-24px'>
                        <div className='flex items-center gap-8px'>
                            <div className=' h-24px w-24px flex items-center justify-center rounded-full'>
                                <Check color='#F7AA1A' size={24} />
                            </div>
                            <div className='font-nunito font-semibold'>Název první výhody</div>
                        </div>
                        <div className='flex items-center gap-8px mt-8px'>
                            <div className=' h-24px w-24px flex items-center justify-center rounded-full'>
                                <Check color='#F7AA1A' size={24} />
                            </div>
                            <div className='font-nunito font-semibold'>Název první výhody</div>
                        </div>
                    </div>
                    <Button href={"#"} icon={<CreditCard size={24} />}>Předplatit</Button>
                </div>
            </div>
        </div>
    )
}

export default BuyPremiumModal
