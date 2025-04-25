import { ModalsContext } from '@/Components/contexts/ModalsContext';
import { PortfolioContext } from '@/Components/contexts/PortfolioContext';
import Img from '@/Components/Image';
import usePageProps from '@/hooks/usePageProps';
import { ArrowUpRight, HandPointing, MagnifyingGlass, X } from '@phosphor-icons/react';
import React, { useContext, useState } from 'react'
import { Button } from './UI/Button';
import Form from './forms/Form';
import { useForm } from '@inertiajs/react';
import TextField from './forms/inputs/TextField';
import Search from './forms/inputs/Search';
import ProductCard from './ProductCard';
import useLazyLoad from '@/hooks/useLazyLoad';
import PortfolioProductCard from './PortfolioProductCard';
import Select from './forms/inputs/Select';
import ImageInput from './forms/inputs/ImageInput';
import { t } from '@/Components/Translator';

function SearchCard({ name, type, image, href }) {
    return (
        <div className="border-t leading-4 px-16px hover:bg-app-input-border-light/10 border-app-input-border py-8px cursor-pointer flex text-black" >
            <div className="flex py-4px items-center  rounded gap-12px flex-grow">
                {image && <div className="w-30px h-30px flex-shrink-0 overflow-hidden rounded-full"><Img className="object-cover object-center w-full h-full" src={image} alt={`${name} | Matejovsky`} /></div>}
                <div className='whitespace-nowrap flex items-center justify-between flex-grow gap-12px'>
                    {name}
                    <div className='bg-app-background-orange border-app-input-border rounded px-12px py-4px'>{type}</div>
                </div>
            </div>
        </div>
    );
}

function TextCard() {

    return (
        <div className='border-2 border-black max-w-[380px] min-w-[380px] grid'>
            <Img className='object-cover row-start-1 col-start-1' src="/assets/img/brick-placeholder.png" />
            <div className='row-start-1 col-start-1 p-[56px]'>
                <div className='font-teko font-bold text-3xl text-white text-center'>Vzácný Harry Potter</div>
                <div className='text-white font-nunito text-center'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non.</div>
            </div>
        </div>
    )
}

interface Props { }

function PortfolioModal(props: Props) {
    const { } = props
    let { close } = useContext(ModalsContext)
    let { setDisplayModal, hasProducts, selected, setSelected, setProducts, products: _contextProducts } = useContext(PortfolioContext)
    let [createPortfolio, setCreatePortfolio] = useState(hasProducts)
    const { auth, search_products } = usePageProps<{ auth: { user: User }, search_products: Array<Product> }>();
    const [products, button, meta, setItems] = useLazyLoad<Product>('products');
    const form = useForm({
        search_products: '',
        day: '',
        month: '',
        year: '',
        price: 0,
        status: '',
        currency: ''
    });
    const { data, post, reset, processing, progress } = form;

    function add_to_portfolio() {
        post(route('add_product_to_user', { product: selected?.id }), {
            onSuccess: () => { setProducts((d) => [...d, { product: selected, purchase_date: `${data['day']}. ${data['month']}. ${data['year']}`, price: data['price'], status: data['status'], currency: data['currency'] }]); setSelected(undefined); reset(); }
        })
    }
    function add_to_portfolio_and_close() {
        post(route('add_product_to_user', { product: selected?.id }), {
            onSuccess: () => { setProducts((d) => [...d, { product: selected, purchase_date: `${data['day']}. ${data['month']}. ${data['year']}`, price: data['price'], status: data['status'], currency: data['currency'] }]); setSelected(undefined); reset(); close(); }
        })
    }
    function remove_from_portfolio(my_product: Product) {
        post(route('remove_product_from_user', { product: my_product.id }), {
            onSuccess: () => { setProducts(_contextProducts.filter((cp) => cp?.product?.id != my_product.id)); }
        })
    }
    console.log('processing: ', processing)
    return (
        <div onClick={() => { close() }} className="bg-black bg-opacity-80 fixed top-0 left-0 w-full h-screen items-center justify-center mob:block mob:max-h-full flex z-max mob:pb-0">
            <div onClick={(e) => { e.stopPropagation(); }} className='bg-white border-2 border-black w-full h-full overflow-y-auto'>
                {/* <div className='flex items-end justify-end'>
                    <div onClick={() => { close() }} className='w-40px h-40px bg-black flex items-center justify-center'>
                        <X color='white' size={24} />
                    </div>
                </div> */}
                <div className=''>
                    {
                        (!createPortfolio && !hasProducts) ?
                            <>
                                <div className='mx-auto max-w-1/3 flex flex-col items-center justify-center'>
                                    <Img className='w-[84px] h-[84px]' src="/assets/img/user.png" />
                                    <div className='py-16px'>{t('Vítej ve Winfolio')}</div>
                                    <div className='font-bold text-6xl'>{auth?.user?.first_name}!</div>
                                </div>
                                <div className='flex gap-32px overflow-x-auto mt-32px'>
                                    <TextCard />
                                    <TextCard />
                                    <TextCard />
                                    <TextCard />
                                    <TextCard />
                                </div>
                                <div className='flex items-center justify-center gap-12px max-w-[200px] mx-auto mt-40px'>
                                    <div className='cursor-pointer' onClick={() => { close(); }}>{t('Přeskočit')}</div>
                                    <Button href="#" icon={<HandPointing size={24} weight='bold' />} onClick={(e) => { e.preventDefault(); setCreatePortfolio(true); }}>{t('Vytvořit portfolio')}</Button>
                                </div>
                            </>
                            :
                            <Form form={form}>

                                {
                                    selected ?

                                        <div className='max-w-1/3 mx-auto grid'>
                                            <div className='flex justify-between mt-48px'>
                                                <div className='font-bold font-teko text-xl mb-24px'>{t('Nová položka')}</div>
                                                <div onClick={() => { setSelected(undefined); }} className='cursor-pointer font-bold'>{t('Cancel')}</div>
                                            </div>
                                            <ProductCard wide {...selected} />
                                            <div className='mt-40px font-nunito mb-8px text-[#4D4D4D]'>{t('Datum nákupu')}</div>
                                            <div className='flex gap-8px'>
                                                <Select name="day" placeholder='DD' options={[
                                                    { text: '01', value: '1' },
                                                    { text: '02', value: '2' },
                                                    { text: '03', value: '3' },
                                                    { text: '04', value: '4' },
                                                    { text: '05', value: '5' },
                                                    { text: '06', value: '6' },
                                                    { text: '07', value: '7' },
                                                    { text: '08', value: '8' },
                                                    { text: '09', value: '9' },
                                                    { text: '10', value: '10' },
                                                    { text: '11', value: '11' },
                                                    { text: '12', value: '12' },
                                                    { text: '13', value: '13' },
                                                    { text: '14', value: '14' },
                                                    { text: '15', value: '15' },
                                                    { text: '16', value: '16' },
                                                    { text: '17', value: '17' },
                                                    { text: '18', value: '18' },
                                                    { text: '19', value: '19' },
                                                    { text: '20', value: '20' },
                                                    { text: '21', value: '21' },
                                                    { text: '22', value: '22' },
                                                    { text: '23', value: '23' },
                                                    { text: '24', value: '24' },
                                                    { text: '25', value: '25' },
                                                    { text: '26', value: '26' },
                                                    { text: '27', value: '27' },
                                                    { text: '28', value: '28' },
                                                    { text: '29', value: '29' },
                                                    { text: '30', value: '30' },
                                                    { text: '31', value: '31' },

                                                ]} />
                                                <Select name="month" placeholder='MM' options={[
                                                    { text: '01', value: '1' },
                                                    { text: '02', value: '2' },
                                                    { text: '03', value: '3' },
                                                    { text: '04', value: '4' },
                                                    { text: '05', value: '5' },
                                                    { text: '06', value: '6' },
                                                    { text: '07', value: '7' },
                                                    { text: '08', value: '8' },
                                                    { text: '09', value: '9' },
                                                    { text: '10', value: '10' },
                                                    { text: '11', value: '11' },
                                                    { text: '12', value: '12' },
                                                ]} />
                                                <Select name="year" placeholder='YYYY' options={[

                                                    { text: '1960', value: '1960' },
                                                    { text: '1961', value: '1961' },
                                                    { text: '1962', value: '1962' },
                                                    { text: '1963', value: '1963' },
                                                    { text: '1964', value: '1964' },
                                                    { text: '1965', value: '1965' },
                                                    { text: '1966', value: '1966' },
                                                    { text: '1967', value: '1967' },
                                                    { text: '1968', value: '1968' },
                                                    { text: '1969', value: '1969' },

                                                    { text: '1970', value: '1970' },
                                                    { text: '1971', value: '1971' },
                                                    { text: '1972', value: '1972' },
                                                    { text: '1973', value: '1973' },
                                                    { text: '1974', value: '1974' },
                                                    { text: '1975', value: '1975' },
                                                    { text: '1976', value: '1976' },
                                                    { text: '1977', value: '1977' },
                                                    { text: '1978', value: '1978' },
                                                    { text: '1979', value: '1979' },

                                                    { text: '1980', value: '1980' },
                                                    { text: '1981', value: '1981' },
                                                    { text: '1982', value: '1982' },
                                                    { text: '1983', value: '1983' },
                                                    { text: '1984', value: '1984' },
                                                    { text: '1985', value: '1985' },
                                                    { text: '1986', value: '1986' },
                                                    { text: '1987', value: '1987' },
                                                    { text: '1988', value: '1988' },
                                                    { text: '1989', value: '1989' },

                                                    { text: '1990', value: '1990' },
                                                    { text: '1991', value: '1991' },
                                                    { text: '1992', value: '1992' },
                                                    { text: '1993', value: '1993' },
                                                    { text: '1994', value: '1994' },
                                                    { text: '1995', value: '1995' },
                                                    { text: '1996', value: '1996' },
                                                    { text: '1997', value: '1997' },
                                                    { text: '1998', value: '1998' },
                                                    { text: '1999', value: '1999' },

                                                    { text: '2000', value: '2000' },
                                                    { text: '2001', value: '2001' },
                                                    { text: '2002', value: '2002' },
                                                    { text: '2003', value: '2003' },
                                                    { text: '2004', value: '2004' },
                                                    { text: '2005', value: '2005' },
                                                    { text: '2006', value: '2006' },
                                                    { text: '2007', value: '2007' },
                                                    { text: '2008', value: '2008' },
                                                    { text: '2009', value: '2009' },
                                                    { text: '2010', value: '2010' },
                                                    { text: '2011', value: '2011' },
                                                    { text: '2012', value: '2012' },
                                                    { text: '2013', value: '2013' },
                                                    { text: '2014', value: '2014' },
                                                    { text: '2015', value: '2015' },
                                                    { text: '2016', value: '2016' },
                                                    { text: '2017', value: '2017' },
                                                    { text: '2018', value: '2018' },
                                                    { text: '2019', value: '2018' },
                                                    { text: '2020', value: '2020' },
                                                    { text: '2021', value: '2021' },
                                                    { text: '2022', value: '2022' },
                                                    { text: '2023', value: '2023' },
                                                    { text: '2024', value: '2024' },
                                                    { text: '2025', value: '2025' },
                                                ]} />

                                            </div>
                                            <div className='flex gap-8px mt-8px'>
                                                <TextField name="price" placeholder={"Nákupní cena"} label={"Nákupní cena"} />
                                                <Select name="currency" placeholder={t('currency')} options={[
                                                    { text: 'CZK', value: 'CZK' },
                                                    { text: 'EUR', value: 'EUR' },
                                                    { text: 'USD', value: 'USD' },
                                                ]} />
                                            </div>
                                            <div className='mt-8px'>
                                                <Select name="status" placeholder='Stav' options={[
                                                    { text: 'Zabalený', value: 'packed' },
                                                    { text: 'Rozbalený', value: 'unpacked' },
                                                    { text: 'Použitý', value: 'used' },
                                                ]} />
                                            </div>
                                            <div className='mt-40px font-nunito mb-8px text-[#4D4D4D]'>{t('Nahrát fotografie')}</div>
                                            <ImageInput multiple imagePreview name="images" />
                                            <div className='flex justify-end items-center gap-24px'>
                                                <div className='cursor-pointer font-bold font-teko' onClick={() => { add_to_portfolio() }}>{t('Uložit a vytvořit další')}</div>
                                                <Button className='max-w-[160px]' href="#" onClick={(e) => { e.preventDefault(); add_to_portfolio_and_close(); }}>{t('Dokončit')}</Button>
                                            </div>
                                        </div>

                                        :
                                        <div className='flex divide-x-2 divide-[#DEDFE5]'>
                                            <div className='w-full mt-48px'>
                                                <div className='flex gap-8px items-center mx-auto w-full justify-center mb-48px'>
                                                    <div className='h-2px w-38px bg-[#666666]'></div>
                                                    <div className='h-2px w-38px bg-[#999999]'></div>
                                                    <div className='h-2px w-38px bg-[#999999]'></div>
                                                </div>
                                                <div className='max-w-1/3 mx-auto'>
                                                    {/* <TextField icon={<MagnifyingGlass size={24} weight='bold' />} placeholder={"Vyhledat položku"} label={"Vyhledat položku"} name="search" /> */}
                                                    <Search<Product>
                                                        // className="min-w-[400px]"
                                                        name="search_products"
                                                        placeholder="Hledat položku"
                                                        keyName="search_products"
                                                        noSuggestion
                                                        optionsCallback={(r) => ({
                                                            text: r.name,
                                                            element: (
                                                                <SearchCard name={`${r?.name}`} type={'App\\Models\\Product'} image={'thumbnail' in r ? r?.thumbnail : undefined} href={"#"} />
                                                            ),
                                                            value: r.id,
                                                        })}
                                                    />
                                                </div>
                                                
                                                {
                                                    data['q']?.length > 0 &&
                                                    <>
                                                        {
                                                            search_products?.length > 0 ?
                                                                <>
                                                                    <div className='flex justify-center items-center gap-12px mt-28px'>
                                                                        <ArrowUpRight size={24} weight='bold' />
                                                                        <div className='font-bold font-teko text-xl'>{`Výsledky vyhledávání ${data['search_products']?.length > 0 ? search_products?.length : ''}`}</div>
                                                                    </div>
                                                                    <div className='grid grid-cols-2 gap-16px p-24px'>
                                                                        {
                                                                            search_products?.length > 0 ?
                                                                                search_products?.map((sp) =>
                                                                                    <PortfolioProductCard wide {...sp} />
                                                                                )
                                                                                :
                                                                                products?.map((sp) =>
                                                                                    <PortfolioProductCard wide {...sp} />
                                                                                )

                                                                        }
                                                                    </div>
                                                                </>
                                                                :
                                                                <div className='font-bold text-3xl text-center py-40px'>{t('Pro vaše vyhledávání nejsou výsledky, nebo jste zatím nic nevyhledali')}</div>
                                                        }
                                                    </>
                                                }
                                                <div className='flex justify-center mt-24px mb-48px'>
                                                    <Button className='max-w-[160px]' href="#" onClick={(e) => { e.preventDefault(); close(); }}>{t('Dokončit')}</Button>
                                                </div>
                                            </div>
                                            {
                                                _contextProducts?.length > 0 &&
                                                <div className='px-32px flex-shrink-0 sticky top-0'>
                                                    <div className='font-bold text-lg font-teko mb-24px text-center mt-48px'>{t('Seznam položek')}</div>
                                                    {
                                                        _contextProducts?.map((cp) =>
                                                            <div className='grid mb-16px'>
                                                                <PortfolioProductCard wide {...cp.product} />
                                                                <div className='flex border-l-2 border-r-2 border-b-2 border-black p-16px justify-between'>
                                                                    <div>
                                                                        <div className='font-nunito font-medium text-[#4D4D4D]'>{t("Datum nákupu")}</div>
                                                                        <div className='font-bold font-nunito'>{cp?.purchase_date}</div>
                                                                    </div>
                                                                    <div>
                                                                        <div className='font-nunito font-medium text-[#4D4D4D]'>{t('Nákupní cena')}</div>
                                                                        <div className='font-bold font-nunito'>{cp?.currency} {cp?.price}</div>
                                                                    </div>
                                                                    <div>
                                                                        <div className='font-nunito font-medium text-[#4D4D4D]'>{t('Stav')}</div>
                                                                        <div className='font-bold font-nunito'>{cp?.status}</div>
                                                                    </div>
                                                                </div>
                                                                <div onClick={() => { remove_from_portfolio(cp.product) }} className='font-nunito underline text-end cursor-pointer font-bold mt-12px'>{t('Odstranit položku')}</div>
                                                            </div>
                                                        )
                                                    }
                                                </div>
                                            }
                                        </div>
                                }
                            </Form>
                    }
                </div>

            </div>
        </div>
    )
}

export default PortfolioModal
