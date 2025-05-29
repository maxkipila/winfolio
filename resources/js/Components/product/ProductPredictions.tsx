import { Question } from '@phosphor-icons/react'
import React from 'react'
import { t } from '../Translator'

interface Props {
    product: Product
}

function ProductPredictions(props: Props) {
    const { product } = props

    return (
        <div className='mt-32px border-2 border-black p-32px'>
            <div className='font-bold text-xl w-full pb-16px border-b border-black'>{t('Set Predictions')}</div>
            <div className='mt-16px font-bold text-lg'>{t('New/Sealed')}</div>
            <div className='mt-16px'>
                <div className='w-full flex justify-between items-center'>
                    <div className='flex gap-8px items-center'>
                        <div className='text-[#4D4D4D]'>{t('Today’s value')}</div>
                        <Question size={24} />
                    </div>
                    <div className='text-[#4D4D4D]'>${product?.latest_price?.wholesale}</div>
                </div>

                <div className='w-full flex justify-between items-center mt-8px'>
                    <div className='flex gap-8px items-center'>
                        <div className='text-[#4D4D4D]'>{t('1 year forecast')}</div>
                        <Question size={24} />
                    </div>
                    <div className='flex gap-8px items-center'>
                        <div className='text-[#4D4D4D]'>${product?.latest_price?.retail}</div>
                        <div className='py-6px px-8px bg-[#F5F5F5] text-xs font-semibold font-nunito text-[#4D4D4D]'>{t('Medium accuracy')}</div>
                    </div>
                </div>

                <div className='w-full flex justify-between items-center mt-8px'>
                    <div className='flex gap-8px items-center'>
                        <div className='text-[#4D4D4D]'>{t('5 year forecast')}</div>
                        <Question size={24} />
                    </div>
                    <div className='text-[#4D4D4D]'>${product?.latest_price?.retail}</div>

                </div>

                <div className='flex gap-8px w-full mt-16px'>
                    <div className='w-full h-8px rounded-[4px] bg-[#ED2E1B]'></div>
                    <div className='w-full h-8px rounded-[4px] bg-[#FFB400] relative'>
                        <div className='absolute -top-5px left-[50%] transform -translate-x-1/2 h-18px w-18px border border-black rounded-[5px] bg-white'>
                            <div className='w-12px h-12px bg-black mx-auto mt-2px rounded-[5px]'></div>
                        </div>
                    </div>
                    <div className='w-full h-8px rounded-[4px] bg-[#46BD0F]'></div>
                </div>
                <div className='w-full flex justify-between items-center mt-16px'>
                    <div className='font-semibold text-[#4D4D4D] font-nunito'>$32</div>
                    <div className='font-semibold text-[#4D4D4D]  font-nunito'>$33</div>
                </div>
                <div className='mt-16px text-[#4D4D4D] font-semibold font-nunito'>6508941 Faunas’s House is projected to be valued between $32 - $33 within five years.</div>
            </div>
        </div>
    )
}

export default ProductPredictions
