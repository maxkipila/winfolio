import { t } from '@/Components/Translator'
import Form from '@/Fragments/forms/Form'
import Toggle from '@/Fragments/forms/inputs/Toggle'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import ProfileLayout from '@/Layouts/ProfileLayout'
import { useForm } from '@inertiajs/react'
import React from 'react'

interface Props { }

function Notifications(props: Props) {
    const { } = props
    const form = useForm({});
    const {data} = form;
    return (
        <AuthenticatedLayout>
            <ProfileLayout>
                <div className='w-full p-24px'>
                    <div className='w-full text-center font-bold text-xl'>{t('Notifikace')}</div>
                    <Form className='mt-32px flex flex-col gap-16px' form={form}>
                        <div className='border-2 border-black flex p-16px bg-[#F5F5F5] justify-between'>
                            <div>
                                <div className='font-bold font-nunito'>Název upozornění 1</div>
                                <div className='text-[#4D4D4D] font-nunito mt-4px'>Odebírat newsletter</div>
                            </div>
                            <Toggle name="notification_1" />
                        </div>
                        <div className='border-2 border-black flex p-16px bg-[#F5F5F5] justify-between'>
                            <div>
                                <div className='font-bold font-nunito'>Název upozornění 1</div>
                                <div className='text-[#4D4D4D] font-nunito mt-4px'>Odebírat newsletter</div>
                            </div>
                            <Toggle name="notification_2" />
                        </div>
                        <div className='border-2 border-black flex p-16px bg-[#F5F5F5] justify-between'>
                            <div>
                                <div className='font-bold font-nunito'>Název upozornění 1</div>
                                <div className='text-[#4D4D4D] font-nunito mt-4px'>Odebírat newsletter</div>
                            </div>
                            <Toggle name="notification_3" />
                        </div>
                    </Form>
                </div>
            </ProfileLayout>
        </AuthenticatedLayout>
    )
}

export default Notifications
