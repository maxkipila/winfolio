import { ModalsContext } from '@/Components/contexts/ModalsContext';
import { PaperPlaneTilt, Star } from '@phosphor-icons/react';
import React, { useContext, useState } from 'react'
import Form from './forms/Form';
import { useForm } from '@inertiajs/react';
import TextField from './forms/inputs/TextField';
import TextArea from './forms/inputs/TextArea';
import { Button } from './UI/Button';
import moment from 'moment';
import ReviewCard from './ReviewCard';
import { MODALS } from './Modals';
import usePageProps from '@/hooks/usePageProps';
import { t } from '@/Components/Translator';

interface ReviewLineProps extends Review {

}

function ReviewLine(props: ReviewLineProps) {
    const { comment, id, rating, role, user, created_at } = props
    return (
        <div className='border-t border-[#E6E6E6] py-24px'>
            <div className='flex justify-between items-center'>
                <div className='font-bold text-lg'>{user?.first_name} {user?.last_name}</div>
                <div className='text-[#4D4D4D] font-nunito'>{moment(created_at).format('DD.MM.YYYY')}</div>
            </div>
            <div className='flex gap-4px mt-16px'>
                <div className='grid'>
                    {
                        rating > 0 &&
                        <Star className='col-start-1 row-start-1' weight='fill' color='#F7AA1A' />
                    }
                    <Star className='col-start-1 row-start-1' weight='bold' />
                </div>
                <div className='grid'>
                    {
                        rating > 1 &&
                        <Star className='col-start-1 row-start-1' weight='fill' color='#F7AA1A' />
                    }
                    <Star className='col-start-1 row-start-1' weight='bold' />
                </div>
                <div className='grid'>
                    {
                        rating > 2 &&
                        <Star className='col-start-1 row-start-1' weight='fill' color='#F7AA1A' />
                    }
                    <Star className='col-start-1 row-start-1' weight='bold' />
                </div>
                <div className='grid'>
                    {
                        rating > 3 &&
                        <Star className='col-start-1 row-start-1' weight='fill' color='#F7AA1A' />
                    }
                    <Star className='col-start-1 row-start-1' weight='bold' />
                </div>
                <div className='grid'>
                    {
                        rating > 4 &&
                        <Star className='col-start-1 row-start-1' weight='fill' color='#F7AA1A' />
                    }
                    <Star className='col-start-1 row-start-1' weight='bold' />
                </div>
            </div>
            <div className='font-nunito text-[#4D4D4D]'>{comment}</div>
        </div>
    )
}


interface Props {
    product: Product
}

function ReviewsModal(props: Props) {
    const { product } = props
    let { close, open } = useContext(ModalsContext)
    let [collected, setCollected] = useState(true)
    const { auth } = usePageProps<{ auth: { user: User } }>();
    const form = useForm({
        rating: 0,
        role: "collector",
        product_id: product?.id,
        user_id: auth?.user?.id
    });
    const { data, setData, post } = form;
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
    let five = product?.reviews?.filter((r) => r.rating == 5)
    let four = product?.reviews?.filter((r) => r.rating == 4)
    let three = product?.reviews?.filter((r) => r.rating == 3)
    let two = product?.reviews?.filter((r) => r.rating == 2)
    let one = product?.reviews?.filter((r) => r.rating == 1)

    function submit() {
        post(route('submit_review'), {
            onSuccess: () => { open(MODALS.SUCCESS, false) }
        })
    }
    return (
        <div onClick={() => { close() }} className="bg-black bg-opacity-80 fixed top-0 left-0 w-full h-screen items-center justify-center mob:block mob:max-h-full flex z-max p-24px mob:pb-0">
            <div onClick={(e) => { e.stopPropagation(); }} className='bg-white border-2 border-black min-w-2/3 mob:w-full mob:max-h-90vh overflow-y-auto'>
                <div className='flex divide-x-2 divide-black mob:flex-col mob:divide-x-0'>
                    <div className='w-full p-24px'>
                        <div className='font-bold text-3xl'>Averange Rating</div>
                        <div className='flex items-center gap-8px'>
                            <div className='text-[#4D4D4D] font-nunito'>{reviewValue ? Math.floor(reviewValue / ratingValues?.length) : 0}</div>
                            <div className='flex gap-4px'>
                                <div className='grid'>

                                    {
                                        Math.floor(reviewValue / ratingValues?.length) > 0 &&
                                        <Star className='col-start-1 row-start-1' weight='fill' color='#F7AA1A' />
                                    }
                                    <Star className='col-start-1 row-start-1' weight='bold' />
                                </div>
                                <div className='grid'>

                                    {
                                        Math.floor(reviewValue / ratingValues?.length) > 1 &&
                                        <Star className='col-start-1 row-start-1' weight='fill' color='#F7AA1A' />
                                    }
                                    <Star className='col-start-1 row-start-1' weight='bold' />

                                </div>
                                <div className='grid'>

                                    {
                                        Math.floor(reviewValue / ratingValues?.length) > 2 &&
                                        <Star className='col-start-1 row-start-1' weight='fill' color='#F7AA1A' />
                                    }
                                    <Star className='col-start-1 row-start-1' weight='bold' />
                                </div>
                                <div className='grid'>

                                    {
                                        Math.floor(reviewValue / ratingValues?.length) > 3 &&
                                        <Star className='col-start-1 row-start-1' weight='fill' color='#F7AA1A' />
                                    }
                                    <Star className='col-start-1 row-start-1' weight='bold' />
                                </div>
                                <div className='grid'>

                                    {
                                        Math.floor(reviewValue / ratingValues?.length) > 4 &&
                                        <Star className='col-start-1 row-start-1' weight='fill' color='#F7AA1A' />
                                    }
                                    <Star className='col-start-1 row-start-1' weight='bold' />
                                </div>
                            </div>
                        </div>

                        <div className='flex items-center gap-16px mt-16px'>
                            <div className='font-nunito text-[#4D4D4D]'>5</div>
                            <div className='grid w-full'>
                                <div className='w-full h-8px rounded-[4px] bg-[#F5F5F5] col-start-1 row-start-1'></div>
                                <div style={{ width: `${five?.length > 0 ? (five?.length / product?.reviews?.length) * 100 : 0}%` }} className={`h-8px rounded-[4px] bg-[#F7AA1A] col-start-1 row-start-1`}></div>
                            </div>
                            <div className='font-nunito text-[#4D4D4D]'>{five?.length > 0 ? Math.floor((five?.length / product?.reviews?.length) * 100) : 0}%</div>
                        </div>
                        <div className='flex items-center gap-16px mt-16px'>
                            <div className='font-nunito text-[#4D4D4D]'>4</div>
                            <div className='grid w-full'>
                                <div className='w-full h-8px rounded-[4px] bg-[#F5F5F5] col-start-1 row-start-1'></div>
                                <div style={{ width: `${four?.length > 0 ? (four?.length / product?.reviews?.length) * 100 : 0}%` }} className={` h-8px rounded-[4px] bg-[#F7AA1A] col-start-1 row-start-1`}></div>
                            </div>
                            <div className='font-nunito text-[#4D4D4D]'>{four?.length > 0 ? Math.floor((four?.length / product?.reviews?.length) * 100) : 0}%</div>
                        </div>
                        <div className='flex items-center gap-16px mt-16px'>
                            <div className='font-nunito text-[#4D4D4D]'>3</div>
                            <div className='grid w-full'>
                                <div className='w-full h-8px rounded-[4px] bg-[#F5F5F5] col-start-1 row-start-1'></div>
                                <div style={{ width: `${three?.length > 0 ? (three?.length / product?.reviews?.length) * 100 : 0}%` }} className={` h-8px rounded-[4px] bg-[#F7AA1A] col-start-1 row-start-1`}></div>
                            </div>
                            <div className='font-nunito text-[#4D4D4D]'>{three?.length > 0 ? Math.floor((three?.length / product?.reviews?.length) * 100) : 0}%</div>
                        </div>
                        <div className='flex items-center gap-16px mt-16px'>
                            <div className='font-nunito text-[#4D4D4D]'>2</div>
                            <div className='grid w-full'>
                                <div className='w-full h-8px rounded-[4px] bg-[#F5F5F5] col-start-1 row-start-1'></div>
                                <div style={{ width: `${two?.length > 0 ? (two?.length / product?.reviews?.length) * 100 : 0}%` }} className={` h-8px rounded-[4px] bg-[#F7AA1A] col-start-1 row-start-1`}></div>
                            </div>
                            <div className='font-nunito text-[#4D4D4D]'>{two?.length > 0 ? Math.floor(two?.length / product?.reviews?.length) * 100 : 0}%</div>
                        </div>
                        <div className='flex items-center gap-16px mt-16px'>
                            <div className='font-nunito text-[#4D4D4D]'>1</div>
                            <div className='grid w-full'>
                                <div className='w-full h-8px rounded-[4px] bg-[#F5F5F5] col-start-1 row-start-1'></div>
                                <div style={{ width: `${one?.length > 0 ? (one?.length / product?.reviews?.length) * 100 : 0}%` }} className={` h-8px rounded-[4px] bg-[#F7AA1A] col-start-1 row-start-1`}></div>
                            </div>
                            <div className='font-nunito text-[#4D4D4D]'>{one?.length > 0 ? Math.floor((one?.length / product?.reviews?.length) * 100) : 0}%</div>
                        </div>

                    </div>
                    <div className='w-full p-24px'>
                        <div className='font-bold text-3xl whitespace-nowrap'>Submit Your Review</div>
                        <div className='flex items-center gap-8px'>
                            <div className='text-[#4D4D4D] font-nunito whitespace-nowrap'>Add Your Rating</div>
                            <div className='flex gap-4px'>
                                <div onClick={() => { setData('rating', 1) }} className='grid cursor-pointer'>
                                    {
                                        data['rating'] > 0 &&
                                        <Star className='col-start-1 row-start-1' weight='fill' color='#F7AA1A' />
                                    }
                                    <Star className='col-start-1 row-start-1' weight='bold' />
                                </div>
                                <div onClick={() => { setData('rating', 2) }} className='grid cursor-pointer'>
                                    {
                                        data['rating'] > 1 &&
                                        <Star className='col-start-1 row-start-1' weight='fill' color='#F7AA1A' />
                                    }
                                    <Star className='col-start-1 row-start-1' weight='bold' />
                                </div>
                                <div onClick={() => { setData('rating', 3) }} className='grid cursor-pointer'>
                                    {
                                        data['rating'] > 2 &&
                                        <Star className='col-start-1 row-start-1' weight='fill' color='#F7AA1A' />
                                    }
                                    <Star className='col-start-1 row-start-1' weight='bold' />
                                </div>
                                <div onClick={() => { setData('rating', 4) }} className='grid cursor-pointer'>
                                    {
                                        data['rating'] > 3 &&
                                        <Star className='col-start-1 row-start-1' weight='fill' color='#F7AA1A' />
                                    }
                                    <Star className='col-start-1 row-start-1' weight='bold' />
                                </div>
                                <div onClick={() => { setData('rating', 5) }} className='grid cursor-pointer'>
                                    {
                                        data['rating'] > 4 &&
                                        <Star className='col-start-1 row-start-1' weight='fill' color='#F7AA1A' />
                                    }
                                    <Star className='col-start-1 row-start-1' weight='bold' />
                                </div>
                            </div>
                        </div>
                        <div className='w-full flex mt-12px'>
                            <div onClick={() => { setCollected(true); setData('role', 'collector') }} className={`cursor-pointer border-b-2 pb-12px font-bold text-lg w-full ${collected ? "border-black" : "border-[#E6E6E6] text-[#E6E6E6]"}`}>
                                Collected
                            </div>
                            <div onClick={() => { setCollected(false); setData('role', 'investor') }} className={`cursor-pointer border-b-2 pb-12px font-bold text-lg w-full ${!collected ? "border-black" : "border-[#E6E6E6] text-[#E6E6E6]"}`}>
                                Invested in
                            </div>
                        </div>
                        <Form form={form} className='mt-12px'>
                            <TextArea label={'Write Your Review…'} placeholder='Write Your Review…' name="text" />
                        </Form>
                        <div className='max-w-[175px] mt-40px'>
                            <Button onClick={(e) => { e.preventDefault(); submit() }} href="#" icon={<PaperPlaneTilt weight='bold' size={24} />}>Submit Reviews</Button>
                        </div>
                    </div>
                </div>
                <div className='p-24px border-t-2 border-black'>
                    <div className='font-bold text-3xl'>Customer Feedbacks</div>
                    {
                        product?.reviews?.length > 0 ?
                        product?.reviews?.map((r) =>
                            <ReviewLine {...r} />
                        )
                        :
                        <div className='mx-auto w-full font-bold text-2xl text-center'>
                            {t('Tento produkt nebyl zatím recenzován.')}
                        </div>
                    }
                </div>
            </div>
        </div>
    )
}

export default ReviewsModal
