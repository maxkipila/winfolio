import Img from '@/Components/Image'
import PointsGraph from '@/Fragments/PointsGraph'
import ProductCard from '@/Fragments/ProductCard'
import PromotionalCard from '@/Fragments/PromotionalCard'
import ReviewCard from '@/Fragments/ReviewCard'
import { Button } from '@/Fragments/UI/Button'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { ArrowRight, Basket, Export, Heart, Lock, Plus, Question } from '@phosphor-icons/react'
import React, { useState } from 'react'

interface Props { }

function Product(props: Props) {
    const { } = props
    let [quickBuy, setQuickBuy] = useState(true)
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
                        <div className='font-bold text-xl mb-12px'>Set Pricing</div>
                        <div className='flex items-center justify-between w-full pb-16px border-b border-[#D0D4DB]'>
                            <div className='font-nunito'>Retail price</div>
                            <div className='flex items-center gap-16px'>
                                <div className='font-nunito font-semibold text-[#4D4D4D]'>Free</div>
                                <div className='py-6px px-8px bg-[#F5F5F5] text-xs font-semibold font-nunito text-[#4D4D4D]'>Medium accuracy</div>
                            </div>
                        </div>

                        <div className='font-bold text-xl mt-12px flex items-center gap-12px mb-12px'>
                            <div>New/Sealed</div>
                            <Question size={24} />
                        </div>
                        <div className='flex items-center justify-between mb-8px'>
                            <div className='font-nunito flex items-center'>Value</div>
                            <div className='font-semibold font-nunito text-[#4D4D4D]'>$22.36</div>
                        </div>
                        <div className='flex items-center justify-between w-full pb-16px border-b border-[#D0D4DB]'>
                            <div className='font-nunito flex items-center gap-12px'>
                                <div>90-day change</div>
                                <Question size={24} />
                            </div>
                            <div className='flex items-center gap-16px'>
                                <div className='py-6px px-8px bg-[#F5F5F5] text-xs font-semibold font-nunito text-[#4D4D4D]'>-3.09%</div>
                            </div>
                        </div>

                        <div className='font-bold text-xl mt-12px gap-12px flex items-center mb-12px'>
                            <div>Used</div>
                            <Question size={24} />
                        </div>
                        <div className='flex items-center justify-between w-full'>
                            <div className='font-nunito'>Value</div>
                            <div className='flex items-center gap-16px'>
                                <div className='py-6px px-8px  font-semibold font-nunito text-[#4D4D4D]'>$18.56</div>
                            </div>
                        </div>
                        <div className='flex items-center justify-between w-full pb-16px'>
                            <div className='font-nunito flex items-center gap-12px'>
                                <div>Range</div>
                                <Question size={24} />
                            </div>
                            <div className='flex items-center gap-16px'>
                                <div className='py-6px px-8px  font-semibold font-nunito text-[#4D4D4D]'>$18.21-$20.66</div>
                            </div>
                        </div>

                    </div>
                    <PointsGraph />
                    <div className='mt-32px border-2 border-black p-32px'>
                        <div className='font-bold text-xl w-full pb-16px border-b border-black'>Set Predictions</div>
                        <div className='mt-16px font-bold text-lg'>New/Sealed</div>
                        <div className='mt-16px'>
                            <div className='w-full flex justify-between items-center'>
                                <div className='flex gap-8px items-center'>
                                    <div className='text-[#4D4D4D]'>Today’s value</div>
                                    <Question size={24} />
                                </div>
                                <div className='text-[#4D4D4D]'>$22.36</div>
                            </div>

                            <div className='w-full flex justify-between items-center mt-8px'>
                                <div className='flex gap-8px items-center'>
                                    <div className='text-[#4D4D4D]'>1 year forecast</div>
                                    <Question size={24} />
                                </div>
                                <div className='flex gap-8px items-center'>
                                    <div className='text-[#4D4D4D]'>$22.36</div>
                                    <div className='py-6px px-8px bg-[#F5F5F5] text-xs font-semibold font-nunito text-[#4D4D4D]'>Medium accuracy</div>
                                </div>
                            </div>

                            <div className='w-full flex justify-between items-center mt-8px'>
                                <div className='flex gap-8px items-center'>
                                    <div className='text-[#4D4D4D]'>5 year forecast</div>
                                    <Question size={24} />
                                </div>
                                <div className='text-[#4D4D4D]'>$22.36</div>

                            </div>

                            <div className='flex gap-8px w-full mt-16px'>
                                <div className='w-full h-8px rounded-[4px] bg-[#ED2E1B]'></div>
                                <div className='w-full h-8px rounded-[4px] bg-[#F7AA1A] relative'>
                                    <div className='absolute -top-5px left-[50%] transform -translate-x-1/2 h-18px w-18px border border-black rounded-[5px] bg-white'>
                                        <div className='w-12px h-12px bg-black mx-auto mt-2px rounded-[5px]'></div>
                                    </div>
                                </div>
                                <div className='w-full h-8px rounded-[4px] bg-[#46BD0F]'></div>
                            </div>
                            <div className='w-full flex justify-between items-center mt-16px'>
                                <div className='font-semibold text-[#4D4D4D] font-nunito'>$32</div>
                                <div className='font-semibold text-[#4D4D4D]  font-nunito'>$33</div>
                            </div>
                            <div className='mt-16px text-[#4D4D4D] font-semibold font-nunito'>6508941 Faunas’s House is projected to be valued between $32 - $33 within five years.</div>
                        </div>
                    </div>
                    <div className='mt-40px font-bold text-xl'>Claim</div>
                    <div className='mt-16px font-nunito text-[#4D4D4D]'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non.</div>

                    <div className='mt-40px bg-[#F5F5F5] p-24px'>
                        <div className='font-bold text-xl'>Buy this set</div>
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
                        </div>

                        <div className='mt-40px font-bold text-xl'>Minifigs</div>
                        <div className='mt-16px text-[#4D4D4D] font-nunito'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</div>
                        <div className='flex items-center gap-12px mob:overflow-y-auto'>
                            <div className='border-2 border-black flex flex-col gap-8px bg-white py-12px w-full px-50px'>
                                <div className='text-center font-bold font-nunito'>Name</div>
                                <Img src="/assets/img/thor.png" />
                            </div>
                            <div className='border-2 border-black flex flex-col gap-8px bg-white py-12px w-full px-50px'>
                                <div className='text-center font-bold font-nunito'>Name</div>
                                <Img src="/assets/img/thor.png" />
                            </div>
                            <div className='border-2 border-black flex flex-col gap-8px bg-white py-12px w-full px-50px'>
                                <div className='text-center font-bold font-nunito'>Name</div>
                                <Img src="/assets/img/thor.png" />
                            </div>
                            <div className='border-2 border-black flex flex-col gap-8px bg-white py-12px w-full px-50px'>
                                <div className='text-center font-bold font-nunito'>Name</div>
                                <Img src="/assets/img/thor.png" />
                            </div>
                        </div>
                    </div>
                    <div className='mt-40px font-bold text-xl'>Other sets in Theme</div>
                    <div className='grid grid-cols-2 mob:grid-cols-1 mt-16px gap-12px'>
                        <ProductCard wide />
                        <ProductCard wide />
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
                    <div className='mt-32px border-2 border-black p-32px'>
                        <div className='font-bold font-teko'>Set Facts</div>
                        <div className='mt-16px flex gap-16px'>
                            <div className='w-8px h-8px rounded-full bg-black flex-shrink-0 mt-8px'></div>
                            <div className='text-[#4D4D4D]'>The Make & Take event were be available in UK and selected European stores on March 9th between 10:00 – 12:00 and on March 10th between 12:00 – 14:00.</div>
                        </div>
                    </div>
                    <ReviewCard />
                    <PromotionalCard />
                </div>
            </div>


        </AuthenticatedLayout>
    )
}

export default Product
