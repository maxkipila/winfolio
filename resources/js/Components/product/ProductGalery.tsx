import React, { useState } from 'react'
import Img from '../Image'

interface Props {
    product: Product
}

function ProductGalery(props: Props) {
    const { product } = props
    let [galeryIndex, setGaleryIndex] = useState(0)
    return (
        <div className='flex gap-24px mob:flex-col mob:mt-24px'>
            <div className='border-2 border-black w-full max-w-[590px] max-h-[444px] overflow-hidden'>
                <Img className='w-full object-cover' src={product?.images[galeryIndex]} />
            </div>
            {
                product?.images?.length > 0 &&
                <div className='flex flex-col gap-24px mob:flex-row mob:overflow-x-auto max-h-[444px] overflow-auto tagscrollbar'>
                    {
                        product?.images?.map((im, i) =>
                            <Img onClick={()=>{setGaleryIndex(i)}} className='max-w-[200px] border-2 border-black cursor-pointer' src={im} />
                        )
                    }

                </div>
            }
        </div>
    )
}

export default ProductGalery
