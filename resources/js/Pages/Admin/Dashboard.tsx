import { SubmitButton } from '@/Fragments/forms/Buttons/SubmitButton';
import Form from '@/Fragments/forms/Form';
import { FormContext } from '@/Fragments/forms/FormContext';
import DateFilter from '@/Fragments/forms/inputs/DateFilter';
import DateInput from '@/Fragments/forms/inputs/DateInput';
import Submit from '@/Fragments/forms/inputs/Submit';
import Table from '@/Fragments/Table/Table';
import Td from '@/Fragments/Table/Td';
import Th from '@/Fragments/Table/Th';
import { Button } from '@/Fragments/UI/Button';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { AlignBottom, AlignLeft, ArrowCircleUpRight, Fire, Triangle, User, Users, UsersFour } from '@phosphor-icons/react';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    Title,
    Tooltip,
    Legend,
    PointElement,
    LineElement
} from 'chart.js';
import { toPadding } from 'chart.js/helpers';
import moment from 'moment';
import { FormEventHandler, useContext, useEffect } from 'react';
import { Line } from 'react-chartjs-2';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
);

interface UsersData {
    total: number
    before_total: number
    percentage: number
    diff: number

    active_total: number
    active_before_total: number
    active_percentage: number
    active_diff: number

    new_total: number
    new_before_total: number
    new_percentage: number
    new_diff: number
}

interface Props {
    data?: {
        users?: UsersData
        // Můžete sem doplnit freeUsersByDay, premiumUsersByDay atd. z kontroleru
        p_start?: string
        p_end?: string
    }
    start: string
    end: string
    users: Array<User>

}

function UserRow(props: User & { sent_payments_sum: number } & { setItems: React.Dispatch<React.SetStateAction<Array<User & { sent_payments_sum: number }>>> }) {
    const { id, status, email, first_name, last_name, phone, prefix, thumbnail, sent_payments_sum, setItems } = props;
    /* const { open, close } = useContext(ModalsContext) */
    const { setData } = useContext(FormContext);

    useEffect(() => {
        setData(d => ({ ...d, [`status-${id}`]: status }))
    }, [])

    return (
        <tr className='odd:bg-[#F5F5F5] hover:outline hover:outline-2 hover:outline-offset-[-2px] outline-black w-full '>
            <Td><Link className='hover:underline' href={route('admin.users.show', { user: id })}>{id}</Link></Td>
            <Td><Link className='hover:underline' href={route('admin.users.show', { user: id })}>{first_name}{last_name}</Link></Td>
            <Td><Link className='hover:underline' href={route('admin.users.show', { user: id })}>{/* {Math.floor((sent_payments_sum ?? 0) * 0.05 * 100) / 100} */} {/* Kč */}</Link></Td>

            {/* <Td>
                <div className='flex gap-8px items-center justify-end'>
                    <Link href={route('users.edit', { user: id })}><PencilSimple /></Link>
                    <button onClick={(e) => removeItem(e, id)}><Trash className='text-app-input-error' /></button>
                </div>
            </Td> */}
        </tr>
    );
}

export default function Dashboard(props: Props) {
    const { data = {}, start, end } = props;
    const { users = null } = data || {};

    // Formulář pro odeslání filtru data (od-do)
    const form = useForm({
        start: start,
        end: end
    });
    const { post, setData, data: form_data } = form;

    // ----------------------------------------------------
    // 1) Generování dvou sad náhodných dat (free/premium)
    //    Místo toho napojte reálná data z props (např. props.data.freeUsersByDay, props.data.premiumUsersByDay).
    // ----------------------------------------------------
    let daysFree: Record<string, number> = {};
    let daysPremium: Record<string, number> = {};

    let current = moment(start).clone();
    while (current.isBefore(moment(end))) {
        let key = current.format('YYYY-MM-DD');
        daysFree[key] = Math.floor(Math.random() * 100);
        daysPremium[key] = Math.floor(Math.random() * 100);
        current.add(1, 'day');
    }

    // ----------------------------------------------------
    // 2) Příprava labels a datasetů pro line chart
    // ----------------------------------------------------
    const labels = Object.keys(daysFree).map(dateStr =>
        moment(dateStr, 'YYYY-MM-DD').format('D. MMM')
    );

    const freeValues = Object.values(daysFree);
    const premiumValues = Object.values(daysPremium);

    const lineChartData = {
        labels,
        datasets: [
            {
                label: 'Free ',
                data: freeValues,
                borderColor: '#339933',
                backgroundColor: '#33993320',
                tension: 0.3,
                fill: true,

            },
            {
                label: 'Premium ',
                data: premiumValues,
                borderColor: '#ffcc00',
                backgroundColor: '#ffcc0020',
                tension: 0.3,
                fill: true
            },
        ]
    };

    const options = {
        responsive: true,
        plugins: {
            legend: {
                display: true,
                position: 'top' as const,
                align: 'start' as const,
                labels: {
                    usePointStyle: true,
                    pointStyle: 'circle',
                    boxWidth: 10,
                    boxHeight: 10,
                    font: {
                        size: 12,
                        weight: 'bold' as const
                    },
                },
            },
            title: {
                display: true,
                text: '',
            },
        },
        scales: {
            x: {
                grid: {
                    display: false
                }
            },
            y: {
                ticks: {
                    callback: function (value: number) {
                        return Math.floor(value);
                    },
                },
                grid: {
                    display: true
                }
            }
        },
        maintainAspectRatio: false,
    };

    // ----------------------------------------------------
    // 3) Odeslání filtru (start-end)
    // ----------------------------------------------------
    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        // POST na stejnou adresu s parametry start/end
        post('?');
    }

    // ----------------------------------------------------
    // 4) Pomocná funkce pro tvar šipky (triangle) v kartách
    // ----------------------------------------------------
    const positive = (value?: number) => (value ?? -1) > 0 ? 'text-app-secondary' : 'text-[#E66C6C] rotate-180';

    return (
        <AdminLayout rightChild={false} title='Dashboard | Winfolio'>
            <Form className='flex items-center w-full' form={form} onSubmit={submit}>
                <div className='w-full'>
                    <div className='flex w-full -z-10  justify-end mb-24px items-center text-end mx-auto gap-8px'>
                        <DateFilter userId={0} />
                    </div>
                </div>
                {/*  <SubmitButton texts='LOAD' /> */}
            </Form>
            <div className='flex flex-col border-2 rounded-sm border-black bg-white  h-min p-24px max-w-limit w-full'>
                <div className='flex items-center gap-12px'>
                    <UsersFour size={24} />
                    <div className='font-teko font-bold text-xl'>
                        Celkový počet uživatelů
                    </div>
                </div>
                <div className='h-full'>
                    <div className='w-full mt-24px' style={{ height: 500 }}>
                        <Line data={lineChartData} options={options} />
                    </div>
                </div>
            </div>

            {/* Users statistics section */}
            <div className='flex gap-16px max-w-limit w-full mt-16px'>
                <div className='bg-white rounded-md  w-full'>

                    {/* User statistics cards */}
                    <div className='flex gap-16px mt-16px'>
                        {/* free users */}
                        <div className="p-24px border-2 border-black rounded-sm mb-24px bg-white w-full flex items-center justify-between">
                            {/* Levá část - Nadpis a celkový počet uživatelů */}
                            <div className="flex flex-col">
                                <div className="font-bold text-sm">Celkem free uživatelů</div>
                                <div className="font-bold font-teko  text-4xl">{users?.total || 0}</div>
                            </div>

                            {/* Pravá část - Procento změny a indikátor (zelená kulička) */}
                            <div className="flex items-center flex-col gap-4px">

                                <div className="w-16px h-16px rounded-full bg-green-600"></div>

                                <span className="text-black font-medium  flex items-center gap-1">+{users?.percentage || 0}%</span>
                            </div>
                        </div>


                        {/* Active users */}
                        <div className="p-24px border-2 border-black rounded-sm mb-24px bg-white w-full flex items-center justify-between">
                            {/* Levá část */}
                            <div className="flex flex-col">
                                <div className="font-bold text-sm">Celkem premium uživatelů</div>
                                <div className="font-extrabold text-4xl">{users?.active_total || 0}</div>
                            </div>

                            {/* Pravá část - Šipka + procento + zlatá tečka */}
                            <div className="flex items-center flex-col gap-8px">
                                <div className="w-16px h-16px rounded-full bg-yellow-500"></div>
                                <div className="flex items-center gap-4px">
                                    <ArrowCircleUpRight weight='bold' size={20} className={`${positive(users?.active_percentage)} text-[#46BD0F]`} />
                                    <div className="text-black font-medium">
                                        +{users?.active_percentage || 0}%
                                    </div>
                                </div>
                            </div>
                        </div>


                        {/* New users */}
                        {/*  <div className='p-16px bg-app-lightbackground w-full rounded-md flex flex-col items-center justify-center'>
                            <div className='font-bold'>Noví</div>
                            <div className='font-extrabold text-xl'>{users?.new_total || 0}</div>
                            <div className='flex items-center gap-12px'>
                                <div className='flex items-center gap-4px'>
                                    <Triangle weight='fill' size={12} className={positive(users?.new_percentage)} />
                                    <div>{users?.new_percentage || 0}%</div>
                                </div>
                                <div className='flex items-center gap-4px'>
                                    <Triangle weight='fill' size={12} className={positive(users?.new_diff)} />
                                    <div>{users?.new_diff || 0}</div>
                                </div>
                            </div>
                        </div> */}
                        {/* Incomes */}
                        <div className="p-16px border-2 border-black rounded-sm mb-24px bg-white w-full flex items-center justify-between">
                            {/* Levá část - Nadpis a celkový počet uživatelů */}
                            <div className="flex flex-col">
                                <div className="font-bold text-sm">Příjmy</div>
                                <div className="font-bold font-teko  text-4xl">100 500 Kč</div>
                            </div>

                            {/* Pravá část - Procento změny a indikátor (zelená kulička) */}
                            <div className="flex items-center gap-4px">
                                <ArrowCircleUpRight weight='bold' size={20} className={`${positive(users?.active_percentage)} text-[#46BD0F]`} />
                                <div className="text-black font-medium">
                                    +{users?.active_percentage || 0}%
                                </div>
                            </div>
                        </div>

                    </div>
                    <div className='flex gap-24px'>
                        <div className=' w-1/2 rounded-sm border-2 border-black'>
                            <div className='p-24px w-full'>
                                <div className='flex gap-16px items-center'>
                                    <div className='w-48px h-48px bg-[#F5F5F5] rounded-sm bg-app-lightbackground flex items-center justify-center '>
                                        <Fire size={24} weight='bold' />
                                    </div>
                                    <div className='font-bold font-teko text-xl'>Nejaktivnější uživatelé</div>
                                </div>
                                <div className=''>
                                    <Table<User>
                                        item_key="top_users_collection"
                                        custom="border-none m-0 w-full table-auto"
                                        Row={UserRow}
                                    >

                                        <Th order_by='id'>ID</Th>
                                        <Th order_by='id'>Uživatelé</Th>

                                    </Table>
                                </div>
                            </div>
                        </div>
                        <div className="p-24px border-2 w-1/2 border-black rounded-sm bg-white">

                            <div className='flex gap-16px items-center'>
                                <div className='w-48px mb-8px h-48px bg-[#F5F5F5] rounded-sm bg-app-lightbackground flex items-center justify-center '>
                                    <Users size={24} weight='bold' />
                                </div>
                                <div className='font-bold font-teko text-xl'>Uživatelé</div>
                            </div>

                            {/* 3 boxy vedle sebe */}
                            <div className="flex gap-16px">
                                {/* Box 1 - Celkem */}
                                <div className="py-[52px] px-16px rounded-sm bg-[#F5F5F5] flex-1 flex flex-col items-center">
                                    <div className="text-sm font-bold text-center  mb-4px">Celkem</div>
                                    <div className="text-4xl font-teko font-bold mb-8px">1000</div>
                                    <div className="flex items-center gap-8px">
                                        <div className="flex items-center gap-4px">
                                            <ArrowCircleUpRight size={20} weight='bold' className="text-[#46BD0F] " />
                                            <span className="text-black font-medium">+100%</span>
                                        </div>
                                        <div className='flex items-center gap-4px'>
                                            <ArrowCircleUpRight size={20} weight='bold' className="text-[#46BD0F]" />
                                            <div className="font-medium">1000</div>

                                        </div>
                                    </div>
                                </div>

                                {/* Box 2 - Aktivní */}
                                <div className="py-[52px] px-16px rounded-sm bg-[#F5F5F5] flex-1 flex flex-col items-center">
                                    <div className="text-sm font-bold text-center  mb-4px">Aktivní</div>
                                    <div className="text-4xl font-teko font-bold mb-8px">100</div>
                                    <div className="flex items-center gap-8px">
                                        <div className="flex items-center gap-4px">
                                            <ArrowCircleUpRight size={20} weight='bold' className="text-[#46BD0F]" />
                                            <span className="text-black font-medium">+100%</span>
                                        </div>
                                        <div className='flex items-center gap-4px'>
                                            <ArrowCircleUpRight size={20} weight='bold' className="text-[#46BD0F]" />
                                            <div className="font-medium">1000</div>

                                        </div>
                                    </div>
                                </div>

                                {/* Box 3 - Noví */}
                                <div className="py-[52px] px-16px rounded-sm bg-[#F5F5F5] flex-1 flex flex-col items-center">
                                    <div className="text-sm font-bold text-center  mb-4px">Noví</div>
                                    <div className="text-4xl font-teko font-bold mb-8px">10</div>
                                    <div className="flex items-center gap-8px">
                                        <div className="flex items-center gap-4px">
                                            <ArrowCircleUpRight size={20} weight='bold' className="text-[#46BD0F]" />
                                            <span className="text-black font-medium">+100%</span>
                                        </div>
                                        <div className='flex items-center gap-4px'>
                                            <ArrowCircleUpRight size={20} weight='bold' className="text-[#46BD0F]" />
                                            <div className="font-medium">1000</div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout >
    );
}