import { PaperPlaneTilt, Star } from '@phosphor-icons/react'
import React, { useContext } from 'react'
import { Button } from './UI/Button'
import { ModalsContext } from '@/Components/contexts/ModalsContext'
import { MODALS } from './Modals'

interface Props { }

function ReviewCard(props: Props) {
    const { } = props
    let { open } = useContext(ModalsContext)
    return (
        <div className='border-2 border-black p-32px mt-32px'>
            <div className='font-bold'>Fauna’s House Reviews</div>
            <div className='flex items-center gap-16px mt-16px'>
                <div className='flex gap-40px mob:flex-col mob:gap-12px'>
                    <div>
                        <div className='flex gap-4px'>
                            <div className='grid'>
                                <Star className='col-start-1 row-start-1' weight='fill' color='#F7AA1A' />
                                <Star className='col-start-1 row-start-1' weight='bold' />
                            </div>
                            <div className='grid'>
                                <Star className='col-start-1 row-start-1' weight='fill' color='#F7AA1A' />
                                <Star className='col-start-1 row-start-1' weight='bold' />
                            </div>
                            <div className='grid'>
                                <Star className='col-start-1 row-start-1' weight='fill' color='#F7AA1A' />
                                <Star className='col-start-1 row-start-1' weight='bold' />
                            </div>
                            <div className='grid'>
                                <Star className='col-start-1 row-start-1' weight='fill' color='#F7AA1A' />
                                <Star className='col-start-1 row-start-1' weight='bold' />
                            </div>
                            <div className='grid'>
                                <Star className='col-start-1 row-start-1' weight='bold' />
                            </div>
                        </div>
                        <div className='text-[#4D4D4D]'>40 ratings</div>
                    </div>
                    <div className='flex gap-16px'>
                        <div className='font-bold font-teko text-5xl'>4.7</div>
                        <div className='w-full'>
                            <div className='flex items-center justify-between w-full gap-24px'>
                                <div className='text-[#4D4D4D]'>Collectors</div>
                                <div className='grid w-110px'>
                                    <div className='col-start-1 row-start-1 h-8px w-full rounded-[4px] bg-[#F5F5F5]'></div>
                                    <div className='col-start-1 row-start-1 h-8px w-[90%] rounded-[4px] bg-[#F7AA1A]'></div>
                                </div>
                            </div>
                            <div className='flex items-center justify-between w-full gap-24px'>
                                <div className='text-[#4D4D4D]'>Collectors</div>
                                <div className='grid w-110px'>
                                    <div className='col-start-1 row-start-1 h-8px w-full rounded-[4px] bg-[#F5F5F5]'></div>
                                    <div className='col-start-1 row-start-1 h-8px w-[35%] rounded-[4px] bg-[#F7AA1A]'></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div className='max-w-[180px] mob:max-w-full mt-16px'>
                <Button onClick={(e) => { e.preventDefault(); open(MODALS.REVIEW, false) }} href={"#"} icon={<PaperPlaneTilt size={24} />}>Submit Reviews</Button>
            </div>
        </div>
    )
}

export default ReviewCard
