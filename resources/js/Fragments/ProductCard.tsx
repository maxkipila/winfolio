import Img from '@/Components/Image'
import { t } from '@/Components/Translator'
import { Link } from '@inertiajs/react'
import { ArrowDownRight, ArrowUpRight } from '@phosphor-icons/react'
import React from 'react'

interface Props extends Product {
    wide?: boolean,

}

function ProductCard(props: Props) {
    const { wide = false, id, img_url, prices_count, annual_growth, availability, growth, monthly_growth, weekly_growth, name, num_parts, product_num, theme_id, thumbnail, year, theme, latest_price } = props

    return (
        <Link href={route('product.detail', { product: id })} className='border-2 border-black divide-y-2 divide-black'>
            <div className='p-16px w-full flex bg-[#F5F5F5] gap-16px'>
                <Img className='w-80px h-80px object-contain' src={img_url} />
                <div className='w-full'>
                    <div className='flex justify-between items-center w-full'>
                        <div className='font-bold'>{name}</div>
                        <div className={`w-16px h-16px ${availability != null ? "bg-[#46BD0F]" : "bg-[#FEB34A]"} rounded-full flex-shrink-0`}></div>
                    </div>
                    <div className='mt-4px mb-8px'>{theme?.name ?? "---"}</div>
                    <div className='pt-8px border-t border-[#D0D4DB]'>{year}</div>
                </div>
            </div>
            {
                prices_count > 0 &&
                <div className={`p-16px w-full grid bg-[white] ${wide ? "grid-cols-4" : "grid-cols-2"} gap-16px`}>
                    {
                        prices_count > 0 &&
                        <div>
                            <div className='text-[#4D4D4D]'>{t('Retail')}</div>
                            <div className='mt-6px font-bold'>$ {latest_price?.retail}</div>
                        </div>
                    }
                    {
                        prices_count > 0 &&
                        <div>
                            <div className='text-[#4D4D4D]'>{t('Value')}</div>
                            <div className='mt-6px font-bold'>$ {latest_price?.value}</div>
                        </div>
                    }
                    {
                        prices_count > 0 &&
                        <div>
                            <div className='text-[#4D4D4D]'>{t('Growth')}</div>
                            <div className={`${growth?.monthly >= 0 ? "bg-[#46BD0F]" : "bg-[#ED2E1B]"}  flex items-center w-[78px] text-center pb-2px pt-6px rounded justify-center mt-6px`}>
                                {
                                    growth?.monthly >= 0 ?
                                        <ArrowUpRight size={16} className='mb-4px' color="white" />
                                        :
                                        <ArrowDownRight size={16} className='mb-4px' color="white" />
                                }
                                <div className='text-white '>{growth?.monthly} %</div>
                            </div>
                        </div>
                    }
                    {
                        prices_count > 0 &&
                        <div>
                            <div className='text-[#4D4D4D]'>{t('Annual')}</div>
                            <div className='mt-6px font-bold'>{growth?.annual} %</div>
                        </div>
                    }
                </div>
            }
        </Link>
    )
}

export default ProductCard
