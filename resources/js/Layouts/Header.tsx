import Img from "@/Components/Image";
import Form from "@/Fragments/forms/Form";
import SearchHeader from "@/Fragments/forms/inputs/SearchHeader";
import TextField from "@/Fragments/forms/inputs/TextField";
import { Button } from "@/Fragments/UI/Button";
import { Link, useForm } from "@inertiajs/react";
import { Plus } from "@phosphor-icons/react";
import { ReactNode } from "react";



interface Props {
    children?: ReactNode;
    title?: string;
    rightChild?: ReactNode;
}


function SearchCard({ name, type, image, href }) {
    return (
        <Link href={href} className="border-b leading-4 px-16px hover:bg-app-input-border-light/10 border-app-input-border py-8px cursor-pointer flex text-black" >
            <div className="flex py-4px items-center  rounded gap-12px flex-grow">
                {image && <div className="w-30px h-30px flex-shrink-0 overflow-hidden rounded-full"><Img className="object-cover object-center w-full h-full" src={image} alt={`${name} | Jobert`} /></div>}
                <div className='whitespace-nowrap flex items-center justify-between flex-grow gap-12px'>
                    {name}
                    <div className='bg-app-background-orange border-app-input-border rounded px-12px py-4px'>{type}</div>
                </div>
            </div>
        </Link>
    );
}

function Header(props: Props) {
    const { rightChild } = props

    const routes = (model, key) => {

        switch (model) {
            case 'Minifig':
                return route('admin.products.index.minifig', { minifig: key })
            case 'SetLego':
                return route('admin.products.index.set', { product: key })
            case 'User':
                return route('admin.users.edit', { user: key })
            /* case 'Admin':
                return route('team.edit', { admin: key }) */
        }

    }

    const types = (model) => {

        switch (model) {
            case 'ProductLego':
                return "Sety"
            case 'User':
                return "Uživatelé"
        }

    }
    const form = useForm({});
    const { data } = form;

    return (
        <header className="border-b-[1px] w-full px-16px py-32px flex items-center justify-between relative">
            {/* Levá část: Logo */}
            <Link href={route('admin.dashboard')}>
                <Img className="p-16px" src="/assets/img/logo.png" />
            </Link>

            <div className="flex mx-auto">
                <Form form={form} className="flex  items-center gap-4px">
                    <SearchHeader<User | ProductLego >
                        className="min-w-[300px]"
                        name="search"
                        placeholder="Vyhledat"
                        keyName="search_all"
                        optionsCallback={(r) => ({
                            text: ('name' in r ? (r.name as string) : `${r.id} | ${r.model}`),
                            element: (
                                <SearchCard name={('name' in r ? r.name : `${r.id}`)}
                                    type={types(r.model)}
                                    image={'thumbnail' in r ? r?.thumbnail : undefined}
                                    href={routes(r.model, r.id)}


                                />

                            ),
                            value: r.id ?? '',
                            model: r,
                            header: types(r.model)

                        })}
                    />
                </Form>
            </div>

            {rightChild === false ? null : (
                <>
                    <div>
                        <Button
                            className="font-black bg-[#F7AA1A] border-black rounded-sm border-2 mr-16px"
                            href="#"
                            icon={<Plus size={24} weight='bold' />}
                        >
                            Přidat položku
                        </Button>
                    </div>
                </>
            )}

            {typeof rightChild !== 'boolean' && rightChild}
        </header>
    );
}

export default Header
