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
            <div className='border-2 border-black w-full max-w-[590px] h-[444px] overflow-hidden flex items-center justify-center'>
                <Img className='w-full h-full object-contain object-center' src={product?.images[galeryIndex]} />
            </div>
            {
                product?.images?.length > 1 &&
                <div className='flex flex-col gap-24px mob:flex-row mob:overflow-x-auto max-h-[444px] overflow-auto tagscrollbar'>
                    {
                        product?.images?.map((im, i) =>
                            <Img onClick={() => { setGaleryIndex(i) }} className={`max-w-[200px] border-2 cursor-pointer ${galeryIndex === i ? 'border-app-button-dark bg-app-button/50' : 'border-black'}`} src={im} />
                        )
                    }

                </div>
            }
        </div>
    )
}

export default ProductGalery
