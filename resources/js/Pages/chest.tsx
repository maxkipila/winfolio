import ProductCard from '@/Fragments/ProductCard'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { ArrowUpRight, LegoSmiley } from '@phosphor-icons/react'
import React, { useContext, useState } from 'react'
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
    PointElement,
    LineElement,
    Filler
} from 'chart.js';
import { Line } from 'react-chartjs-2';
import moment from 'moment';
import useLazyLoad from '@/hooks/useLazyLoad';
import usePageProps from '@/hooks/usePageProps';
import { Button } from '@/Fragments/UI/Button';
import { ModalsContext } from '@/Components/contexts/ModalsContext';
import { MODALS } from '@/Fragments/Modals';
ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
    PointElement,
    LineElement,
    Filler
);
interface Props {
    user_products: Array<Product>
}


let days: Record<string, number> = {};
let day = moment();

while (day.isBefore(moment().add('days', "30"))) {
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
            borderColor: '#16A049',
            backgroundColor: '#46BD0F80',
            borderWidth: 2,
            borderRadius: 4,
            borderSkipped: false,
            barThickness: 16,
            pointHitRadius: 0,
            pointBorderWidth: 0,
            pointRadius: 0,
            fill: true
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
            display: false,
            grid: {
                display: false
            }
        },
        y: {
            display: false,
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

function Chest(props: Props) {
    const { user_products } = props
    let [portfolio, setPortfolio] = useState(true)
    const [products, button, meta, setItems] = useLazyLoad<Product>('products');
    const { auth } = usePageProps<{ auth: { user: User } }>();
    let { open } = useContext(ModalsContext)
    let current_value = 0
    let all_prices = user_products?.flatMap((up) => up.prices)
    let price_values = all_prices?.flatMap((ap) => ap.value)
    let current_prices = user_products?.flatMap((up) => up.latest_price?.value)
    current_prices?.map((cp) => current_value += parseInt(cp, 10))
    let dates = all_prices?.flatMap((dt) => moment(dt.created_at, 'YYYY-MM-DD').format('D.'))



    const data = {
        labels: dates,
        datasets: [
            {
                label: 'Profit',
                data: price_values,
                stack: 'Stack 1',
                borderColor: '#16A049',
                backgroundColor: '#46BD0F80',
                borderWidth: 2,
                borderRadius: 4,
                borderSkipped: false,
                barThickness: 16,
                pointHitRadius: 0,
                pointBorderWidth: 0,
                pointRadius: 0,
                fill: true
            },
        ]
    };
    return (
        <AuthenticatedLayout>
            <div className='w-full pt-32px mob:pt-24px px-24px min-h-screen-no-header'>
                <div className='flex justify-between mob:flex-col-reverse mob:gap-16px'>
                    <div className='flex items-center w-full justify-between'>
                        <div className='flex items-center'>
                            <div className='font-bold text-4xl'>$</div>
                            <div className='font-bold text-6xl'>{current_value}</div>
                            <div className='text-[#999999] font-bold text-4xl'>.13</div>
                        </div>
                        <div className='bg-[#46BD0F] flex items-center  py-2px rounded w-[78px] text-center justify-center'>
                            <ArrowUpRight color="white" />
                            <div className='text-white'>+4,1 %</div>
                        </div>
                    </div>
                    <div className='w-full flex items-center justify-end'>
                        <div className='border-2 border-black'>
                            <div className='font-bold text-lg text-center px-24px py-4px bg-[#F7AA1A] '>This Week</div>
                            <div className='font-bold text-lg text-center px-24px py-4px border-t-2 border-b-2 border-black mob:hidden'>This Month</div>
                            <div className='font-bold text-lg text-center px-24px py-4px mob:hidden'>This Year</div>
                        </div>
                    </div>
                </div>
                <div className='w-full mt-24px'>
                    <Line height={102} options={options} data={data} />
                </div>
                <div className='flex'>
                    <div className='flex flex-shrink-0 items-center'>
                        <div onClick={() => { setPortfolio(true) }} className={`cursor-pointer py-12px px-48px text-lg font-bold border-b-2 ${portfolio ? "text-black border-black" : "text-[#999999] border-[#E6E6E6]"}`}>Portfolio</div>
                        <div onClick={() => { setPortfolio(false) }} className={`cursor-pointer py-12px px-48px text-lg font-bold border-b-2 ${!portfolio ? "text-black border-black" : "text-[#999999] border-[#E6E6E6]"}`}>Wishlist</div>
                    </div>
                    <div className='w-full border-b-2 border-[#E6E6E6]'></div>
                </div>
                {
                    portfolio ?
                        <>
                            {
                                auth?.user?.products?.length > 0 ?
                                    <div className='mt-24px grid grid-cols-3 gap-24px mob:grid-cols-1'>
                                        {
                                            auth?.user?.products?.map((s) =>
                                                <ProductCard wide {...s} />
                                            )
                                        }
                                    </div>
                                    :
                                    <div className='w-full flex items-center justify-center h-full min-h-full mt-[180px]'>
                                        <div className='h-full w-full flex-shrink-0'>
                                            <LegoSmiley className='mx-auto' size={64} />
                                            <div className='mt-24px font-bold text-xl text-center'>Zatím neexistují žádná data</div>
                                            <div className='my-16px font-nunito text-[#4D4D4D] text-center'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non.</div>
                                            <Button className='max-w-150px mx-auto' href={"#"} onClick={(e) => { e.preventDefault(); open(MODALS.PORTFOLIO) }}>Vytvořit portfolio</Button>
                                        </div>
                                    </div>
                            }
                        </>
                        :
                        <div className='mt-24px grid grid-cols-3 gap-24px mob:grid-cols-1'>
                            {
                                auth?.user?.favourites?.map((s) =>
                                    <ProductCard wide {...s.favourite} />
                                )
                            }
                        </div>
                }
            </div>
        </AuthenticatedLayout>
    )
}

export default Chest
