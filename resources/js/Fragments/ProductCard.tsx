import Img from '@/Components/Image'
import { Link } from '@inertiajs/react'
import { ArrowUpRight } from '@phosphor-icons/react'
import React from 'react'

interface Props extends Product {
    wide?: boolean,

}

function ProductCard(props: Props) {
    const { wide = false, id, img_url, name, num_parts, set_num, theme_id, thumbnail, year } = props

    return (
        <Link href={route('product.detail', { set: id })} className='border-2 border-black divide-y-2 divide-black'>
            <div className='p-16px w-full flex bg-[#F5F5F5] gap-16px'>
                <Img className='w-80px h-80px object-contain' src={img_url} />
                <div>
                    <div className='flex justify-between items-center'>
                        <div className='font-bold'>{name}</div>
                        <div className='w-16px h-16px bg-[#46BD0F] rounded-full'></div>
                    </div>
                    <div className='mt-4px mb-8px'>Star Wars / Ultimate Collecto…</div>
                    <div className='pt-8px border-t border-[#D0D4DB]'>2021</div>
                </div>
            </div>
            <div className={`p-16px w-full grid ${wide ? "grid-cols-4" : "grid-cols-2"} gap-16px`}>
                <div>
                    <div className='text-[#4D4D4D]'>Retail</div>
                    <div className='mt-6px font-bold'>$ 849,00</div>
                </div>
                <div>
                    <div className='text-[#4D4D4D]'>Value</div>
                    <div className='mt-6px font-bold'>$ 856,00</div>
                </div>
                <div>
                    <div className='text-[#4D4D4D]'>Growth</div>
                    <div className='bg-[#46BD0F] flex items-center w-[78px] text-center py-2px rounded justify-center mt-6px'>
                        <ArrowUpRight color="white" />
                        <div className='text-white'>+4,1 %</div>
                    </div>
                </div>
                <div>
                    <div className='text-[#4D4D4D]'>Annual</div>
                    <div className='mt-6px font-bold'>6,1 %</div>
                </div>
            </div>
        </Link>
    )
}

export default ProductCard
