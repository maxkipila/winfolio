import PrimaryButton from '@/Components/PrimaryButton'
import SecondaryButton from '@/Components/SecondaryButton'
import Breadcrumbs from '@/Fragments/forms/Breadcrumbs'
import { CSubmitButton } from '@/Fragments/forms/Buttons/CSubmitButton'
import CustomButton from '@/Fragments/forms/Buttons/CustomButton'
import { SubmitButton } from '@/Fragments/forms/Buttons/SubmitButton'
import Form from '@/Fragments/forms/Form'
import Select from '@/Fragments/forms/inputs/Select'
import Submit from '@/Fragments/forms/inputs/Submit'
import TextArea from '@/Fragments/forms/inputs/TextArea'
import TextField from '@/Fragments/forms/inputs/TextField'
import TitleTextField from '@/Fragments/forms/inputs/TitleTextfield'
import Toggle from '@/Fragments/forms/inputs/Toggle'
import { Button } from '@/Fragments/UI/Button'
import AdminLayout from '@/Layouts/AdminLayout'
import { Head, useForm, usePage } from '@inertiajs/react'
import { Check } from '@phosphor-icons/react'
import React, { useEffect, useState } from 'react'

type NewsCategory = 'Odznak' | 'Lorem'

type Props = {
    awards?: Award
}
type LangType = 'cz' | 'eng'

const Credit = ({ awards }: Props) => {
    const [activeTab, setActiveTab] = useState<NewsCategory>(awards?.category as NewsCategory || 'Odznak');
    const form = useForm({
        description: awards?.description || '',
        name: awards?.name || '',
        category: activeTab,
        condition_type: awards?.condition_type || '',
    })

    /*  useEffect(() => {
         form.setData('category', activeTab)
     }, [activeTab]) */

    const submit = (e: React.FormEvent) => {
        e.preventDefault()
        form.post(route('admin.awards.store'), {
        })
    }
    const buttonHref = awards && form.isDirty ? route('admin.awards.update', { award: awards.id }) : route('admin.awards.store');
    const buttonText = awards && !form.isDirty ? null : (awards ? "Upravit ocenění" : "Přidat ocenění");


    const addPost = (e: React.MouseEvent) => {
        e.preventDefault()
        form.post(route('admin.awards.store'), {
            onSuccess: () => {
                form.reset()
            }
        })
    }




    return (
        <AdminLayout
            rightChild={false}
            customButtonHref={buttonHref}
            addButtonText={buttonText}
            customButtonClassName="bg-app-input-green text-white font-teko"
            title={`${awards ? `Detail ${awards.name}` : 'Přidat nové ocenění'} | Winfolio`}
        >
            <Head title={`${awards ? `Detail ${awards.name}` : 'Přidat nové ocenění'} | Winfolio`} />
            <div className="flex flex-col">

                <Breadcrumbs
                    previous={{ name: 'Ocenění', href: route('admin.awards.index') }}
                    current={`${awards ? ` ${awards.name}` : 'Přidat nové ocenění'}`}
                />
                <div className='mt-24px text-xl mb-16px font-bold font-teko'>
                    {awards ? 'Upravit ocenění' : 'Přidat nové ocenění'}
                </div>
                <Form form={form} onSubmit={submit} >
                    <div className="flex  flex-col">
                        <div className=' flex flex-col gap-8px p-[24px] border border-black'>
                            <div className='font-bold text-base '>Základní údaje</div>
                            <div>
                                <TextField label={'Název ocenění'} name={'name'} />
                            </div>
                            <div>
                                <TextField label={'Popisek'} name={'description'} />
                            </div>


                            <div className="mt-[16px] border p-4px border-black flex w-[fit-content] text-[14px] font-bold">
                                {/* Tlačítko pro Novinku */}
                                <Button
                                    preserveState
                                    onClick={() => {
                                        setActiveTab('Odznak');
                                        form.setData('category', 'Odznak');
                                    }}
                                    className={`px-[16px] py-[8px] border-2 border-black ${activeTab === 'Odznak' ? 'bg-black text-white' : 'bg-white text-black'} rounded-l-[4px]`} href={'#'}>
                                    Odznak
                                </Button>
                                {/* Tlačítko pro Blogpost */}
                                {/*  <Button
                                    preserveState
                                    onClick={() => setActiveTab('Lorem')}
                                    className={`px-[16px] py-[8px] border-2 border-black ${activeTab === 'Lorem' ? 'bg-black text-white' : 'bg-white text-black'} -ml-[2px]`} href={'#'}>
                                    Lorem
                                </Button> */}

                            </div>



                        </div>
                        <div className='pt-16px'>
                            <Select
                                label={'Podmínky'}
                                name={'condition_type'}
                                value={form.data.condition_type}
                                onChange={e => form.setData('condition_type', e.target.value)}
                                options={[
                                    { text: 'Konkrétní produkt', value: 'specific_product' },
                                    { text: 'Konkrétní kategorie', value: 'specific_category' },
                                    { text: 'Počet produktů v kategorii', value: 'category_items_count' },
                                    { text: 'Celkový počet produktů', value: 'total_items_count' },
                                    { text: 'Hodnota portfolia', value: 'portfolio_value' },
                                    { text: 'Procento portfolia', value: 'portfolio_percentage' }
                                ]}
                            />
                        </div>
                        <div className='flex justify-end mt-16px'>
                            {awards ? (
                                form.isDirty && (
                                    <button
                                        onClick={(e) => {
                                            e.preventDefault();
                                            form.post(route('admin.awards.update', { award: awards.id }), {
                                                onSuccess: () => {
                                                    form.reset();
                                                }
                                            });
                                        }}
                                        className='py-12px px-24px hover:scale-105 items-center bg-app-input-green rounded-sm font-bold font-teko text-white border-black border-2 flex gap-12px'
                                    >
                                        <Check weight='bold' />Upravit ocenění
                                    </button>
                                )
                            ) : (
                                <button
                                    onClick={addPost}
                                    className='py-12px px-24px hover:scale-105 items-center bg-app-input-green rounded-sm font-bold font-teko text-white border-black border-2 flex gap-12px'
                                >
                                    <Check weight='bold' />Přidat nové ocenění
                                </button>
                            )}
                        </div>


                    </div>
                </Form>
            </div >

        </AdminLayout >
    )
}

export default Credit