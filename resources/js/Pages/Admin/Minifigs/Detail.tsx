import React from 'react'
import AdminLayout from '@/Layouts/AdminLayout'
import Breadcrumbs from '@/Fragments/forms/Breadcrumbs'
import Img from '@/Components/Image'
import TextField from '@/Fragments/forms/inputs/TextField'
import Form from '@/Fragments/forms/Form'
import SearchHeader, { SearchCard } from '@/Fragments/forms/inputs/SearchHeader'
import { types } from 'util'
import { Link, useForm } from '@inertiajs/react'
import Search from '@/Fragments/forms/inputs/Search'
import { Plus } from '@phosphor-icons/react'



type Props = {
    minifig: ProductLego;
    otherMinifigs: Array<SetLego>;
}

const Detail = (props: Props) => {

    const { name, img_url, availability, retail_price, release_date, num_parts, packaging, minifigs_count, forecast, market_price } = props.minifig
    const { otherMinifigs } = props

    const form = useForm({

    });
    const { data } = form;
    const types = (model) => {

        switch (model) {
            case 'Product':
                return "MiniFigurky"
            case 'User':
                return "Uživatelé"
        }

    }
    const routes = (model, key) => {

        switch (model) {
            /* case 'Minifig':
                return route('admin.minifigs.index', { minifig: key }) */
            case 'Minifig':
                return route('admin.products.index.minifig', { product: key })

        }

    }
    return (
        <AdminLayout rightChild={true} title="Detail Minifigurky | Winfolio">

            <div className="p-[16px] ">
                <Breadcrumbs
                    previous={{ name: 'Seznam minifigurek', href: route('admin.products.index.minifig') }}
                    current={`${name} `}
                />

                <div className='flex  items-center'>

                    <h1 className="font-teko text-[40px]  leading-[40px] font-bold">
                        {name || 'Název Minifigurky'}
                    </h1>
                    <div className='border justify-center mx-a ml-[150px] items-start p-24px flex-col border-gray-300  flex gap-[8px] border-b-0 border-r-0 rounded-b-0 rounded-r-0 rounded-md'>
                        <div className="font-bold text-black text-xl">Minifig Details</div>
                        <div className='flex gap-24px justify-between'>
                            <span className="text-base">Minifig number</span>
                            <span className="text-base ">{props.minifig.product_num}</span>

                        </div>
                    </div>
                </div>

                {/* Základní info */}
                <div className="text-[20px] leading-[24px] font-teko mt-24px mb-[54px] border-2 border-black p-16px font-semibold">Basic info</div>
                <div className="   ">

                    <div className="flex p-[16px] bg-[#F5F5F5] border-black border-2">

                        <div className=" flex  items-center justify-center overflow-hidden">
                            <div className="w-[120px] aspect-video flex items-center justify-center overflow-hidden bg-white border">
                                {img_url ? (
                                    <Img
                                        src={img_url}
                                        alt={name}
                                        className="object-contain w-full h-full"
                                    />
                                ) : (
                                    <span className="text-[12px]">No image</span>
                                )}
                            </div>
                        </div>
                        <div className=' ml-24px flex w-full flex-col gap-[8px]'>
                            <div className='text-[16px] text-black font-bold leading-[20px]'>{name}</div>
                            <div className='text-[#4D4D4D] font-bold text-sm'>{/* {props.minifig.theme.name} */}</div>
                            <div className='w-full border-[#D0D4DB] border-b'></div>
                            <div className='text-[#4D4D4D] font-medium text-sm'>{/* {year} */}year</div>

                        </div>

                        <div>

                        </div>
                    </div>
                    <div className="flex-1 p-[16px] border-2 border-black border-t-0  grid grid-cols-5 gap-y-[8px] text-[14px]">
                        <div className='text-[#4D4D4D] font-medium leading-[20px]'>Released</div>
                        <div className='text-[#4D4D4D] font-medium leading-[20px]'>Availability</div>
                        <div className='text-[#4D4D4D] font-medium leading-[20px]'>Packaging</div>
                        <div className='text-[#4D4D4D] font-medium leading-[20px]'>Pieces</div>
                        <div className='text-[#4D4D4D] font-medium leading-[20px]'>Minifigs</div>

                        <div className='text-sm text-black font-bold'>{release_date || 'N/A'}</div>
                        <div className='text-sm text-black font-bold'>{availability || 'N/A'}</div>
                        <div className='text-sm text-black font-bold'>{packaging || 'N/A'}</div>
                        <div className='text-sm text-black font-bold'>{num_parts || 'N/A'}</div>
                        <div className='text-sm text-black font-bold'>{minifigs_count ?? 'N/A'}</div>
                    </div>
                    <div className="flex-1 p-[16px] border-2 border-black border-t-0  grid grid-cols-2 gap-y-[8px] text-[14px]">
                        <div className='text-black  font-bold  text-sm'>Forecast</div>
                        <div className='text-black  font-bold  text-sm'>minifig pricing</div>


                        <div className='text-[14px] pr-14px leading-[20px] text-[#4D4D4D] font-medium'>{forecast || 'N/A'}Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non.</div>
                        <div className='flex justify-between'>
                            <div>
                                <div className='text-[#4D4D4D]  font-medium leading-[20px]'>{availability || 'N/A'}Retail</div>
                                <div className='text-[#4D4D4D]  font-medium leading-[20px]'>{availability || 'N/A'}Market</div>
                            </div>
                            <div>
                                <div className='text-sm text-black  font-bold'>{retail_price?.toFixed(2) ?? 'N/A'}</div>
                                <div className='text-sm text-black font-bold'>{market_price?.toFixed(2) ?? 'N/A'}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Minifigs sekce */}
                <div className=" mt-[39px] border-[2px] border-black borde-2 p-[16px] space-y-[8px]">
                    <div className="border-2 font-teko border-black  px-[16px] py-[12px] flex items-center justify-between font-bold text-[16px] ">
                        minifigs

                    </div>
                    <Form form={form} className="flex  items-center gap-4px">
                        <Search<ProductLego >
                            name="searchProducts"
                            placeholder="Vyhledat v produktech"
                            keyName="searchProducts"
                            optionsCallback={(r: ProductLego) => {
                                return {
                                    text: r.name,
                                    element: (
                                        <SearchCard
                                            name={r.name}
                                            image={r.img_url}
                                            href={route('admin.products.show.minifig', { product: r.id })}
                                        />
                                    ),
                                    value: r.id,
                                    model: r,
                                    header: types(r.model)
                                }


                            }}
                        />
                    </Form>
                    <div className="w-full py-[12px] flex flex-grow gap-[16px]">
                        {otherMinifigs && otherMinifigs.map((item) => (
                            <Link
                                key={item.id}
                                href={route('admin.products.show.minifig', { product: item.id })}
                                className="border-2 border-black p-[16px] flex-1 bg-white hover:shadow-md"
                            >
                                {/* Obrázek minifigu */}
                                <div className="flex items-center justify-center mb-[8px] h-[120px] overflow-hidden bg-[#F5F5F5]">
                                    {item.img_url ? (
                                        <Img
                                            src={item.img_url}
                                            alt={item.name}
                                            className="object-contain max-h-full"
                                        />
                                    ) : (
                                        <span className="text-[12px]">No image</span>
                                    )}
                                </div>

                                {/* Název a info o tématu */}
                                <div className="text-[14px] font-bold mb-[4px]">{item.name}</div>
                                <div className="text-[12px] text-gray-500">
                                    {item.theme?.name ?? 'No theme'}
                                </div>
                                <div className="text-[12px] text-gray-500 mb-[8px]">
                                    {item.year ?? '—'}
                                </div>

                                {/* Ceny a statistiky */}
                                <div className="flex justify-between text-[12px] mb-[4px]">
                                    <div>
                                        <div className="font-medium">Retail</div>
                                        <div>
                                            ${item.retail_price?.toFixed(2) ?? 'N/A'}
                                        </div>
                                    </div>
                                    <div>
                                        <div className="font-medium">Value</div>
                                        <div>
                                            ${item.market_price?.toFixed(2) ?? 'N/A'}
                                        </div>
                                    </div>
                                </div>
                                <div className="flex justify-between text-[12px]">
                                    <div>
                                        <div className="font-medium">Growth</div>
                                        <div>7.41%</div>
                                    </div>
                                    <div>
                                        <div className="font-medium">Annual</div>
                                        <div>6.1%</div>
                                    </div>
                                </div>
                            </Link>
                        ))}
                    </div>

                </div>
                <div className='flex mt-[50px] mb-[14px]'>
                    <div className="border-2 border-black  bg-[#F5F5F5] px-[16px] py-[12px] flex items-center justify-between font-bold text-[16px] font-sans w-full">
                        <span>Minifigs</span>
                        <span className='cursor-pointer'><Plus size={16} /></span>
                    </div>


                </div>
                <div className='flex'>
                    <div className="border-2 border-black bg-[#F5F5F5] px-[16px] py-[12px] flex items-center justify-between font-bold text-[16px] font-sans w-full">
                        <span>Other minifigs in Theme </span>

                        <span className='cursor-pointer'><Plus size={16} /></span>
                    </div>


                </div>
            </div>

        </AdminLayout>
    )
}

export default Detail