import { PortfolioContext } from '@/Components/contexts/PortfolioContext'
import Img from '@/Components/Image'
import { t } from '@/Components/Translator'
import { Link } from '@inertiajs/react'
import { ArrowDownRight, ArrowUpRight } from '@phosphor-icons/react'
import React, { useContext } from 'react'

interface Props extends Product {
    wide?: boolean,

}

function PortfolioProductCard(props: Props) {
    const { wide = false, id, img_url, annual_growth, availability, growth, monthly_growth, weekly_growth, name, num_parts, product_num, theme_id, thumbnail, year, theme, latest_price } = props
    let { setSelected } = useContext(PortfolioContext)
    return (
        <div onClick={() => { setSelected({ ...props }) }} className='border-2 border-black divide-y-2 divide-black'>
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
            <div className={`p-16px w-full grid ${wide ? "grid-cols-4" : "grid-cols-2"} gap-16px`}>
                {
                    latest_price?.retail &&
                    <div>
                        <div className='text-[#4D4D4D]'>{t('Retail')}</div>
                        <div className='mt-6px font-bold'>$ {latest_price?.retail}</div>
                    </div>
                }
                {
                    latest_price?.value &&
                    <div>
                        <div className='text-[#4D4D4D]'>{t('Value')}</div>
                        <div className='mt-6px font-bold'>$ {latest_price?.value}</div>
                    </div>
                }
                {
                    growth?.monthly &&
                    <div>
                        <div className='text-[#4D4D4D]'>{t('Growth')}</div>
                        <div className={`${growth.monthly >= 0 ? "bg-[#46BD0F]" : "bg-[#ED2E1B]"} pt-6px  flex items-center w-[78px] text-center py-2px rounded justify-center mt-6px`}>
                            {
                                growth.monthly >= 0 ?
                                    <ArrowUpRight className='mb-4px' color="white" />
                                    :
                                    <ArrowDownRight className='mb-4px' color="white" />
                            }
                            <div className='text-white'>{growth.monthly} %</div>
                        </div>
                    </div>
                }
                {
                    growth?.annual &&
                    <div>
                        <div className='text-[#4D4D4D]'>{t('Annual')}</div>
                        <div className='mt-6px font-bold '>{growth.annual} %</div>
                    </div>
                }
            </div>
        </div>
    )
}

export default PortfolioProductCard
