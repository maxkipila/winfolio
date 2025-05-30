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
                <div className='text-[#4D4D4D]'>{product?.name ?? "---"}</div>
            </div>
            <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px font-nunito'>
                <div className='text-[#4D4D4D]'>{t('Theme')}</div>
                <div className='text-[#4D4D4D]'>{product?.theme?.parent?.name ? product?.theme?.parent?.name : (product?.theme?.name ?? "---")}</div>
            </div>
            <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px font-nunito'>
                <div className='text-[#4D4D4D]'>{t('Subtheme')}</div>
                <div className='text-[#4D4D4D]'>{product?.theme?.parent?.name ? product?.theme?.name : "---"}</div>
            </div>
            <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px font-nunito'>
                <div className='text-[#4D4D4D]'>{t('Year')}</div>
                <div className='text-[#4D4D4D]'>{product?.year ?? "---"}</div>
            </div>
            <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px font-nunito'>
                <div className='text-[#4D4D4D]'>{t('Released')}</div>
                <div className='text-[#4D4D4D]'>{product?.year ?? "---"}</div>
            </div>
            <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px font-nunito'>
                <div className='text-[#4D4D4D]'>{t('Availability')}</div>
                <div className='text-[#4D4D4D]'>{product?.latest_price?.condition ?? "---"}</div>
            </div>
            <div className='flex justify-between items-center border-t border-[#D0D4DB] py-12px font-nunito'>
                <div className='text-[#4D4D4D]'>{t('Packaging')}</div>
                <div className='text-[#4D4D4D]'>{product?.latest_price?.condition ?? "---"}</div>
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
                <div className='text-white font-bold px-12px py-8px bg-[#46BD0F] max-w-[136px]'>{t('Availible at retail')}</div>
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
                    <Button className='mob:hidden' href={"#"} onClick={(e) => { e.preventDefault(); setSelected({ ...product }); open(MODALS.PORTFOLIO) }} icon={<Plus size={24} />}>{t('Add to portfolio')}</Button>
            }
        </div>
    )
}

export default ProductDetails
