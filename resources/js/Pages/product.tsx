import Img from '@/Components/Image'
import { Button } from '@/Fragments/UI/Button'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Export, Heart, Plus } from '@phosphor-icons/react'
import React from 'react'

interface Props { }

function Product(props: Props) {
    const { } = props

    return (
        <AuthenticatedLayout>
            <div className='w-full pb-24px px-24px flex gap-20px pt-32px mob:flex-col'>
                <div className='w-2/3 flex-shrink-0 mob:w-full'>
                    <div className='flex gap-24px mob:flex-col'>
                        <div className='border-2 border-black w-full'>
                            <Img className='w-full object-cover' src="/assets/img/product-placeholder.png" />
                        </div>
                        <div className='flex flex-col gap-24px mob:flex-row mob:overflow-x-auto'>
                            <Img className='max-w-[200px] border-2 border-black' src="/assets/img/product-placeholder.png" />
                            <Img className='max-w-[200px] border-2 border-black' src="/assets/img/product-placeholder.png" />
                        </div>
                    </div>
                    <div className='mt-24px'>
                        <div className='font-nunito font-bold'>75313 AT-AT</div>
                        <div className='font-bold text-4xl'>Dom’s Charger</div>
                    </div>
                    <div className='mt-12px flex items-center justify-between w-full'>
                        <div className='flex gap-8px items-center'>
                            <div className='font-nunito font-bold'>2021</div>
                            <div className='h-16px w-16px bg-[#46BD0F] rounded-full'></div>
                        </div>
                        <div className='flex gap-16px'>
                            <Export size={24} />
                            <Heart size={24} />
                        </div>
                    </div>
                    <div className='mt-24px font-bold text-xl'>Forecast</div>
                    <div className='mt-16px font-nunito text-[#4D4D4D]'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non.</div>
                    <div className='border-2 border-black p-32px mt-20px'>
                        <div className='font-bold text-xl'>Set Pricing</div>
                        <div className='flex items-center justify-between w-full pb-16px border-b border-[#D0D4DB]'>
                            <div className='font-nunito'>Retail price</div>
                            <div className='flex items-center gap-16px'>
                                <div className='font-nunito font-semibold'>Free</div>
                                <div className='py-6px px-8px bg-[#F5F5F5] text-xs font-semibold font-nunito'>Medium accuracy</div>
                            </div>
                        </div>

                        <div className='font-bold text-xl mt-12px'>New/Sealed</div>
                        <div className='flex items-center justify-between w-full pb-16px border-b border-[#D0D4DB]'>
                            <div className='font-nunito'>Retail price</div>
                            <div className='flex items-center gap-16px'>
                                <div className='font-nunito font-semibold'>Free</div>
                                <div className='py-6px px-8px bg-[#F5F5F5] text-xs font-semibold font-nunito'>Medium accuracy</div>
                            </div>
                        </div>

                        <div className='font-bold text-xl mt-12px'>Set Pricing</div>
                        <div className='flex items-center justify-between w-full pb-16px border-b border-[#D0D4DB]'>
                            <div className='font-nunito'>Retail price</div>
                            <div className='flex items-center gap-16px'>
                                <div className='font-nunito font-semibold'>Free</div>
                                <div className='py-6px px-8px bg-[#F5F5F5] text-xs font-semibold font-nunito'>Medium accuracy</div>
                            </div>
                        </div>

                    </div>
                </div>
                <div className='w-full'>
                    <div className='border-2 border-black p-24px flex flex-col gap-12px'>
                        <div className='font-bold text-xl'>Set Details</div>

                        <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px'>
                            <div className='text-[#4D4D4D]'>Set number</div>
                            <div className='text-[#4D4D4D]'>30713-2</div>
                        </div>
                        <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px'>
                            <div className='text-[#4D4D4D]'>Name</div>
                            <div className='text-[#4D4D4D]'>...</div>
                        </div>
                        <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px'>
                            <div className='text-[#4D4D4D]'>Theme</div>
                            <div className='text-[#4D4D4D]'>...</div>
                        </div>
                        <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px'>
                            <div className='text-[#4D4D4D]'>Year</div>
                            <div className='text-[#4D4D4D]'>2024</div>
                        </div>
                        <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px'>
                            <div className='text-[#4D4D4D]'>Released</div>
                            <div className='text-[#4D4D4D]'>01/2024</div>
                        </div>
                        <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px'>
                            <div className='text-[#4D4D4D]'>Availability</div>
                            <div className='text-[#4D4D4D]'>Retail</div>
                        </div>
                        <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px'>
                            <div className='text-[#4D4D4D]'>Packaging</div>
                            <div className='text-[#4D4D4D]'>Paper</div>
                        </div>
                        <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px'>
                            <div className='text-[#4D4D4D]'>Pieces</div>
                            <div className='text-[#4D4D4D]'>43 (PPP 0,15)</div>
                        </div>
                        <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px'>
                            <div className='text-[#4D4D4D]'>Minifigs</div>
                            <div className='text-[#4D4D4D]'>1</div>
                        </div>
                        <div className='text-white font-bold px-12px py-8px bg-[#46BD0F] max-w-[136px]'>Availible at retail</div>
                        <Button href={"#"} icon={<Plus size={24} />}>Add to portfolio</Button>
                    </div>
                </div>
            </div>

            
        </AuthenticatedLayout>
    )
}

export default Product
