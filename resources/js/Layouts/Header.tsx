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
            case 'Task':
                return route('tasks.show', { task: key })
            case 'JobOffer':
                return route('jobs.show', { jobOffer: key })
            case 'User':
                return route('users.edit', { user: key })
            case 'Admin':
                return route('team.edit', { admin: key })
        }

    }

    const types = (model) => {

        switch (model) {
            case 'JobOffer':
                return "Joby"
            case 'User':
                return "Uživatelé"
            case 'Task':
                return "Tasky"
            case 'Admin':
                return "Adminy"
        }

    }
    const form = useForm({});
    const { data } = form;

    return (
        <header className=" border-b-[1px] rounded-0 flex items-center justify-between  relative">
            {/* Levá část: Logo */}
            <Link href={route('admin.dashboard')}>
                <Img className="p-16px" src="/assets/img/logo.png" />
            </Link>

            {/* Střed: Form se SearchHeaderem */}
            <Form form={form} className="flex items-center gap-4">
                <TextField className="min-w-[300px] mb-8px flex justify-center border-2" placeholder="Vyhledat" name={"x"} />
                {/*  <SearchHeader
                    className="min-w-[300px] flex justify-center border-2"
                    name="search"
                    placeholder="Vyhledat"
                    keyName="search_all" optionsCallback={function (data: unknown): { text: string; value: string | number; element?: any; model: unknown; header?: string; } {
                        throw new Error("Function not implemented.");
                    }}                    optionsCallback={(data) => ({
                        text: data?.name || `${data.id} | ${data.model}`,
                        value: data.id ?? '',
                        model: data,
                    })}
                /> */}
            </Form>
            <div>
                <Button className='font-black bg-[#F7AA1A] border-black rounded-sm border-2 mr-16px' href=/* {route('categories.create')}  */"#" icon={<Plus size={24} weight='bold' />}>Přidat položku</Button>
            </div>

            {/* Pravá část: cokoliv co vložíš do {rightChild} */}
            {rightChild}
        </header>
    )

}

export default Header
