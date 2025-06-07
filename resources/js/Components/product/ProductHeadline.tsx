import { Export, Heart } from '@phosphor-icons/react'
import React from 'react'

interface Props {
    product: Product,
    form: any
}

function ProductHeadline(props: Props) {
    const { product, form } = props
    const { data, post } = form;
    return (
        <div className='mob:flex mob:flex-col-reverse'>
            <div className='mt-24px mob:mt-24px'>
                <div className='font-nunito font-bold'>{product?.product_num}</div>
                <div className='font-bold text-4xl'>{product?.name}</div>
            </div>
            <div className='mt-12px flex items-center justify-between w-full mob:mt-0'>
                <div className='flex gap-8px items-center'>
                    <div className='font-nunito font-bold'>{product?.year}</div>
                    <div className='relative group'>
                        <div className={`h-16px w-16px  ${product.availability != null ? "bg-[#46BD0F]" : "bg-[#FEB34A]"} rounded-full`}></div>
                        <div className='hidden group-hover:block  absolute translate-x-1/2 p-4px bg-white border-2 border-black -mt-8px top-0 left-0 whitespace-nowrap'>
                            {product?.availability == null ? "Non-avilable" : product?.availability}
                        </div>
                    </div>
                </div>
                <div className='flex gap-16px'>
                    <Export size={24} />
                    <Heart weight={product?.favourited ? "fill" : "regular"} color={product?.favourited ? "#FFB400" : "black"} className='cursor-pointer' onClick={() => {
                        post(route('favourites.toggle', { type: encodeURI(encodeURIComponent("App\\Models\\Product")), favouritable: product.id }))
                    }} size={24} />
                </div>
            </div>
        </div>
    )
}

export default ProductHeadline
