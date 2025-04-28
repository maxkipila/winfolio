import Img from '@/Components/Image'
import PrimaryButton from '@/Components/PrimaryButton'
import SecondaryButton from '@/Components/SecondaryButton'
import Breadcrumbs from '@/Fragments/forms/Breadcrumbs'
import { CSubmitButton } from '@/Fragments/forms/Buttons/CSubmitButton'
import CustomButton from '@/Fragments/forms/Buttons/CustomButton'
import { SubmitButton } from '@/Fragments/forms/Buttons/SubmitButton'
import Form from '@/Fragments/forms/Form'
import Search, { SearchCard } from '@/Fragments/forms/inputs/Search'
import SearchMultiple from '@/Fragments/forms/inputs/SearchMultiple'
import Select from '@/Fragments/forms/inputs/Select'
import Submit from '@/Fragments/forms/inputs/Submit'
import TextArea from '@/Fragments/forms/inputs/TextArea'
import TextField from '@/Fragments/forms/inputs/TextField'
import TitleTextField from '@/Fragments/forms/inputs/TitleTextfield'
import Toggle from '@/Fragments/forms/inputs/Toggle'
import { Button } from '@/Fragments/UI/Button'
import AdminLayout from '@/Layouts/AdminLayout'
import { Head, useForm, usePage } from '@inertiajs/react'
import { Check, X } from '@phosphor-icons/react'
import axios from 'axios'
import React, { useEffect, useState } from 'react'

type NewsCategory = 'Odznak' | 'Lorem'


type Props = {
    awards?: Award
    conditions?: Award

}
type LangType = 'cz' | 'eng'

const Credit = ({ awards, conditions }: Props) => {
    const [activeTab, setActiveTab] = useState<NewsCategory>(awards?.category as NewsCategory || 'Odznak');
    const form = useForm({
        description: awards?.description || '',
        name: awards?.name || '',
        category: activeTab,
        condition_type: awards?.condition_type || conditions?.condition_type || 'specific_product',
        product_id: awards?.product_id || conditions?.product_id || '',
        required_count: awards?.conditions?.[0]?.required_count || '',
        required_value: awards?.required_value || conditions?.required_value || '',
        required_percentage: awards?.conditions?.[0]?.required_percentage || awards?.required_percentage || '',
        category_id: awards?.category_id || conditions?.category_id || [],
        category_name: awards?.category_name || conditions?.category_name || '',

        conditions: [
            {
                award_id: awards?.id || '',
                category_id: '',
                product_id: awards?.product_id || '',
                required_count: awards?.required_count || '',
                required_value: awards?.required_value || '',
                category_name: awards?.category_name || '',
            }
        ]

    });

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
    const removePost = (e: React.MouseEvent) => {
        e.preventDefault()
        form.delete(route('admin.awards.destroy', { award: awards.id }), {
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

                            </div>

                        </div>
                        <div className='flex flex-col gap-16px p-[24px] mt-[16px] border border-black'>

                            <div>
                                <Select
                                    label={'Typ podmínky'}
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

                            {form.data.condition_type === 'specific_product' && (
                                <div>
                                    <div className='mt-16px pb-16px flex font-teko text-xl'>
                                        {awards?.conditions?.map((c, index) => (
                                            <div key={c.award_id || index} className="flex items-center p-16px justify-between mb-2 p-2 border border-gray-200 rounded">
                                                <div className="flex gap-16px">
                                                    <span>ID: {c.product_id}</span>
                                                    <span>Nazev: {c.product_name}</span>
                                                </div>
                                                <div className='flex gap-16px'>
                                                    <button
                                                        className="cursor-pointer"
                                                        onClick={() => {
                                                            form.delete(route('admin.awards.removeField', {
                                                                award: awards.id,
                                                                condition: c.award_id,
                                                                field: 'product'
                                                            }));
                                                        }}
                                                    />
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                    <SearchMultiple<Award>
                                        name="product_name"
                                        keyName="search_products"
                                        placeholder='Konkretní produkt'
                                        value={form.data.product_id}
                                        onChange={(value) => form.setData('product_id', value)}
                                        optionsCallback={(r) => ({
                                            text: r.name,
                                            element: (
                                                <div>{r.name}</div>
                                            ),
                                            value: r.id,
                                            object: r
                                        })}

                                    />

                                </div>
                            )}

                            {form.data.condition_type === 'specific_category' && (
                                <div>
                                    <div className='mt-16px pb-16px flex flex-col font-teko text-xl'>
                                        {awards?.conditions?.map((c, index) => (
                                            <div key={c.condition} className="flex w-1/4 items-center p-16px justify-between mb-2 p-2 border border-gray-200 rounded">
                                                <div className="flex gap-16px">
                                                    <span>ID: {c.category_id}</span>
                                                    <span>Nazev: {c.category_name}</span>
                                                </div>
                                                <button
                                                    className="cursor-pointer"
                                                    value={'x'}
                                                    onClick={() => {
                                                        form.delete(route('admin.awards.removeField', {
                                                            award: awards.id,
                                                            condition: c.award_id,
                                                            field: 'category'
                                                        }));
                                                    }}
                                                >X</button>
                                            </div>
                                        ))}
                                    </div>
                                    <SearchMultiple<any>
                                        name="category_id"
                                        keyName="search_themes"
                                        placeholder="Název kategorie"
                                        value={form.data.category_id}
                                        onChange={(value) => {
                                            const selectedId = Array.isArray(value) ? value[0] : value;
                                            form.setData('category_id', selectedId);
                                        }}
                                        optionsCallback={(r) => ({
                                            text: r.parent && r.parent.name ? `${r.parent.name} > ${r.name}` : r.name,
                                            element: <div>
                                                {r.parent && r.parent.name ? (
                                                    <span className="text-black font-bold  mr-4px">{r.parent.name} &gt;</span>
                                                ) : null}
                                                <span>{r.name}</span>
                                            </div>,
                                            value: r.id,
                                            object: r
                                        })}
                                    />


                                </div>
                            )}

                            {
                                form.data.condition_type === 'category_items_count' && (
                                    <div className='flex flex-col gap-16px'>
                                        <div className='mt-16px flex pb-16px  flex-col font-teko text-xl'>
                                            {awards?.conditions?.map((c, index) => (
                                                <div key={c.condition} className="flex  w-1/4 items-center p-16px justify-between mb-2 p-2 border border-gray-200 rounded">
                                                    <div className="flex gap-16px">
                                                        <span>ID: {c.category_id}</span>
                                                        <span>Nazev: {c.category_name}</span>
                                                    </div>
                                                    <button
                                                        className="cursor-pointer"
                                                        value={'x'}
                                                        onClick={() => {
                                                            form.delete(route('admin.awards.removeField', {
                                                                award: awards.id,
                                                                condition: c.award_id,
                                                                field: 'category'
                                                            }));
                                                        }}
                                                    >X</button>
                                                </div>
                                            ))}
                                        </div>
                                        <div className='flex flex-row gap-16px'>
                                            <SearchMultiple<any>
                                                name="category_id"
                                                keyName="search_themes"
                                                placeholder="Název kategorie"
                                                value={form.data.category_id}
                                                onChange={(value) => {
                                                    const selectedId = Array.isArray(value) ? value[0] : value;
                                                    form.setData('category_id', selectedId);
                                                }}
                                                optionsCallback={(r) => ({
                                                    text: r.parent && r.parent.name ? `${r.parent.name} > ${r.name}` : r.name,
                                                    element: <div>
                                                        {r.parent && r.parent.name ? (
                                                            <span className="text-black font-bold  mr-4px">{r.parent.name} &gt;</span>
                                                        ) : null}
                                                        <span>{r.name}</span>
                                                    </div>,
                                                    value: r.id,
                                                    object: r
                                                })}
                                            />
                                            <TextField
                                                label="Počet"
                                                name="required_count"
                                                value={form.data.required_count}
                                                onChange={e => form.setData('required_count', e.target.value)}
                                            />
                                        </div>
                                    </div>
                                )
                            }

                            {
                                form.data.condition_type === 'total_items_count' && (
                                    <TextField
                                        label="Počet"
                                        name="required_count"
                                        value={form.data.required_count}
                                        onChange={e => form.setData('required_count', e.target.value)}
                                    />
                                )
                            }

                            {
                                form.data.condition_type === 'portfolio_value' && (
                                    <TextField
                                        label="Hodnota"
                                        name="required_value"
                                        value={form.data.required_value}
                                        onChange={e => form.setData('required_value', e.target.value)}
                                    />
                                )
                            }

                            {
                                form.data.condition_type === 'portfolio_percentage' && (
                                    <TextField
                                        label="Procento (%)"
                                        name="required_percentage"
                                        value={form.data.required_percentage}
                                        onChange={e => form.setData('required_percentage', e.target.value)}
                                    />
                                )
                            }
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
                    </div>
                </Form>
            </div >

        </AdminLayout >
    )
}

export default Credit