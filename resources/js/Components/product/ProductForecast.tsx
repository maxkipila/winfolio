import React from 'react'
import { t } from '../Translator'
import { Question } from '@phosphor-icons/react'

interface Props {
    product: Product
}

function ProductForecast(props: Props) {
    const { product } = props

    return (
        <>
            
            <div className='border-2 border-black p-32px mt-20px mob:border-0 mob:border-t-2 mob:p-0 mob:pt-32px'>
                <div className='font-bold text-xl mb-12px'>{t('Set Pricing')}</div>
                <div className='flex items-center justify-between w-full pb-16px border-b border-[#D0D4DB]'>
                    <div className='font-nunito'>{t('Retail price')}</div>
                    <div className='flex items-center gap-16px'>
                        <div className='font-nunito font-semibold text-[#4D4D4D]'>${product?.latest_price?.retail}</div>
                        <div className='py-6px px-8px bg-[#F5F5F5] text-xs font-semibold font-nunito text-[#4D4D4D]'>{t('Medium accuracy')}</div>
                    </div>
                </div>

                <div className='font-bold text-xl mt-12px flex items-center gap-12px mb-12px'>
                    <div>{t('New/Sealed')}</div>
                    <Question size={24} />
                </div>
                <div className='flex items-center justify-between mb-8px'>
                    <div className='font-nunito flex items-center'>{t('Value')}</div>
                    <div className='font-semibold font-nunito text-[#4D4D4D]'>${product?.latest_price?.value}</div>
                </div>
                <div className='flex items-center justify-between w-full pb-16px border-b border-[#D0D4DB]'>
                    <div className='font-nunito flex items-center gap-12px'>
                        <div>{t('90-day change')}</div>
                        <Question size={24} />
                    </div>
                    <div className='flex items-center gap-16px'>
                        <div className='py-6px px-8px bg-[#F5F5F5] text-xs font-semibold font-nunito text-[#4D4D4D]'>-3.09%</div>
                    </div>
                </div>

                <div className='font-bold text-xl mt-12px gap-12px flex items-center mb-12px'>
                    <div>{t('Used')}</div>
                    <Question size={24} />
                </div>
                <div className='flex items-center justify-between w-full'>
                    <div className='font-nunito'>{t('Value')}</div>
                    <div className='flex items-center gap-16px'>
                        <div className='py-6px px-8px  font-semibold font-nunito text-[#4D4D4D]'>${product?.latest_price?.value}</div>
                    </div>
                </div>
                <div className='flex items-center justify-between w-full pb-16px'>
                    <div className='font-nunito flex items-center gap-12px'>
                        <div>{t('Range')}</div>
                        <Question size={24} />
                    </div>
                    <div className='flex items-center gap-16px'>
                        <div className='py-6px px-8px  font-semibold font-nunito text-[#4D4D4D]'>${product?.latest_price?.value}-${product?.latest_price?.retail}</div>
                    </div>
                </div>

            </div>
        </>
    )
}

export default ProductForecast
