import ProductCard from '@/Fragments/ProductCard'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { ArrowDownRight, ArrowUpRight, LegoSmiley } from '@phosphor-icons/react'
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
import { t } from '@/Components/Translator';
import { Head, router } from '@inertiajs/react';
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
    range: string,
    portfolioHistory: Array<{ date: string, value: number }>,
    portfolioStats: {
        current_value: number,
        growth_percentage: number,
        growth_value: number,
        initial_value: number
    }
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
            // label: 'Profit',
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

export const getOrCreateTooltip = (chart) => {
    let tooltipEl = chart.canvas.parentNode.querySelector('div');

    if (!tooltipEl) {
        tooltipEl = document.createElement('div');
        tooltipEl.style.background = 'white';
        tooltipEl.style.border = '2px solid black'
        tooltipEl.style.color = 'black';
        tooltipEl.style.opacity = 1;
        tooltipEl.style.pointerEvents = 'none';
        tooltipEl.style.position = 'absolute';
        tooltipEl.style.transform = 'translate(-50%, -100%)';
        tooltipEl.style.transition = 'all .1s ease';

        const table = document.createElement('table');
        table.style.margin = '0px';

        tooltipEl.appendChild(table);
        chart.canvas.parentNode.appendChild(tooltipEl);
    }

    return tooltipEl;
};

export const externalTooltipHandler = (context) => {
    // Tooltip Element
    const { chart, tooltip } = context;
    const tooltipEl = getOrCreateTooltip(chart);

    // Hide if no tooltip
    if (tooltip.opacity === 0) {
        tooltipEl.style.opacity = 0;
        return;
    }

    // Set Text
    if (tooltip.body) {
        const titleLines = tooltip.title || [];
        const bodyLines = tooltip.body.map(b => b.lines);

        const tableHead = document.createElement('thead');

        titleLines.forEach(title => {
            const tr = document.createElement('tr');
            tr.style.borderWidth = '0';

            const th = document.createElement('th');
            th.style.borderWidth = '0';
            const text = document.createTextNode(`${moment(title).format('DD. MM. YYYY')}`);
            th.style.fontWeight = '300';
            th.style.textAlign = 'left';
            th.appendChild(text);
            tr.appendChild(th);
            tableHead.appendChild(tr);
        });

        const tableBody = document.createElement('tbody');
        bodyLines.forEach((body, i) => {
            // const colors = tooltip.labelColors[i];

            // const span = document.createElement('span');
            // span.style.background = colors.backgroundColor;
            // span.style.borderColor = colors.borderColor;
            // span.style.borderWidth = '2px';
            // span.style.marginRight = '10px';
            // span.style.height = '10px';
            // span.style.width = '10px';
            // span.style.display = 'inline-block';

            const tr = document.createElement('tr');
            tr.style.backgroundColor = 'inherit';
            tr.style.borderWidth = '0';

            const td = document.createElement('td');
            td.style.fontWeight = '700';

            td.style.borderWidth = '0';

            const text = document.createTextNode(body);

            // td.appendChild(span);
            td.appendChild(text);
            tr.appendChild(td);
            tableBody.appendChild(tr);
        });

        const tableRoot = tooltipEl.querySelector('table');

        // Remove old children
        while (tableRoot.firstChild) {
            tableRoot.firstChild.remove();
        }

        // Add new children
        tableRoot.appendChild(tableHead);
        tableRoot.appendChild(tableBody);
    }

    const { offsetLeft: positionX, offsetTop: positionY } = chart.canvas;

    // Display, position, and set styles for font
    tooltipEl.style.opacity = 1;
    tooltipEl.style.left = positionX + tooltip.caretX + 'px';
    tooltipEl.style.top = positionY + tooltip.caretY + 'px';
    tooltipEl.style.font = tooltip.options.bodyFont.string;
    tooltipEl.style.padding = tooltip.options.padding + 'px ' + tooltip.options.padding + 'px';
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
        tooltip: {
            enabled: false,
            position: 'nearest',
            external: externalTooltipHandler
        }

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
    const { user_products, range, portfolioHistory, portfolioStats } = props
    let [portfolio, setPortfolio] = useState(true)
    const [products, button, meta, setItems] = useLazyLoad<Product>('products');
    const { auth } = usePageProps<{ auth: { user: User } }>();
    let { open } = useContext(ModalsContext)
    let current_value = 0
    let all_prices = user_products?.flatMap((up) => up?.prices)
    let price_values = all_prices?.flatMap((ap) => ap?.value)
    let current_prices = user_products?.flatMap((up) => up.latest_price?.value)
    current_prices?.map((cp) => current_value += parseInt(cp, 10))
    let dates = all_prices?.flatMap((dt) => moment(dt?.created_at, 'YYYY-MM-DD').format('D.'))

    let historyDates = portfolioHistory.flatMap((pH) => pH.date)
    let historyValues = portfolioHistory.flatMap((pH) => pH.value)


    const data = {
        labels: historyDates,
        datasets: [
            {
                label: 'Total: ',
                data: historyValues,
                stack: 'Stack 1',
                borderColor: '#16A049',
                backgroundColor: '#46BD0F80',
                borderWidth: 2,
                borderRadius: 4,
                borderSkipped: false,
                barThickness: 16,
                pointHitRadius: 2,
                pointBorderWidth: 2,
                pointRadius: 2,

                fill: true
            },
        ]
    };
    return (
        <AuthenticatedLayout>
            <Head title="Chest | Winfolio" />
            <div className='w-full pt-32px mob:pt-24px px-24px min-h-screen-no-header'>
                <div className='flex justify-between mob:flex-col-reverse mob:gap-16px'>
                    <div className='flex items-center w-full justify-between'>
                        <div className='flex items-center'>
                            <div className='font-bold text-4xl'>$</div>
                            <div className='font-bold text-6xl'>{Math.round(portfolioStats.current_value * 100) / 100}</div>
                            {/* <div className='text-[#999999] font-bold text-4xl'>.13</div> */}
                        </div>
                        {
                            user_products?.length > 0 &&
                            <div className={`${portfolioStats?.growth_percentage >= 0 ? "bg-[#46BD0F]" : "bg-[#ED2E1B]"}  flex items-center  py-2px rounded w-[78px] text-center justify-center`}>
                                {
                                    portfolioStats?.growth_percentage >= 0 ?
                                        <ArrowUpRight color="white" />
                                        :
                                        <ArrowDownRight color="white" />
                                }
                                <div className='text-white'>{Math.floor(portfolioStats.growth_percentage)} %</div>
                            </div>
                        }
                    </div>
                    <div className='w-full flex items-center justify-end'>
                        <div className='border-2 border-black'>
                            <div onClick={() => { router.post(route('chest', { range: 'week' })) }} className={`cursor-pointer font-bold text-lg text-center px-24px py-4px ${range == "week" ? " bg-[#F7AA1A] " : " "}`}>{t('Tento týden')}</div>
                            <div onClick={() => { router.post(route('chest', { range: 'month' })) }} className={`cursor-pointer font-bold text-lg text-center px-24px py-4px border-t-2 border-b-2 border-black ${range == "month" ? " bg-[#F7AA1A] " : " "}`}>{t('Tento měsíc')}</div>
                            <div onClick={() => { router.post(route('chest', { range: 'year' })) }} className={`cursor-pointer font-bold text-lg text-center px-24px py-4px ${range == "year" ? " bg-[#F7AA1A] " : ""} `}>{t('Tento rok')}</div>
                        </div>
                    </div>
                </div>
                {
                    user_products.length > 0 &&
                    <div className='w-full mt-24px'>
                        {
                            //@ts-expect-error
                            <Line height={102} options={options} data={data} />
                        }

                    </div>
                }
                <div className='flex'>
                    <div className='flex flex-shrink-0 items-center'>
                        <div onClick={() => { setPortfolio(true) }} className={`cursor-pointer py-12px px-48px text-lg font-bold border-b-2 ${portfolio ? "text-black border-black" : "text-[#999999] border-[#E6E6E6]"}`}>{t("Portfolio")}</div>
                        <div onClick={() => { setPortfolio(false) }} className={`cursor-pointer py-12px px-48px text-lg font-bold border-b-2 ${!portfolio ? "text-black border-black" : "text-[#999999] border-[#E6E6E6]"}`}>{t('Wishlist')}</div>
                    </div>
                    <div className='w-full border-b-2 border-[#E6E6E6]'></div>
                </div>
                {
                    portfolio ?
                        <>
                            {
                                user_products?.length > 0 ?
                                    <div className='mt-24px grid grid-cols-3 gap-24px mob:grid-cols-1'>
                                        {
                                            user_products?.map((s) =>
                                                <ProductCard wide {...s} />
                                            )
                                        }
                                    </div>
                                    :
                                    <div className='w-full flex items-center justify-center h-full min-h-full mt-[180px]'>
                                        <div className='h-full w-full flex-shrink-0'>
                                            <LegoSmiley className='mx-auto' size={64} />
                                            <div className='mt-24px font-bold text-xl text-center'>{t('Zatím neexistují žádná data')}</div>
                                            <div className='my-16px font-nunito text-[#4D4D4D] text-center'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non.</div>
                                            <Button className='max-w-150px mx-auto' href={"#"} onClick={(e) => { e.preventDefault(); open(MODALS.PORTFOLIO) }}>{t('Vytvořit portfolio')}</Button>
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
