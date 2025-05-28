import { t } from '@/Components/Translator';
import Form from '@/Fragments/forms/Form';
import TextField from '@/Fragments/forms/inputs/TextField';
import ProductCard from '@/Fragments/ProductCard';
import { Button } from '@/Fragments/UI/Button';
import { useDebouncedCallback } from '@/hooks/useDebounceCallback';
import useLazyLoad from '@/hooks/useLazyLoad';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, router, useForm } from '@inertiajs/react';
import { MagnifyingGlass, SlidersHorizontal, SpinnerGap, TrendUp, X } from '@phosphor-icons/react';
import React, { useEffect, useState } from 'react'

interface ThemeCardProps extends Theme {
    selected: Theme
    setSelected: React.Dispatch<React.SetStateAction<Theme>>
}

function ThemeCard(props: ThemeCardProps) {
    const { name, selected, id, setSelected } = props

    return (
        <div onClick={() => { setSelected({ ...props }) }} className={`cursor-pointer w-full min-w-[120px] border-2 ${selected?.id == id ? "border-black nMob:hover:border-[#FFB400] bg-black text-white" : "border-black nMob:hover:border-[#FFB400] bg-[#F5F5F5]"}  flex items-center justify-center  flex-col p-12px gap-8px mob:min-w-[112px]`}>
            <div className={`w-40px h-40px bg-white text-black flex items-center justify-center rounded-full  `}>
                <TrendUp size={24} />
            </div>
            <div className='font-bold text-center font-nunito'>{name}</div>
        </div>
    )
}

interface Props {
    themes: Array<Theme>
}

function Catalog(props: Props) {
    const { themes } = props
    const form = useForm({
        search: ''
    });
    const { data } = form;
    const [products, button, meta, setItems] = useLazyLoad<Product>('products');
    // const [themes] = useLazyLoad<Theme>('themes');
    let [selected, setSelected] = useState<Theme>(null)
    let [themeChildren, setThemeChildren] = useState<Array<number>>([])
    let [type, setType] = useState(null)
    let [showTrending, setShowTrending] = useState(false)
    const search = useDebouncedCallback(() => {
        router.post(route('catalog', { parent_theme: selected?.id ?? null, theme_children: themeChildren, search: data['search'], type: type }))
    }, 700);

    useEffect(() => {
        search()
    }, [selected, themeChildren, data['search'], type])

    useEffect(() => {
        setThemeChildren([])
    }, [selected])

    let [trendingProducts, trendButton, TrendMeta, TrendsetItems] = useLazyLoad<{ product: Product }>('trending_products');

    return (
        <AuthenticatedLayout>
            <Head title="Catalog | Winfolio" />
            <div className='max-w-[920px] mx-auto pb-24px'>
                <Form className='pt-32px mob:px-24px' form={form}>
                    <TextField placeholder={t("Vyhledat položku")} name="search" icon={<MagnifyingGlass size={24} />} />
                </Form>
                <div className='mt-24px w-full flex gap-12px overflow-x-auto mob:px-24px'>
                    <div onClick={() => { setShowTrending((p) => !p) }} className={`cursor-pointer w-full min-w-[120px] border-2 ${showTrending ? "border-[#FFB400]" : "border-black"}  flex items-center justify-center bg-[#F5F5F5] flex-col p-12px gap-8px mob:min-w-[112px]`}>
                        <div className='w-40px h-40px bg-white flex items-center justify-center rounded-full  '>
                            <TrendUp size={24} />
                        </div>
                        <div className='font-bold text-center font-nunito'>Trending</div>
                    </div>
                    {
                        themes?.map((t) =>
                            <ThemeCard setSelected={setSelected} selected={selected} {...t} />
                        )
                    }
                </div>
                <div className='mt-24px border-t-2 border-[#E6E6E6] pt-24px flex justify-between items-center mob:flex-col mob:gap-12px mob:items-start '>
                    <div className='flex items-center gap-12px mob:px-24px'>
                        <div onClick={() => { setType(null) }} className={`cursor-pointer p-12px font-nunito font-bold border-2 border-black ${type == null ? "text-white bg-black" : ""}`}>{t('Vše')}</div>
                        <div onClick={() => { setType('set') }} className={`cursor-pointer p-12px font-nunito font-bold border-2 border-black ${type == "set" ? "text-white bg-black" : ""}`}>{t('Sety')}</div>
                        <div onClick={() => { setType('minifig') }} className={`cursor-pointer p-12px font-nunito font-bold border-2 border-black ${type == "minifig" ? "text-white bg-black" : ""}`}>{t('Minifigs')}</div>
                    </div>
                    <div className='flex gap-12px items-center mob:w-full'>
                        <div className='items-center gap-8px grid grid-cols-3 max-w-[450px] mob:max-w-fit mob:flex overflow-auto mob:px-24px'>
                            {
                                selected?.children?.map((c) => {
                                    let included = themeChildren?.includes(c?.id)
                                    return (
                                        <div onClick={() => { setThemeChildren((p) => included ? p.filter((f) => f != c.id) : [...p, c.id]) }} className={`border-2 cursor-pointer mob:whitespace-nowrap ${included ? "border-[#FFB400]" : "border-[#F5F5F5]"} px-16px py-8px font-nunito font-bold bg-[#F5F5F5] text-center `}>{c.name}</div>
                                    )
                                }
                                )
                            }

                        </div>
                        {/* <SlidersHorizontal className='flex-shrink-0 mr-24px' size={24} /> */}
                    </div>
                </div>

                {
                    showTrending ?
                        <>
                            <div className='grid grid-cols-2 mob:grid-cols-1 gap-24px mt-24px mob:px-24px'>
                                {
                                    trendingProducts?.map((s) =>
                                        <ProductCard {...s.product} />
                                    )
                                }

                            </div>
                            <div className='flex items-center justify-center w-full mt-24px'>
                                <div>
                                    <Button {...trendButton}>{t('Zobrazit další')}</Button>
                                </div>
                            </div>
                        </>
                        :
                        <>
                            <div className='grid grid-cols-2 mob:grid-cols-1 mt-24px gap-24px mob:px-24px'>

                                {
                                    products?.map((s) =>
                                        <ProductCard wide {...s} />
                                    )
                                }
                            </div>
                            <div className='flex items-center justify-center w-full mt-24px'>
                                <div>
                                    <Button {...button}>{t('Zobrazit další')}</Button>
                                </div>
                            </div>
                        </>
                }

            </div>
        </AuthenticatedLayout>
    )
}

export default Catalog
