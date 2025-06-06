import { ModalsContext } from '@/Components/contexts/ModalsContext';
import { t } from '@/Components/Translator';
import Form from '@/Fragments/forms/Form';
import TextField from '@/Fragments/forms/inputs/TextField';
import { MODALS } from '@/Fragments/Modals';
import ProductCard from '@/Fragments/ProductCard';
import { Button } from '@/Fragments/UI/Button';
import { useDebouncedCallback } from '@/hooks/useDebounceCallback';
import useLazyLoad from '@/hooks/useLazyLoad';
import usePageProps from '@/hooks/usePageProps';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { MagnifyingGlass, SlidersHorizontal, SpinnerGap, TrendUp, X } from '@phosphor-icons/react';
import React, { useContext, useEffect, useState } from 'react'

interface ThemeCardProps extends Theme {
    filtered: Record<string, any>
}

function ThemeCard(props: ThemeCardProps) {
    const { name, id, filtered } = props

    return (
        <Link href={route(route().current(), { ...filtered, parent_theme: filtered.parent_theme == id ? null : id, theme_children: [], trending: false })} as='button' method='post' className={`cursor-pointer transform duration-300 w-full min-w-[120px] border-2 ${filtered?.parent_theme == id ? "border-black nMob:hover:border-[#FFB400] bg-black text-white" : "border-black nMob:hover:border-[#FFB400] bg-[#F5F5F5]  hover:bg-white"} group  flex items-center justify-center  flex-col p-12px gap-8px mob:min-w-[112px]`}>
            <div className={`w-40px h-40px transform duration-300  bg-white group-hover:bg-[#F5F5F5] text-black flex items-center justify-center rounded-full  `}>
                <TrendUp size={24} />
            </div>
            <div className='font-bold text-center font-nunito'>{name}</div>
        </Link>
    )
}

interface Props {
    themes: Array<Theme>
    parent_theme: string,
    theme_children: Array<string>,
    price_range: { from: number, to: number }
    price_trend: 'ASC' | 'DESC',
    favourited: 'ASC' | 'DESC',
    reviews: 'ASC' | 'DESC',
    status: string,
    releaseYear: null | number
    trending?: boolean
    search?: string
    type?: null | "set" | "minifig"
}

function Catalog(props: Props) {
    const { themes, favourited, parent_theme, price_range, price_trend, releaseYear, reviews, status, theme_children, trending, search, type } = props

    const filtered = { parent_theme, theme_children, price_range, price_trend, favourited, reviews, status, releaseYear, trending, search, type };

    const form = useForm({
        search: search
    });

    const { data, post } = form;
    const [products, button, meta, setItems] = useLazyLoad<Product>('products');
    const selected = themes.find(t => `${t.id}` == parent_theme);
    const { open } = useContext(ModalsContext);

    const toggleChild = (p) => theme_children.includes(`${p}`) ? theme_children.filter((f) => f != p) : [...theme_children, p];

    const doSearch = useDebouncedCallback(() => {
        if (data?.search) {
            post(route(route().current(), { ...filtered }));
        }
    }, 500)


    useEffect(() => {
        doSearch();
    }, [data?.search])


    return (
        <AuthenticatedLayout>
            <Head title="Catalog | Winfolio" />
            <div className='max-w-[920px] mx-auto pb-24px'>
                <Form className='pt-32px mob:px-24px flex gap-12px items-center nMob:max-w-1/3 nMob:mx-auto' form={form}>
                    <TextField placeholder={t("Vyhledat položku")} name="search" icon={<MagnifyingGlass size={24} />} />
                    {/* <SlidersHorizontal onClick={() => { open(MODALS.CATALOG_FILTERS, false, { priceRange: filtered.price_range, priceTrend: filtered.price_trend, reviews: reviews, favourited: favourited, status: status, releaseYear: releaseYear }) }} className='cursor-pointer flex-shrink-0' size={24} /> */}
                </Form>
                <div className='mt-24px w-full flex gap-12px overflow-x-auto mob:px-24px'>
                    <Link href={route(route().current(), { ...filtered, trending: !trending, parent_theme: null, theme_children: [] })} as='button' method='post' className={`cursor-pointer w-full min-w-[120px] border-2 ${trending ? "border-black nMob:hover:border-[#FFB400] bg-black text-white" : "border-black nMob:hover:border-[#FFB400] bg-[#F5F5F5]  hover:bg-white"}  group    flex items-center justify-center bg-[#F5F5F5] flex-col p-12px gap-8px mob:min-w-[112px]`}>
                        <div className='w-40px h-40px transform duration-300  bg-white group-hover:bg-[#F5F5F5] text-black flex items-center justify-center rounded-full  '>
                            <TrendUp size={24} />
                        </div>
                        <div className='font-bold text-center font-nunito'>Trending</div>
                    </Link>
                    {
                        themes?.map((t) =>
                            <ThemeCard key={`theme-${t.id}`} {...t} filtered={filtered} />
                        )
                    }
                </div>
                <div className='mt-24px border-t-2 border-[#E6E6E6] pt-24px flex justify-between items-center mob:flex-col mob:gap-12px mob:items-start '>
                    <div className='flex items-center gap-12px mob:px-24px'>
                        <Link href={route(route().current(), { ...filtered, type: null })} as='button' method='post' className={`cursor-pointer p-12px font-nunito font-bold border-2 border-black ${!type ? "text-white bg-black" : ""}`}>{t('Vše')}</Link>
                        <Link href={route(route().current(), { ...filtered, type: "set" })} as='button' method='post' className={`cursor-pointer p-12px font-nunito font-bold border-2 border-black ${type == "set" ? "text-white bg-black" : ""}`}>{t('Sety')}</Link>
                        <Link href={route(route().current(), { ...filtered, type: "minifig" })} as='button' method='post' className={`cursor-pointer p-12px font-nunito font-bold border-2 border-black ${type == "minifig" ? "text-white bg-black" : ""}`}>{t('Minifigs')}</Link>
                    </div>
                    <div className='flex gap-12px items-center mob:w-full'>
                        <div className='items-center gap-8px grid grid-cols-3 max-w-[450px] mob:max-w-fit mob:flex overflow-auto mob:px-24px'>
                            {
                                selected?.children?.map((c) =>
                                    <Link key={`child-theme-${c?.id}`} href={route(route().current(), { ...filtered, theme_children: toggleChild(c.id) })} as='button' method='post' className={`border-2 cursor-pointer mob:whitespace-nowrap ${theme_children.includes(`${c.id}`) ? "border-[#FFB400]" : "border-[#F5F5F5]"} px-16px py-8px font-nunito font-bold bg-[#F5F5F5] text-center `}>{c.name}</Link>
                                )
                            }

                        </div>
                        {/* <SlidersHorizontal className='flex-shrink-0 mr-24px' size={24} /> */}
                    </div>
                </div>

                {
                    /*                     trending ?
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
                                            : */
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
        </AuthenticatedLayout >
    )
}

export default Catalog
