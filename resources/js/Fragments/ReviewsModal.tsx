import { ModalsContext } from '@/Components/contexts/ModalsContext';
import { PaperPlaneTilt, Star } from '@phosphor-icons/react';
import React, { useContext, useState } from 'react'
import Form from './forms/Form';
import { useForm } from '@inertiajs/react';
import TextField from './forms/inputs/TextField';
import TextArea from './forms/inputs/TextArea';
import { Button } from './UI/Button';

function ReviewLine() {

    return (
        <div className='border-t border-[#E6E6E6] py-24px'>
            <div className='flex justify-between items-center'>
                <div className='font-bold text-lg'>Michael Coleman</div>
                <div className='text-[#4D4D4D] font-nunito'>20 days ago</div>
            </div>
            <div className='flex gap-4px mt-16px'>
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
            <div className='font-nunito text-[#4D4D4D]'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non.</div>
        </div>
    )
}


interface Props { }

function ReviewsModal(props: Props) {
    const { } = props
    let { close } = useContext(ModalsContext)
    let [collected, setCollected] = useState(true)
    const form = useForm({});
    const { data } = form;
    return (
        <div onClick={() => { close() }} className="bg-black bg-opacity-80 fixed top-0 left-0 w-full h-screen items-center justify-center mob:block mob:max-h-full flex z-max p-24px mob:pb-0">
            <div onClick={(e) => { e.stopPropagation(); }} className='bg-white border-2 border-black min-w-2/3 mob:w-full mob:max-h-90vh overflow-y-auto'>
                <div className='flex divide-x-2 divide-black mob:flex-col mob:divide-x-0'>
                    <div className='w-full p-24px'>
                        <div className='font-bold text-3xl'>Averange Rating</div>
                        <div className='flex items-center gap-8px'>
                            <div className='text-[#4D4D4D] font-nunito'>4.7</div>
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
                        </div>

                        <div className='flex items-center gap-16px mt-16px'>
                            <div className='font-nunito text-[#4D4D4D]'>5</div>
                            <div className='grid w-full'>
                                <div className='w-full h-8px rounded-[4px] bg-[#F5F5F5] col-start-1 row-start-1'></div>
                                <div className='w-[90%] h-8px rounded-[4px] bg-[#F7AA1A] col-start-1 row-start-1'></div>
                            </div>
                            <div className='font-nunito text-[#4D4D4D]'>90%</div>
                        </div>
                        <div className='flex items-center gap-16px mt-16px'>
                            <div className='font-nunito text-[#4D4D4D]'>4</div>
                            <div className='grid w-full'>
                                <div className='w-full h-8px rounded-[4px] bg-[#F5F5F5] col-start-1 row-start-1'></div>
                                <div className='w-[60%] h-8px rounded-[4px] bg-[#F7AA1A] col-start-1 row-start-1'></div>
                            </div>
                            <div className='font-nunito text-[#4D4D4D]'>60%</div>
                        </div>
                        <div className='flex items-center gap-16px mt-16px'>
                            <div className='font-nunito text-[#4D4D4D]'>3</div>
                            <div className='grid w-full'>
                                <div className='w-full h-8px rounded-[4px] bg-[#F5F5F5] col-start-1 row-start-1'></div>
                                <div className='w-[40%] h-8px rounded-[4px] bg-[#F7AA1A] col-start-1 row-start-1'></div>
                            </div>
                            <div className='font-nunito text-[#4D4D4D]'>40%</div>
                        </div>
                        <div className='flex items-center gap-16px mt-16px'>
                            <div className='font-nunito text-[#4D4D4D]'>2</div>
                            <div className='grid w-full'>
                                <div className='w-full h-8px rounded-[4px] bg-[#F5F5F5] col-start-1 row-start-1'></div>
                                <div className='w-[30%] h-8px rounded-[4px] bg-[#F7AA1A] col-start-1 row-start-1'></div>
                            </div>
                            <div className='font-nunito text-[#4D4D4D]'>30%</div>
                        </div>
                        <div className='flex items-center gap-16px mt-16px'>
                            <div className='font-nunito text-[#4D4D4D]'>1</div>
                            <div className='grid w-full'>
                                <div className='w-full h-8px rounded-[4px] bg-[#F5F5F5] col-start-1 row-start-1'></div>
                                <div className='w-[0%] h-8px rounded-[4px] bg-[#F7AA1A] col-start-1 row-start-1'></div>
                            </div>
                            <div className='font-nunito text-[#4D4D4D]'>0%</div>
                        </div>

                    </div>
                    <div className='w-full p-24px'>
                        <div className='font-bold text-3xl whitespace-nowrap'>Submit Your Review</div>
                        <div className='flex items-center gap-8px'>
                            <div className='text-[#4D4D4D] font-nunito whitespace-nowrap'>Add Your Rating</div>
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
                        </div>
                        <div className='w-full flex mt-12px'>
                            <div className={`border-b-2 pb-12px font-bold text-lg w-full ${collected ? "border-black" : "border-[#E6E6E6] text-[#E6E6E6]"}`}>
                                Collected
                            </div>
                            <div className={`border-b-2 pb-12px font-bold text-lg w-full ${!collected ? "border-black" : "border-[#E6E6E6] text-[#E6E6E6]"}`}>
                                Invested in
                            </div>
                        </div>
                        <Form form={form} className='mt-12px'>
                            <TextArea label={'Write Your Review…'} placeholder='Write Your Review…' name="text" />
                        </Form>
                        <div className='max-w-[175px] mt-40px'>
                            <Button href="#" icon={<PaperPlaneTilt weight='bold' size={24} />}>Submit Reviews</Button>
                        </div>
                    </div>
                </div>
                <div className='p-24px border-t-2 border-black'>
                    <div className='font-bold text-3xl'>Customer Feedbacks</div>
                    <ReviewLine />
                    <ReviewLine />
                    <ReviewLine />
                </div>
            </div>
        </div>
    )
}

export default ReviewsModal
