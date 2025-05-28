import React from 'react'
import { t } from '../Translator'
import { Link } from '@inertiajs/react'
import Img from '../Image'

interface Props {
    product: Product
}

function ProductMinifigs(props: Props) {
    const { product } = props

    return (
        <div className='mt-40px bg-[#F5F5F5] p-24px'>
            {/* <div className='font-bold text-xl'>Buy this set</div>
                        <div className='flex w-full mt-16px'>
                            <div className={`pb-12px font-bold border-b-2 w-full ${quickBuy ? "border-black" : "border-[#999999] text-[#999999]"}`}>Quick buy</div>
                            <div className={`pb-12px font-bold border-b-2 w-full ${!quickBuy ? "border-black" : "border-[#999999] text-[#999999]"}`}>Community</div>
                        </div>
                        <div className='mt-16px flex justify-between w-full items-center bg-white px-12px py-20px border-2 border-black'>
                            <div className='flex gap-8px items-center'>
                                <div className='bg-[#F5F5F5] w-40px h-40px rounded-full flex items-center justify-center'>
                                    <Lock size={24} />
                                </div>
                                <div className='font-nunito'>Lego Store</div>
                            </div>
                            <div className='flex gap-8px items-center'>
                                <div className='text-[#4D4D4D] font-nunito'>$ 5.99</div>
                                <ArrowRight size={24} />
                            </div>
                        </div>

                        <div className='mt-16px flex justify-between w-full items-center bg-white px-12px py-20px border-2 border-black'>
                            <div className='flex gap-8px items-center'>
                                <div className='bg-[#F5F5F5] w-40px h-40px rounded-full flex items-center justify-center'>
                                    <Basket size={24} />
                                </div>
                                <div className='font-nunito'>eBay</div>
                            </div>
                            <div className='flex gap-8px items-center'>
                                <div className='text-[#4D4D4D] font-nunito'>$ 8-11</div>
                                <ArrowRight size={24} />
                            </div>
                        </div> */}

            {

                <>
                    <div className='mt-40px font-bold text-xl'>{t('Minifigs')}</div>
                    {/* <div className='mt-16px text-[#4D4D4D] font-nunito'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</div> */}
                    {
                        product?.minifigs?.length > 0 ?
                            <div className='grid grid-cols-4 mob:grid-cols-1 gap-12px mob:overflow-y-auto'>
                                {
                                    product?.minifigs?.map((m) =>
                                        <Link href={route('product.detail', { product: m.id })} className='border-2 border-black flex flex-col gap-8px bg-white py-12px px-12px w-full mob:max-h-[200px]'>
                                            <div className='text-center font-bold font-nunito'>{m.name}</div>
                                            <Img className='max-h-100px object-contain' src={m.img_url} />
                                        </Link>
                                    )
                                }
                            </div>
                            :
                            <div className='font-bold text-lg'>{t('Tento produkt neobsahuje minifigurky')}</div>
                    }

                </>
            }
        </div>
    )
}

export default ProductMinifigs
