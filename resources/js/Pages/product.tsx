import { ModalsContext } from '@/Components/contexts/ModalsContext'
import { PortfolioContext } from '@/Components/contexts/PortfolioContext'
import Img from '@/Components/Image'
import ProductDetails from '@/Components/product/ProductDetails'
import ProductForecast from '@/Components/product/ProductForecast'
import ProductGalery from '@/Components/product/ProductGalery'
import ProductHeadline from '@/Components/product/ProductHeadline'
import ProductMinifigs from '@/Components/product/ProductMinifigs'
import ProductPredictions from '@/Components/product/ProductPredictions'
import ProductPricing from '@/Components/product/ProductPricing'
import ProductSimilarProducts from '@/Components/product/ProductSimilarProducts'
import { t } from '@/Components/Translator'
import { MODALS } from '@/Fragments/Modals'
import PointsGraph from '@/Fragments/PointsGraph'
import ProductCard from '@/Fragments/ProductCard'
import PromotionalCard from '@/Fragments/PromotionalCard'
import ReviewCard from '@/Fragments/ReviewCard'
import { Button } from '@/Fragments/UI/Button'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, Link, useForm } from '@inertiajs/react'
import { ArrowDownRight, ArrowRight, ArrowUpRight, Basket, Export, Heart, Lock, Plus, Question, X } from '@phosphor-icons/react'
import axios from 'axios'
import moment from 'moment'
import React, { useContext, useState } from 'react'

interface Props {
    product: Product
    similiar_products?: Array<Product>
    priceHistory: any
}

function Product(props: Props) {
    const { product, similiar_products, priceHistory } = props
    let [quickBuy, setQuickBuy] = useState(true)
    const form = useForm({});
    const { data, post } = form;
    let { open } = useContext(ModalsContext)
    let { setSelected } = useContext(PortfolioContext)
    console.log(' set selcted', setSelected)

    function remove_from_portfolio(my_product: Product) {
        post(route('remove_product_from_user', { product: my_product.id }))
    }
    return (
        <AuthenticatedLayout>
            <Head title={`${product?.name} | Winfolio`} />
            {/* DESKTOP */}
            <div className='w-full pb-24px px-24px flex gap-20px pt-32px mob:flex-col mob:hidden'>
                <div className='w-2/3 flex-shrink-0 mob:w-full'>
                    <ProductGalery product={product} />
                    <ProductHeadline product={product} form={form} />
                    {
                        !(product?.user_owns?.length > 0) &&
                        <Button className='nMob:hidden mt-12px' href={"#"} onClick={(e) => { e.preventDefault(); setSelected({ ...product }); open(MODALS.PORTFOLIO, false, { create_portfolio: true }) }} icon={<Plus size={24} />}>{t('Add to portfolio')}</Button>
                    }
                    <div className='mt-24px font-bold text-xl'>{t('Forecast')}</div>
                    {/* <div className='mt-16px font-nunito text-[#4D4D4D]'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non.</div> */}
                    <ProductForecast product={product} />
                    <PointsGraph product={product} priceHistory={priceHistory} />
                    {/* <ProductPredictions product={product} /> */}
                    <div className='mt-40px font-bold text-xl'>{t('Claim')}</div>
                    {/* <div className='mt-16px font-nunito text-[#4D4D4D]'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non.</div> */}
                    <ProductMinifigs product={product} />
                    <ProductSimilarProducts product={product} similiar_products={similiar_products} />
                </div>
                <div className='w-full'>
                    <ProductDetails product={product} form={form} />
                    <ProductPricing product={product} />
                    <div className='mt-32px border-2 border-black p-32px'>
                        <div className='font-bold font-teko'>{t('Set Facts')}</div>
                        <div className='mt-16px flex gap-16px'>
                            <div className='w-8px h-8px rounded-full bg-black flex-shrink-0 mt-8px'></div>
                            <div className='text-[#4D4D4D]'>The Make & Take event were be available in UK and selected European stores on March 9th between 10:00 – 12:00 and on March 10th between 12:00 – 14:00.</div>
                        </div>
                    </div>
                    <ReviewCard product={product} />
                </div>
            </div>


            {/* MOBILE */}
            <div className='w-full pb-24px px-24px flex gap-20px pt-32px mob:flex-col nMob:hidden mob:pt-0'>


                <ProductHeadline product={product} form={form} />
                <ProductGalery product={product} />
                {
                    !(product?.user_owns?.length > 0) &&
                    <Button className='nMob:hidden mt-12px' href={"#"} onClick={(e) => { e.preventDefault(); setSelected({ ...product }); open(MODALS.PORTFOLIO, false, { create_portfolio: true }) }} icon={<Plus size={24} />}>{t('Add to portfolio')}</Button>
                }
                <div className='mt-24px font-bold text-xl'>{t('Forecast')}</div>
                {/* <div className='mt-16px font-nunito text-[#4D4D4D]'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non.</div> */}
                <PointsGraph product={product} priceHistory={priceHistory} />
                <ProductDetails product={product} form={form} />
                {/* <ProductForecast product={product} /> */}
                 <ProductPricing product={product} />
                {/* <ProductPredictions product={product} /> */}
                <div className='mt-40px font-bold text-xl'>{t('Claim')}</div>
                {/* <div className='mt-16px font-nunito text-[#4D4D4D]'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non.</div> */}
                <ProductMinifigs product={product} />
                <ProductSimilarProducts product={product} similiar_products={similiar_products} />


                
               
                {/* <div className='mt-32px border-2 border-black p-32px'>
                    <div className='font-bold font-teko'>{t('Set Facts')}</div>
                    <div className='mt-16px flex gap-16px'>
                        <div className='w-8px h-8px rounded-full bg-black flex-shrink-0 mt-8px'></div>
                        <div className='text-[#4D4D4D]'>The Make & Take event were be available in UK and selected European stores on March 9th between 10:00 – 12:00 and on March 10th between 12:00 – 14:00.</div>
                    </div>
                </div>
                <ReviewCard product={product} /> */}

            </div>

        </AuthenticatedLayout>
    )
}

export default Product
