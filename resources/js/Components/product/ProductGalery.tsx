import React from 'react'
import Img from '../Image'

interface Props {
    product: Product
}

function ProductGalery(props: Props) {
    const { product } = props

    return (
        <div className='flex gap-24px mob:flex-col mob:mt-24px'>
            <div className='border-2 border-black w-full max-w-[590px]'>
                <Img className='w-full object-cover' src={product?.img_url} />
            </div>
            <div className='flex flex-col gap-24px mob:flex-row mob:overflow-x-auto'>
                {/* <Img className='max-w-[200px] border-2 border-black' src={product?.img_url} />
                            <Img className='max-w-[200px] border-2 border-black' src={product?.img_url} /> */}
            </div>
        </div>
    )
}

export default ProductGalery
