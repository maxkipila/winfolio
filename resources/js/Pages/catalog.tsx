import Form from '@/Fragments/forms/Form';
import TextField from '@/Fragments/forms/inputs/TextField';
import ProductCard from '@/Fragments/ProductCard';
import { Button } from '@/Fragments/UI/Button';
import useLazyLoad from '@/hooks/useLazyLoad';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { useForm } from '@inertiajs/react';
import { MagnifyingGlass, SlidersHorizontal, SpinnerGap, TrendUp, X } from '@phosphor-icons/react';
import React, { useState } from 'react'

interface ThemeCardProps extends Theme {
    selected: Theme
    setSelected: React.Dispatch<React.SetStateAction<Theme>>
}

function ThemeCard(props: ThemeCardProps) {
    const { name, selected, id, setSelected } = props

    return (
        <div onClick={() => { setSelected({ ...props }) }} className={`cursor-pointer w-full min-w-[120px] border-2 ${selected?.id == id ? "border-[#F7AA1A]" : "border-black"}  flex items-center justify-center bg-[#F5F5F5] flex-col p-12px gap-8px mob:min-w-[112px]`}>
            <div className='w-40px h-40px bg-white flex items-center justify-center rounded-full  '>
                <TrendUp size={24} />
            </div>
            <div className='font-bold text-center font-nunito'>{name}</div>
        </div>
    )
}

interface Props { }

function Catalog(props: Props) {
    const { } = props
    const form = useForm({});
    const { data } = form;
    const [products, button, meta, setItems] = useLazyLoad<Product>('products');
    const [themes] = useLazyLoad<Theme>('themes');
    let [selected, setSelected] = useState<Theme>(null)

    return (
        <AuthenticatedLayout>

            <div className='max-w-[920px] mx-auto pb-24px'>
                <Form className='pt-32px mob:px-24px' form={form}>
                    <TextField placeholder={"Vyhledat položku"} name="search" icon={<MagnifyingGlass size={24} />} />
                </Form>
                <div className='mt-24px w-full flex gap-12px overflow-x-auto mob:px-24px'>
                    {
                        themes?.map((t) =>
                            <ThemeCard setSelected={setSelected} selected={selected} {...t} />
                        )
                    }
                </div>
                <div className='mt-24px border-t-2 border-[#E6E6E6] pt-24px flex justify-between items-center mob:flex-col mob:gap-12px mob:items-start mob:px-24px'>
                    <div className='flex items-center gap-12px'>
                        <div className='p-12px font-nunito font-bold border-2 border-black text-white bg-black'>Vše</div>
                        <div className='p-12px font-nunito font-bold border-2 border-black'>Avatar</div>
                        <div className='p-12px font-nunito font-bold border-2 border-black'>Baby</div>
                    </div>
                    <div className='items-center gap-8px grid grid-cols-3 max-w-[450px]'>
                        {
                            selected.children?.map((c) =>
                                <div className='px-16px py-8px font-nunito font-bold bg-[#F5F5F5] text-center'>{c.name}</div>
                            )
                        }
                        <SlidersHorizontal size={24} />
                    </div>
                </div>
                <div className='grid grid-cols-2 mob:grid-cols-1 mt-24px gap-24px mob:px-24px'>

                    {
                        products?.map((s) =>
                            <ProductCard wide {...s} />
                        )
                    }
                </div>
                <div className='flex items-center justify-center w-full mt-24px'>
                    <div>
                        <Button href="#">Zobrazit další</Button>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    )
}

export default Catalog
