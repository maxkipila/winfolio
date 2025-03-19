import Form from '@/Fragments/forms/Form';
import { FormContext } from '@/Fragments/forms/FormContext';
import DateInput from '@/Fragments/forms/inputs/DateInput';
import Submit from '@/Fragments/forms/inputs/Submit';
import Td from '@/Fragments/Table/Td';
import Th from '@/Fragments/Table/Th';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { Triangle, Users } from '@phosphor-icons/react';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
    PointElement,
    LineElement
} from 'chart.js';
import moment from 'moment';
import { FormEventHandler, useContext, useEffect } from 'react';
import { Line } from 'react-chartjs-2';
ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
    PointElement,
    LineElement
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
        usersByDay?: Record<string, number>
        p_start?: string
        p_end?: string
    }
    start: string
    end: string
}

export default function Dashboard(props: Props) {
    const { data = {}, start, end } = props;
    const { users = null } = data || {};
    const form = useForm({
        start: start,
        end: end
    });
    const { post, setData, data: form_data } = form;

    let days: Record<string, number> = {};
    let day = moment(start);

    while (day.isBefore(moment(end))) {
        let key = day.format('YYYY-MM-DD');
        days[key] = Math.floor(Math.random() * 1000);
        day.add(1, 'day');
    }

    let daysLabel = Object.keys(days ?? {}).map(d => moment(d, 'YYYY-MM-DD').format('D.'));
    let max = Math.max(...Object.values(days ?? {}), 1);

    const dummyData = {
        labels: daysLabel,
        datasets: [
            {
                label: 'Profit',
                data: Object.values(days ?? {}),
                stack: 'Stack 1',
                borderColor: '#00686E',
                backgroundColor: '#00686E',
                borderWidth: 2,
                borderRadius: 4,
                borderSkipped: false,
                barThickness: 16
            },
        ]
    };

    let options = {
        responsive: true,
        plugins: {
            legend: {
                display: false,
                position: 'top' as const,
            },
            title: {
                display: false,
                text: 'Chart.js Bar Chart',
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
                    callback: function (value: any) {
                        return Math.floor(value) + ' Kč';
                    },
                    stepSize: max / 2
                },
                grid: {
                    display: false
                }
            }
        },
        maintainAspectRatio: false,
    };

    const positive = (value?: number) => (value ?? -1) > 0 ? 'text-app-secondary' : 'text-[#E66C6C] rotate-180';

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('?');
    }

    return (
        <AdminLayout title='Dashboard | Winfolio'>
            <div className='flex flex-col bg-white rounded-xl h-min p-24px max-w-limit w-full'>
                {/* Date selection form */}
                <div className='h-full'>
                    <Form className='flex items-center gap-8px' form={form} onSubmit={submit}>
                        <div className='w-[130px]'>
                            <DateInput placeholder='Vyberte datum' name='start' />
                        </div>
                        -
                        <div className='w-[130px]'>
                            <DateInput placeholder='Vyberte datum' name='end' />
                        </div>
                        <Submit />
                    </Form>

                    {/* Bar chart */}
                    <div className='w-full mt-24px'>
                        <Line height={500} options={options} data={dummyData} />
                    </div>
                </div>
            </div>

            {/* Users statistics section */}
            <div className='flex gap-16px max-w-limit w-full mt-16px'>
                <div className='bg-white rounded-md p-24px w-full'>
                    <div className='flex gap-16px items-center'>
                        <div className='w-48px h-48px bg-app-lightbackground flex items-center justify-center rounded-md'>
                            <Users size={24} color='#00686E' />
                        </div>
                        <div className='font-bold text-xl leading-[24px]'>Uživatelé</div>
                    </div>

                    {/* User statistics cards */}
                    <div className='flex gap-16px mt-16px'>
                        {/* Total users */}
                        <div className='p-16px bg-app-lightbackground w-full rounded-md flex flex-col items-center justify-center'>
                            <div className='font-bold'>Celkem</div>
                            <div className='font-extrabold text-xl'>{users?.total || 0}</div>
                            <div className='flex items-center gap-12px'>
                                <div className='flex items-center gap-4px'>
                                    <Triangle weight='fill' size={12} className={positive(users?.percentage)} />
                                    <div>{users?.percentage || 0}%</div>
                                </div>
                                <div className='flex items-center gap-4px'>
                                    <Triangle weight='fill' size={12} className={positive(users?.diff)} />
                                    <div>{users?.diff || 0}</div>
                                </div>
                            </div>
                        </div>

                        {/* Active users */}
                        <div className='p-16px bg-app-lightbackground w-full rounded-md flex flex-col items-center justify-center'>
                            <div className='font-bold'>Aktivní</div>
                            <div className='font-extrabold text-xl'>{users?.active_total || 0}</div>
                            <div className='flex items-center gap-12px'>
                                <div className='flex items-center gap-4px'>
                                    <Triangle weight='fill' size={12} className={positive(users?.active_percentage)} />
                                    <div>{users?.active_percentage || 0}%</div>
                                </div>
                                <div className='flex items-center gap-4px'>
                                    <Triangle weight='fill' size={12} className={positive(users?.active_diff)} />
                                    <div>{users?.active_diff || 0}</div>
                                </div>
                            </div>
                        </div>

                        {/* New users */}
                        <div className='p-16px bg-app-lightbackground w-full rounded-md flex flex-col items-center justify-center'>
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
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}