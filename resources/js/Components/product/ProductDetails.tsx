import { Button } from '@/Fragments/UI/Button'
import { Link } from '@inertiajs/react'
import moment from 'moment'
import React, { useContext } from 'react'
import { t } from '../Translator'
import { ModalsContext } from '../contexts/ModalsContext'
import { PortfolioContext } from '../contexts/PortfolioContext'
import { Plus, X } from '@phosphor-icons/react'
import { MODALS } from '@/Fragments/Modals'

interface Props {
    product: Product
    form: any
}

function ProductDetails(props: Props) {
    const { product, form } = props
    let { open } = useContext(ModalsContext)
    let { setSelected } = useContext(PortfolioContext)
    const { data, post } = form;


    function remove_from_portfolio(my_product: Product) {
        post(route('remove_product_from_user', { product: my_product.id }))
    }
    return (
        <div className='border-2 border-black p-24px flex flex-col gap-12px mob:border-0 mob:p-0 mob:mt-36px'>
            <div className='font-bold text-xl'>{t('Set Details')}</div>

            <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px font-nunito'>
                <div className='text-[#4D4D4D]'>{t('Set number')}</div>
                <div className='text-[#4D4D4D]'>{product?.product_num ?? "---"}</div>
            </div>
            <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px font-nunito'>
                <div className='text-[#4D4D4D]'>{t('Name')}</div>
                <div className='text-[#4D4D4D] max-w-1/2'>{product?.name ?? "---"}</div>
            </div>
            {
                product?.themes?.length == 1 &&
                <>
                    <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px font-nunito'>
                        <div className='text-[#4D4D4D]'>{t('Theme')}</div>
                        {
                            <Link href={route('catalog', { parent_theme: product?.themes[0]?.parent_id ?? product?.themes[0]?.id })} className='text-[#4D4D4D]'>{product?.themes[0]?.parent?.name ?? product?.themes[0]?.name}</Link>
                        }

                    </div>
                    {
                        product?.themes[0]?.parent_id &&
                        <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px font-nunito'>
                            <div className='text-[#4D4D4D]'>{t('Subtheme')}</div>
                            {
                                <Link href={route('catalog', { parent_theme: product?.themes[0]?.parent_id, theme_children: [product?.themes[0]?.id] })} className='text-[#4D4D4D]'>{product?.themes[0]?.name}</Link>
                            }
                        </div>
                    }
                </>
            }
            {
                product?.themes?.length > 1 &&
                <>
                    <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px font-nunito'>
                        <div className='text-[#4D4D4D]'>{t('Themes')}</div>
                        <div className='flex flex-col items-end'>
                            {
                                product?.themes?.map(th =>
                                    th?.parent_id
                                        ? <Link href={route('catalog', { parent_theme: th?.parent_id, theme_children: [th?.id] })} className='text-[#4D4D4D] '>{th?.name}</Link>
                                        : <Link href={route('catalog', { parent_theme: th?.id })} className='text-[#4D4D4D] '>{th?.name}</Link>
                                )
                            }
                        </div>

                    </div>
                </>
            }
            <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px font-nunito'>
                <div className='text-[#4D4D4D]'>{t('Year')}</div>
                <div className='text-[#4D4D4D]'>{product?.year ?? "---"}</div>
            </div>
            <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px font-nunito'>
                <div className='text-[#4D4D4D]'>{t('Released')}</div>
                <div className='text-[#4D4D4D]'>{product?.released_at ? moment(product?.released_at).format('MMM YYYY') : "---"}</div>
            </div>
            <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px font-nunito'>
                <div className='text-[#4D4D4D]'>{t('Availability')}</div>
                <div className='text-[#4D4D4D]'>{product?.availability ?? "---"}</div>
            </div>
            <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px font-nunito'>
                <div className='text-[#4D4D4D]'>{t('Packaging')}</div>
                <div className='text-[#4D4D4D]'>{product?.packaging ?? "---"}</div>
            </div>
            <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px font-nunito'>
                <div className='text-[#4D4D4D]'>{t('Pieces')}</div>
                <div className='text-[#4D4D4D]'>{product?.num_parts ?? "---"}</div>
            </div>
            {
                product?.sets?.length > 0 &&
                <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px'>
                    <div className='text-[#4D4D4D]'>{t('Sets')}</div>
                    <div className='text-[#4D4D4D] flex gap-8px items-center'>
                        {
                            product?.sets?.map((s) =>
                                <Link className='text-[#FFB400] underline' href={route('product.detail', { product: s.id })}>{s.id}</Link>
                            )
                        }
                    </div>
                </div>
            }
            {
                product?.minifigs?.length > 0 &&
                <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px font-nunito'>
                    <div className='text-[#4D4D4D]'>{t('Minifigs')}</div>
                    <div className='text-[#4D4D4D] flex gap-8px items-center'>
                        {
                            product?.minifigs?.map((s) =>
                                <Link className='text-[#FFB400] underline' href={route('product.detail', { product: s.id })}>{s.id}</Link>
                            )
                        }
                    </div>
                </div>
            }
            {/* <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px'>
                            <div className='text-[#4D4D4D]'>Minifigs</div>
                            <div className='text-[#4D4D4D]'>1</div>
                        </div> */}
            {
                product?.availability != null &&
                <div className='text-white font-bold px-12px py-8px bg-[#46BD0F] max-w-[136px]'>{product?.availability}</div>
            }

            {
                product?.user_owns?.length > 0 ?
                    <div className='flex flex-col gap-12px'>
                        {
                            product?.user_owns?.map((u) =>
                                <div className='w-full border-2 border-black px-12px py-8px'>
                                    <div className='flex items-center justify-between mb-24px'>
                                        <div className='font-bold text-lg'>{moment(`${u.purchase_day}.${u.purchase_month}.${u.purchase_year}`).format('DD. MM. YYYY')}</div>
                                        <div onClick={() => { remove_from_portfolio(product) }} className='cursor-pointer h-32px w-32px bg-[#ED2E1B] flex items-center justify-center'>
                                            <X size={24} color="white" />
                                        </div>
                                    </div>
                                    <div className='flex items-center justify-between'>
                                        <div className='font-nunito'>{t('Nákupní cena')}</div>
                                        <div className='font-bold text-lg'>{u.purchase_price} {u.currency}</div>
                                    </div>
                                    {
                                        u?.condition &&
                                        <div className='flex items-center justify-between'>
                                            <div className='font-nunito'>{t('Stav')}</div>
                                            <div className='font-bold text-lg'>{u.condition}</div>
                                        </div>
                                    }
                                    {/* <div className='flex items-center justify-between'>
                                                    <div className='font-nunito'>{t('Datum nákupu')}</div>
                                                    <div className='font-bold text-lg'>{moment(`${u.purchase_day}.${u.purchase_month}.${u.purchase_year}`).format('DD. MM. YYYY')}</div>
                                                </div> */}
                                </div>
                            )
                        }
                    </div>
                    :
                    <Button className='mob:hidden' href={"#"} onClick={(e) => { e.preventDefault(); setSelected({ ...product }); open(MODALS.PORTFOLIO, false, { create_portfolio: true }) }} icon={<Plus size={24} />}>{t('Add to portfolio')}</Button>
            }
        </div>
    )
}

export default ProductDetails
