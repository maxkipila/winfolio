import ProductCard from '@/Fragments/ProductCard'
import React from 'react'
import { t } from '../Translator'

interface Props {
    product: Product
    similiar_products?: Array<Product>
}

function ProductSimilarProducts(props: Props) {
    const { product, similiar_products } = props

    return (
        <>
            <div className='mt-40px font-bold text-xl'>{t('Other sets in Theme')}</div>
            <div className='grid grid-cols-2 mob:grid-cols-1 mt-16px gap-12px'>
                {/* <ProductCard wide />
                        <ProductCard wide /> */}
                {
                    similiar_products?.length > 0 ?
                        similiar_products?.map((sp) =>
                            <ProductCard wide {...sp} />
                        )
                        :
                        <div className='font-bold text-xl'>{t('No other sets in theme')}</div>
                }
            </div>
        </>
    )
}

export default ProductSimilarProducts
