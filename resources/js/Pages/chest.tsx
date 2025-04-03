import ProductCard from '@/Fragments/ProductCard'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { ArrowUpRight } from '@phosphor-icons/react'
import React, { useState } from 'react'
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
interface Props { }


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
    const { } = props
    let [portfolio, setPortfolio] = useState(true)
    const [products, button, meta, setItems] = useLazyLoad<Product>('products');
    return (
        <AuthenticatedLayout>
            <div className='w-full pt-32px mob:pt-24px px-24px'>
                <div className='flex justify-between mob:flex-col-reverse mob:gap-16px'>
                    <div className='flex items-center w-full justify-between'>
                        <div className='flex items-center'>
                            <div className='font-bold text-4xl'>$</div>
                            <div className='font-bold text-6xl'>1 102</div>
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
                    <Line height={102} options={options} data={dummyData} />
                </div>
                <div className='flex'>
                    <div className='flex flex-shrink-0 items-center'>
                        <div className={`py-12px px-48px text-lg font-bold border-b-2 ${portfolio ? "text-black border-black" : "text-[#999999] border-[#E6E6E6]"}`}>Portfolio</div>
                        <div className={`py-12px px-48px text-lg font-bold border-b-2 ${!portfolio ? "text-black border-black" : "text-[#999999] border-[#E6E6E6]"}`}>Wishlist</div>
                    </div>
                    <div className='w-full border-b-2 border-[#E6E6E6]'></div>
                </div>
                <div className='mt-24px grid grid-cols-3 gap-24px mob:grid-cols-1'>
                    {
                        products?.map((s)=>
                            <ProductCard wide {...s} />
                        )
                    }
                    {/* <ProductCard wide />
                    <ProductCard wide />
                    <ProductCard wide />
                    <ProductCard wide />
                    <ProductCard wide /> */}
                    {/* <div className='font-bold text-xl'>No products</div> */}
                </div>
            </div>
        </AuthenticatedLayout>
    )
}

export default Chest
