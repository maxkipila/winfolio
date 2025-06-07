import { ModalsContext } from '@/Components/contexts/ModalsContext'
import { t } from '@/Components/Translator'
import { MODALS } from '@/Fragments/Modals'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head } from '@inertiajs/react'
import { Check, LegoSmiley, Medal, X } from '@phosphor-icons/react'
import React, { useContext, useEffect } from 'react'

interface AwardCardProps extends Award {

}

function AwardCard(props: AwardCardProps) {
    const { category, category_id, earned, category_name, condition_type, conditions, created_at, description, id, name, product_id, required_count, required_percentage, required_value, updated_at, icon } = props
    return (
        <div className={`${earned ? "border-[#FFB400]" : "border-black"} border-2 bg-[#F5F5F5] w-full min-h-[250px] flex flex-col items-center justify-center px-24px`}>
            <div className='w-40px h-40px bg-white rounded-full flex items-center justify-center mb-8px'>
                {
                    earned ?
                        <Check size={24} />
                        :
                        <X size={24} />
                }
            </div>
            <div className='text-lg font-bold font-teko text-center'>{name}</div>
            <div className='font-medium font-teko text-center'>{description}</div>
        </div>
    )
}

interface Props {
    records: {
        best_purchase: {
            value: number,
            product: Product
        },
        highest_portfolio: number,
        most_items: number,
        worst_purchase: {
            value: number,
            product: Product
        }
    },
    awards: Array<Award>
}

function Award(props: Props) {
    const { awards, records } = props
    let { open } = useContext(ModalsContext)
    useEffect(() => {
        let unclaimed = awards?.filter((a) => a?.pivot?.is_claimed == false)
        if (unclaimed?.length > 0) {
            console.log('otviram')
            open(MODALS.UNCLAIMED_AWARDS, false, { awards: unclaimed })
        }
    }, [])


    return (
        <AuthenticatedLayout>
            <Head title="Awards | Winfolio" />
            <div className='w-2/3 mx-auto mob:w-full px-24px mob:px-0'>
                <div className='font-bold font-teko text-xl pt-48px px-24px mob:pt-16px'>{t('Moje rekordy')}</div>
                <div className='w-full flex gap-12px overflow-x-auto mob:px-24px mob:mt-12px'>

                    {
                        ((records?.best_purchase != null) || records?.highest_portfolio > 0 || records?.most_items > 0 || records?.worst_purchase != null) ?
                            <>
                                {
                                    <div className='w-full bg-[#F5F5F5] flex flex-col items-center justify-center gap-8px min-h-[190px] min-w-[110px] mob:min-w-[150px]'>
                                        <div className='w-40px h-40px rounded-full bg-white flex items-center justify-center'>
                                            <Medal size={24} />
                                        </div>
                                        <div className='font-bold font-nunito text-center'>{t('Nejlepší koupě')}</div>
                                        <div className='text-center'>{records?.best_purchase?.product?.name}</div>
                                        <div className='text-center'>{records?.best_purchase?.value}</div>
                                    </div>
                                }

                                <div className='w-full bg-[#F5F5F5] flex flex-col items-center justify-center gap-8px min-h-[190px] min-w-[110px] mob:min-w-[150px]'>
                                    <div className='w-40px h-40px rounded-full bg-white flex items-center justify-center'>
                                        <Medal size={24} />
                                    </div>
                                    <div className='font-bold font-nunito text-center'>{t('Hodnota portfolia')}</div>
                                    <div>{Math.floor(records?.highest_portfolio)}</div>
                                </div>

                                <div className='w-full bg-[#F5F5F5] flex flex-col items-center justify-center gap-8px min-h-[190px] min-w-[110px] mob:min-w-[150px]'>
                                    <div className='w-40px h-40px rounded-full bg-white flex items-center justify-center'>
                                        <Medal size={24} />
                                    </div>
                                    <div className='font-bold font-nunito text-center'>{t('Množství položek')}</div>
                                    <div>{Math.floor(records?.most_items)}</div>
                                </div>

                                <div className='w-full bg-[#F5F5F5] flex flex-col items-center justify-center gap-8px min-h-[190px] min-w-[110px] mob:min-w-[150px]'>
                                    <div className='w-40px h-40px rounded-full bg-white flex items-center justify-center'>
                                        <Medal size={24} />
                                    </div>
                                    <div className='font-bold font-nunito text-center'>{t('Největší přehmat')}</div>
                                    <div>{records?.worst_purchase?.product?.name}</div>
                                    <div>{records?.worst_purchase?.value}</div>
                                </div>
                            </>
                            :
                            <div className='bg-[#FEF4E1] p-16px flex items-center gap-12px mt-12px w-full'>
                                <LegoSmiley className='mb-4px' size={24} />
                                <div className='font-nunito'>{t('Zatím nemáte žádné rekordy…')}</div>
                            </div>
                    }

                </div>
                <div className='font-bold font-teko text-xl mt-48px px-24px'>{t('Odznaky')}</div>
                {
                    awards?.length > 0 ?
                        <div className='mt-12px grid grid-cols-3 gap-12px pb-48px mob:grid-cols-2 px-24px'>
                            {
                                awards?.map((a) =>
                                    <AwardCard {...a} />
                                )
                            }
                        </div>
                        :
                        <div className='bg-[#FEF4E1] p-16px flex items-center gap-12px mt-12px px-24px'>
                            <LegoSmiley className='mb-4px' size={24} />
                            <div className='font-nunito'>{t('Zatím nemáte žádné odznaky…')}</div>
                        </div>
                }
            </div>
        </AuthenticatedLayout>
    )
}

export default Award
