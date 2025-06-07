import Img from '@/Components/Image'
import Form from '@/Fragments/forms/Form'
import { FormContext } from '@/Fragments/forms/FormContext'
import Group from '@/Fragments/forms/Group'
import Checkbox from '@/Fragments/forms/inputs/Checkbox'
import OrderBy from '@/Fragments/forms/inputs/OrderBy'
import Search from '@/Fragments/forms/inputs/Search'
import Select from '@/Fragments/forms/inputs/Select'
import Toggle from '@/Fragments/forms/inputs/Toggle'
import { MetaBar } from '@/Fragments/MetaBar'
import Table from '@/Fragments/Table/Table'
import Td from '@/Fragments/Table/Td'
import Th from '@/Fragments/Table/Th'
import useLazyLoad from '@/hooks/useLazyLoad'
import usePageProps from '@/hooks/usePageProps'
import AdminLayout from '@/Layouts/AdminLayout'
import { Link, useForm } from '@inertiajs/react'
import { PencilSimple, Trash } from '@phosphor-icons/react'
import { subscribe } from 'diagnostics_channel'
import React, { useContext, useEffect } from 'react'


interface Props {
    productErrors: Array<any>
}

interface ProductError {
    id: number,
    product?: Product
    error: string,
    created_at: string
    context: Record<any, any>
    code: number
}


function Index(props: Props) {
    const { } = props

    return (
        <AdminLayout rightChild={false} title='Product Errory | Winfolio'>
            <ProductErrorTable />
        </AdminLayout>
    )
}

function SearchCard({ name, type, image, href, onClick }) {
    return (
        <div onClick={onClick} className="border-t leading-4 px-16px hover:bg-app-input-border-light/10 border-app-input-border py-8px cursor-pointer flex text-black" >
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

export function ProductErrorTable({ absolute_items, hide_meta }: { absolute_items?: Array<any>, hide_meta?: boolean }) {

    const form = useForm({});
    const { data, setData } = form;

    const search = useForm({});

    return (
        <>
            <Form form={form}>
                <div className='flex flex-col gap-8px mb-16px'>
                    <Group name='codes'>
                        <Checkbox name={'204'} label={"Empty content (204)"} />
                        <Checkbox name={'404'} label={"Not found (404)"} />
                        <Checkbox name={'999'} label={"Time taken (999)"} />
                        <Checkbox name={'4290'} label={"All attempts failed (4290)"} />
                        <Checkbox name={'500'} label={"Error (500)"} />
                    </Group>
                </div>
                <div className='flex flex-col gap-8px mb-16px'>
                    <div className='min-w-[150px]'>
                        <Select name='paginate' label="Počet na stránku" options={[
                            { text: '10', value: 10 },
                            { text: '50', value: 50 },
                            { text: '100', value: 100 },
                            { text: '200', value: 200 },
                            { text: '500', value: 500 },
                            { text: '1000', value: 1000 },
                        ]} />
                    </div>
                    <FormContext.Provider value={search}>
                        <Search<Product>
                            // className="min-w-[400px]"
                            name="search_products"
                            placeholder="Hledat položku"
                            keyName="search_products"
                            asInput
                            useDifferentForm
                            optionsCallback={(r) => ({
                                text: r.name,
                                element: (
                                    <SearchCard name={`${r?.name}`} type={'App\\Models\\Product'} image={'thumbnail' in r ? r?.thumbnail : undefined} onClick={() => setData(d => ({ ...d, q: "", product_id: r.id }))} href={"#"} />
                                ),
                                value: r.id,
                            })}
                        />
                    </FormContext.Provider>

                </div>
            </Form>
            <Table<ProductError> title="ProductErrory" item_key='productErrors' Row={Row} absolute_items={absolute_items} filters={data}>
                <Th>ID</Th>
                <Th>Kód</Th>
                <Th>Produkt</Th>
                <Th>ProductError</Th>
                <Th>Kontext</Th>
                <Th>Vytvořeno</Th>
            </Table>
        </>
    )
}

function Row(props: ProductError & { setItems: React.Dispatch<React.SetStateAction<ProductError[]>> }) {
    const { id, context, created_at, error, product, code, setItems } = props;

    return (
        <tr className='group hover:outline hover:outline-2 hover:outline-offset-[-2px] outline-black'>
            <Td><Link className='hover:underline' href={route('admin.errors.edit', { productError: id })}>{id}</Link></Td>
            <Td>{code}</Td>
            <Td>{product && <Link className='hover:underline' href={route(`admin.products.show.${product.product_type}`, { product: product.id })}> <div className='underline'>{product?.name}</div> </Link>}</Td>
            <Td>{error}</Td>
            <Td><pre className='max-w-[450px] overflow-auto'>{JSON.stringify(context, undefined, 3)}</pre></Td>
            <Td>{created_at}</Td>
        </tr>
    );
}

export default Index
