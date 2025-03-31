import PrimaryButton from '@/Components/PrimaryButton'
import SecondaryButton from '@/Components/SecondaryButton'
import Breadcrumbs from '@/Fragments/forms/Breadcrumbs'
import { CSubmitButton } from '@/Fragments/forms/Buttons/CSubmitButton'
import CustomButton from '@/Fragments/forms/Buttons/CustomButton'
import { SubmitButton } from '@/Fragments/forms/Buttons/SubmitButton'
import Form from '@/Fragments/forms/Form'
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

type NewsCategory = 'novinka' | 'blogpost' | 'analyza'

type Props = {
    news?: News
    category?: News
}
type LangType = 'cz' | 'eng'

const Credit = ({ news }: Props) => {

    const [activeTab, setActiveTab] = useState<NewsCategory>(news?.category as NewsCategory || 'novinka')
    const [langTab, setLangTab] = useState<LangType>('cz')
    const { props } = usePage()

    const form = useForm({
        title: news?.title || '',
        category: activeTab,
        content: news?.content || '',
        is_active: news?.is_active || false,
        lang: langTab,
        id: news?.id || null,
    })

    useEffect(() => {
        form.setData('category', activeTab)
    }, [activeTab])

    const submit = (e: React.FormEvent) => {
        e.preventDefault()
        form.post(route('admin.news.store'), {
        })
    }

    const buttonHref = news && form.isDirty ? route('admin.news.update', { news: news.id }) : route('admin.news.store');
    const buttonText = news && !form.isDirty ? null : (news ? "Upravit příspěvek" : "Přidat příspěvek");


    const addPost = (e: React.MouseEvent) => {
        e.preventDefault()
        form.post(route('admin.news.store'), {
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
            title={`${news ? `Detail ${news.title}` : 'Nová novinka'} | Winfolio`}
        >
            <Head title={`${news ? `Detail ${news.title}` : 'Nová novinka'} | Winfolio`} />
            {/* ... rest of the component content ... */}
            <div className="p-[16px]">

                <Breadcrumbs
                    previous={{ name: 'Novinky a analýzy', href: route('admin.news.index') }}
                    current={`${news ? ` ${news.title}` : 'Nová novinka'}`}
                />
                <Form form={form} onSubmit={submit} >
                    <div className="flex flex-col">
                        <div className='bg-[#F5F5F5] p-[64px] border border-black'>
                            <TitleTextField name={'title'} placeholder={'Nadpis příspěvku'}>
                            </TitleTextField>
                        </div>
                        {/* Přepínač kategorií */}
                        <div className='flex gap-16px'>
                            <div className="mt-[16px] border p-4px border-black flex w-[fit-content] text-[14px] font-bold">
                                {/* Tlačítko pro Novinku */}
                                <Button
                                    preserveState
                                    onClick={() => setActiveTab('novinka')}
                                    className={`px-[16px] py-[8px] border-2 border-black ${activeTab === 'novinka' ? 'bg-black text-white' : 'bg-white text-black'} rounded-l-[4px]`} href={'#'}>
                                    Novinka
                                </Button>
                                {/* Tlačítko pro Blogpost */}
                                <Button
                                    preserveState
                                    onClick={() => setActiveTab('blogpost')}
                                    className={`px-[16px] py-[8px] border-2 border-black ${activeTab === 'blogpost' ? 'bg-black text-white' : 'bg-white text-black'} -ml-[2px]`} href={'#'}>
                                    Blogpost
                                </Button>
                                <Button
                                    preserveState
                                    onClick={() => setActiveTab('analyza')}
                                    className={`px-[16px] py-[8px] border-2 border-black ${activeTab === 'analyza' ? 'bg-black text-white' : 'bg-white text-black'} -ml-[2px]`} href={'#'}>
                                    Analyza
                                </Button>
                            </div>
                            <div className="mt-[16px] border p-4px border-black flex w-[fit-content] text-[14px] font-bold">
                                <Button
                                    onClick={() => setLangTab('cz')}
                                    className={`px-[16px] py-[8px] border-2 border-black ${langTab === 'cz' ? 'bg-black text-white' : 'bg-white text-black'} rounded-l-[4px]`} href={'#'}>
                                    🇨🇿
                                </Button>
                                <Button
                                    onClick={() => setLangTab('eng')}
                                    className={`px-[16px] py-[8px] border-2 border-black ${langTab === 'eng' ? 'bg-black text-white' : 'bg-white text-black'} -ml-[2px]`} href={'#'}>
                                    🇬🇧
                                </Button>
                            </div>
                        </div>
                        <div className="mt-[16px]">

                            <>
                                <TextArea label={'Obsah...'} className="p-[8px] bg-[#F5F5F5]" name={'content'} />
                            </>


                        </div>
                        <div className='flex justify-between w-full gap-16px mt-[16px]'>
                            <div>
                                <Toggle name={`is_active`} />
                            </div>
                            <div>
                                {news ? (
                                    form.isDirty && <CSubmitButton />
                                ) : (
                                    <button onClick={addPost} className='py-12px px-24px hover:scale-105 items-center bg-app-input-green rounded-sm font-bold font-teko text-white border-black border-2 flex gap-12px' ><Check weight='bold' />Přidat příspěvěk</button>
                                )}
                            </div>
                        </div>
                    </div>
                </Form>
            </div>

        </AdminLayout >
    )
}

export default Credit