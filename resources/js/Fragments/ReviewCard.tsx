import { PaperPlaneTilt, Star } from '@phosphor-icons/react'
import React, { useContext } from 'react'
import { Button } from './UI/Button'
import { ModalsContext } from '@/Components/contexts/ModalsContext'
import { MODALS } from './Modals'
import { t } from '@/Components/Translator'

interface Props {
    product: Product
}

function ReviewCard(props: Props) {
    const { product } = props
    let { open } = useContext(ModalsContext)
    console.log(product.reviews?.flatMap((r) => r.rating))
    let ratingValues = product.reviews?.flatMap((r) => r.rating)
    let reviewValue = 0
    ratingValues?.map((v) => reviewValue += v)
    let colNumber = 0
    let invNumber = 0
    product?.reviews?.map((re) => {
        if (re.role == "both") {
            colNumber += 1
            invNumber += 1
        } else if (re.role == "investor") {
            invNumber += 1
        } else {
            colNumber += 1
        }
    })
    return (
        <div className='border-2 border-black p-32px mt-32px'>
            <div className='font-bold'>{product?.name} {t('Reviews')}</div>
            <div className='flex items-center gap-16px mt-16px'>
                <div className='flex gap-24px mob:flex-col mob:gap-12px'>
                    <div>
                        <div className='flex gap-4px'>
                            <div className='grid'>

                                {
                                    Math.floor(reviewValue / ratingValues?.length) > 0 &&
                                    <Star size={24} className='col-start-1 row-start-1' weight='fill' color='#FFB400' />
                                }
                                <Star size={24} className='col-start-1 row-start-1' weight='bold' />
                            </div>
                            <div className='grid'>

                                {
                                    Math.floor(reviewValue / ratingValues?.length) > 1 &&
                                    <Star size={24} className='col-start-1 row-start-1' weight='fill' color='#FFB400' />
                                }
                                <Star size={24} className='col-start-1 row-start-1' weight='bold' />

                            </div>
                            <div className='grid'>

                                {
                                    Math.floor(reviewValue / ratingValues?.length) > 2 &&
                                    <Star size={24} className='col-start-1 row-start-1' weight='fill' color='#FFB400' />
                                }
                                <Star size={24} className='col-start-1 row-start-1' weight='bold' />
                            </div>
                            <div className='grid'>

                                {
                                    Math.floor(reviewValue / ratingValues?.length) > 3 &&
                                    <Star size={24} className='col-start-1 row-start-1' weight='fill' color='#FFB400' />
                                }
                                <Star size={24} className='col-start-1 row-start-1' weight='bold' />
                            </div>
                            <div className='grid'>

                                {
                                    Math.floor(reviewValue / ratingValues?.length) > 4 &&
                                    <Star size={24} className='col-start-1 row-start-1' weight='fill' color='#FFB400' />
                                }
                                <Star size={24} className='col-start-1 row-start-1' weight='bold' />
                            </div>
                        </div>
                        <div className='text-[#4D4D4D] mt-8px'>{product?.reviews?.length ?? 0} {t('ratings')}</div>
                    </div>
                    <div className='flex gap-16px'>
                        <div className='font-bold font-teko text-5xl'>{reviewValue ? Math.floor(reviewValue / ratingValues?.length) : 0}</div>
                        <div className='w-full'>
                            <div className='flex items-center justify-between w-full gap-24px'>
                                <div className='text-[#4D4D4D]'>{t('Collectors')}</div>
                                <div className='grid w-110px'>
                                    <div className='col-start-1 row-start-1 h-8px w-full rounded-[4px] bg-[#F5F5F5]'></div>
                                    <div className={`col-start-1 row-start-1 h-8px w-[${(colNumber / product?.reviews?.length) * 100}%] rounded-[4px] bg-[#FFB400]`}></div>
                                </div>
                            </div>
                            <div className='flex items-center justify-between w-full gap-24px'>
                                <div className='text-[#4D4D4D]'>{t('Investors')}</div>
                                <div className='grid w-110px'>
                                    <div className='col-start-1 row-start-1 h-8px w-full rounded-[4px] bg-[#F5F5F5]'></div>
                                    <div className={`col-start-1 row-start-1 h-8px w-[${(invNumber / product?.reviews?.length) * 100}%] rounded-[4px] bg-[#FFB400]`}></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div className='max-w-[180px] mob:max-w-full mt-16px'>
                <Button onClick={(e) => { e.preventDefault(); open(MODALS.REVIEW, false, { product: product }) }} href={"#"} icon={<PaperPlaneTilt size={24} />}>Submit Reviews</Button>
            </div>
        </div>
    )
}

export default ReviewCard
