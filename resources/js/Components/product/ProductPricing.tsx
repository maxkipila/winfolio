import { ArrowDownRight, ArrowUpRight, Question } from '@phosphor-icons/react'
import React from 'react'
import { t } from '../Translator'

interface Props {
    product: Product
}

function ProductPricing(props: Props) {
    const { product } = props

    return (
        <div className='border-2 border-black p-24px flex flex-col gap-12px mt-32px mob:p-0 mob:pt-24px mob:border-0 mob:border-t-2'>
            <div className='font-bold text-xl'>{t('Pricing')}</div>

            <div className='flex items-center justify-between w-full border-t  border-[#D0D4DB] pt-12px font-nunito'>
                <div className='flex items-center gap-4px '>
                    <div>{t('Value')}</div>
                    <Question size={20} color="#4D4D4D" />
                </div>
                {
                    product?.latest_price?.value ?
                        <div>{product.latest_price?.value}</div>
                        :
                        <div className='font-nunito font-bold '>{t('Zatím neexistují žádná data')}</div>
                }
            </div>

            <div className='flex items-center justify-between w-full border-t  border-[#D0D4DB] pt-12px'>
                <div className='flex items-center gap-4px font-nunito'>
                    <div>{t('Growth')}</div>
                    <Question size={20} color="#4D4D4D" />
                </div>
                {
                    product?.growth?.monthly ?
                        <div className={`${product?.growth?.monthly >= 0 ? "bg-[#46BD0F]" : "bg-[#ED2E1B]"}  flex items-center w-[78px] text-center pb-2px pt-6px rounded justify-center mt-6px`}>
                            {
                                product?.growth?.monthly >= 0 ?
                                    <ArrowUpRight size={16} className='mb-4px' color="white" />
                                    :
                                    <ArrowDownRight size={16} className='mb-4px' color="white" />
                            }
                            <div className='text-white'>{product?.growth?.monthly} %</div>
                        </div>
                        :
                        <div className='font-nunito font-bold '>{t('Zatím neexistují žádná data')}</div>
                }
            </div>

            <div className='flex items-center justify-between w-full border-t  border-[#D0D4DB] pt-12px font-nunito'>
                <div className='flex items-center gap-4px '>
                    <div>{t('Annual growth')}</div>
                    <Question size={20} color="#4D4D4D" />
                </div>
                {
                    product?.growth?.annual ?
                        <div>{product?.growth?.annual}%</div>
                        :
                        <div className='font-nunito font-bold '>{t('Zatím neexistují žádná data')}</div>
                }
            </div>

            {/* <div className='flex items-center justify-between w-full border-t  border-[#D0D4DB] pt-12px font-nunito'>
                <div className='flex items-center gap-4px '>
                    <div>{t('Rolling growth')}</div>
                    <Question size={20} color="#4D4D4D" />
                </div>
                <div></div>
            </div> */}

            <div className='flex items-center justify-between w-full border-t  border-[#D0D4DB] pt-12px font-nunito'>
                <div className='flex items-center gap-4px '>
                    <div>{t('1-year growth')}</div>
                    <Question size={20} color="#4D4D4D" />
                </div>
                {
                    product?.growth?.yearly ?
                        <div>{product?.growth?.yearly}%</div>
                        :
                        <div className='font-nunito font-bold '>{t('Zatím neexistují žádná data')}</div>
                }
            </div>

            {/* <div className='flex items-center justify-between w-full border-t border-b border-[#D0D4DB] pt-12px pb-12px font-nunito'>
                <div className='flex items-center gap-4px '>
                    <div>{t('Future growth')}</div>
                    <Question size={20} color="#4D4D4D" />
                </div>
                <div></div>
            </div> */}

        </div>
    )
}

export default ProductPricing
