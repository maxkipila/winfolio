import { ModalsContext } from "@/Components/contexts/ModalsContext";
import Modals, { MODALS, } from "@/Fragments/Modals";
import { DefaultButtons } from "@/Fragments/modals/DefaultButtons";
import Table from "@/Fragments/Table/Table";
import Td from "@/Fragments/Table/Td";
import Th from "@/Fragments/Table/Th";
import AdminLayout from "@/Layouts/AdminLayout";
import { Link } from "@inertiajs/react";
import { Pencil, Trash } from "@phosphor-icons/react";
import { useContext } from "react";




interface Props {
    awards: Array<Award>
    conditions: Array<Award>

}

function Index(props: Props) {
    const { } = props.awards


    return (
        <AdminLayout
            customButtonHref={route('admin.awards.create')}
            addButtonText="Přidat nové ocenění"
            title='Ocenění | Winfolio'>
            <AwardTable />
        </AdminLayout>
    )
}

export function AwardTable({ absolute_items, hide_meta }: { absolute_items?: Array<Award>, hide_meta?: boolean }) {
    return (
        <Table<Award> title="Ocenění" item_key='awards' Row={Row} absolute_items={absolute_items}>
            <Th order_by='id'>ID</Th>
            <Th order_by='name'>Název ocenění</Th>
            <Th>Popis</Th>
            <Th>Podminka</Th>
            <Th>Typ</Th>
            <Th></Th>
            <Th>Akce</Th>


        </Table>
    );
}
function Row(props: Award & { setItems: React.Dispatch<React.SetStateAction<Award[]>> }) {
    const { id, name, description, setItems } = props;

    const { open, close } = useContext(ModalsContext)

    const removeItem = (e: React.MouseEvent<HTMLButtonElement>, id: number) => {
        e.preventDefault();

        open(MODALS.CONFIRM, false, {
            title: "Potvrdit smazání",
            message: "Opravdu chcete smazat ocenění?",
            buttons: <DefaultButtons
                href={route('admin.awards.destroy', { award: id })}
                onCancel={close}
                onSuccess={() => {
                    setItems(pr => pr.filter(f => f.id != id));
                    close();
                }}
            />
        })
    }
    return (
        <tr className="odd:bg-[#F5F5F5] hover:outline hover:outline-2 hover:outline-offset-[-2px] outline-black">
            <Td ><Link href={route('admin.awards.edit', { award: id })}>{id}</Link></Td>
            <Td><Link href={route('admin.awards.edit', { award: id })}>{name}</Link></Td>
            <Td><Link href={route('admin.awards.edit', { award: id })}>{description}</Link></Td>
            <Td>
                {(() => {
                    switch (props.condition_type) {
                        case 'specific_product':
                            return 'Konkrétní produkt';
                        case 'specific_category':
                            return 'Konkrétní kategorie';
                        case 'category_items_count':
                            return 'Počet položek v kategorii';
                        case 'total_items_count':
                            return 'Celkový počet položek';
                        case 'portfolio_value':
                            return 'Hodnota portfolia';
                        case 'portfolio_percentage':
                            return 'Procento portfolia';
                        default:
                            return props.condition_type;
                    }
                })()}
            </Td>
            <Td>{props.category}</Td>
            <Td></Td>
            <Td>
                <div className='flex gap-8px items-center justify-end'>
                    <Link href={route('admin.awards.edit', { award: id })} className=""><Pencil size={24} /></Link>
                    {/* <button onClick={(e) => removeItem(e, id)}><Trash size={24} className='text-app-input-error' /></button> */}
                    <Link href={route('admin.awards.destroy', { award: id })} method="delete" as="button"><Trash size={24} className='text-app-input-error' /></Link>

                </div>
            </Td>





        </tr>
    );
}

export default Index
