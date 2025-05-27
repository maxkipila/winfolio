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
        <div onClick={() => { close() }} className="bg-black bg-opacity-80 fixed top-0 left-0 w-full h-screen mob:flex items-center justify-center mob:items-end mob:max-h-full flex z-max p-24px mob:p-0">
            <div onClick={(e) => { e.stopPropagation(); }} className='bg-white border-2 border-black min-w-[480px] mob:min-w-0 mob:w-full mob:max-h-90vh overflow-y-auto grid'>
                <div className='col-start-1 row-start-1 bg-gradient-to-b from-[#FEFFE8] to-white nMob:hidden'>
                    <Img className='w-full' src="/assets/img/batman.png" />
                </div>
                <div className='col-start-1 row-start-1'>
                    <div className='flex items-end justify-end mob:mt-24px mob:px-24px mob:justify-between mob:items-center'>
                        <Img className='nMob:hidden' src="/assets/img/logo.svg" />
                        <div onClick={() => { close() }} className='w-40px h-40px bg-black flex items-center justify-center'>
                            <X color='white' size={24} />
                        </div>
                    </div>
                    <div className='px-48px pb-48px pt-8px mob:pt-[90px]'>
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
                        <div className='mt-18px mob:mt-48px'>
                            <div className='flex items-center gap-8px'>
                                <div className='bg-[#FFB400] h-24px w-24px flex items-center justify-center rounded-full'>
                                    <Check size={16} />
                                </div>
                                <div className='font-nunito font-semibold'>Název první výhody</div>
                            </div>
                            <div className='flex items-center gap-8px mt-8px'>
                                <div className='bg-[#FFB400] h-24px w-24px flex items-center justify-center rounded-full'>
                                    <Check size={16} />
                                </div>
                                <div className='font-nunito font-semibold'>Název první výhody</div>
                            </div>
                        </div>
                        <Img className='mt-18px mx-auto' src="/assets/img/price-badge.png" />
                        <div className='mt-18px mb-24px mob:mb-48px'>
                            <div className='flex items-center gap-8px'>
                                <div className=' h-24px w-24px flex items-center justify-center rounded-full'>
                                    <Check color='#FFB400' size={24} />
                                </div>
                                <div className='font-nunito font-semibold'>Název první výhody</div>
                            </div>
                            <div className='flex items-center gap-8px mt-8px'>
                                <div className=' h-24px w-24px flex items-center justify-center rounded-full'>
                                    <Check color='#FFB400' size={24} />
                                </div>
                                <div className='font-nunito font-semibold'>Název první výhody</div>
                            </div>
                        </div>
                        <Button href={"#"} icon={<CreditCard size={24} />}>Předplatit</Button>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default BuyPremiumModal
