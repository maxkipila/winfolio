import React from 'react'
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
import { externalTooltipHandler } from '@/Pages/chest';
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

while (day.isBefore(moment().add('days', "14"))) {
    let key = day.format('YYYY-MM-DD');
    days[key] = Math.floor(Math.random() * 1000);
    day.add(1, 'day');
}

let daysLabel = Object.keys(days ?? {}).map(d => moment(d, 'YYYY-MM-DD').format('D.'));
let max = Math.max(...Object.values(days ?? {}), 1);




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
            display: true,
            grid: {
                display: false
            }
        },
        y: {
            display: true,
            ticks: {
                callback: function (value: any) {
                    return Math.floor(value) + ' Kč';
                },
                stepSize: max / 4
            },
            grid: {
                display: true
            }
        }
    },
    maintainAspectRatio: false,
};

interface Props {
    product: Product,
    priceHistory: any
}

function PointsGraph(props: Props) {
    const { product, priceHistory } = props

    if (!priceHistory || !priceHistory.history) {
        return <div className='mt-32px'>Není dostatek dat pro zobrazení grafu</div>;
    }

    console.log(priceHistory, 'priceHistory')
    /* let prevDates = Object.keys(priceHistory.history).sort() */
    let prevValues = (priceHistory.history.sort((a, b) => a.date > b.date ? 1 : -1)) as any
    /* let historicValues = prevValues.flatMap((h) => h[0]) */
    let dates = prevValues.flatMap((pV) => pV.date)
    let values = prevValues.flatMap((pV) => pV.value) as Array<number>

    let historicData = Array.isArray(priceHistory.history)
        ? priceHistory.history
        : Object.values(priceHistory.history).flat();

    /*  let dates = historicData.map(item => item.date);
     let values = historicData.map(item => Number(item.value)) as Array<number>; */

    let avarageValue = values.reduce((p, c) => c + p, 0) / values?.length
    let max = Math.max(...values)
    let min = Math.min(...values)
    let last = values[values.length - 1]
    let isGrowing = last > avarageValue
    let difference = (max - min) / values?.length
    let seventh = isGrowing ? last + difference : last - difference
    let nextMonth = moment(dates[dates?.length - 1]).add(1, 'M')
    let untilYear = 11 - nextMonth.month()
    let amountOfMonths = untilYear + 24
    let nextDates = [moment(dates[dates?.length - 1]).format('YYYY-MM-DD')]
    let nextValues = [last]

    for (let index = 0; index < amountOfMonths; index++) {

        nextDates.push(moment(nextDates[nextDates.length - 1]).add(1, 'M').format('YYYY-MM-DD'))
    }

    for (let index = 0; index < amountOfMonths; index++) {
        if (isGrowing) {
            nextValues.push(nextValues[nextValues?.length - 1] + difference)
        } else {
            nextValues.push(nextValues[nextValues?.length - 1] - difference)
        }

    }


    let graphDates = [...dates, ...nextDates]
    let graphValues = [...values, ...nextValues]
    let OsaDiff = max - avarageValue
    let osa1 = graphValues.map((g) => g + OsaDiff)
    let osa2 = graphValues.map((g) => g - OsaDiff)
    console.log(graphDates, graphValues, 'osa:')

    const dummyData = {
        labels: graphDates,
        datasets: [

            {
                label: 'Total: ',
                data: osa2,
                stack: 'Stack 1',
                borderColor: 'transparent',
                backgroundColor: 'rgba(196, 234, 178, 0.3)',
                borderWidth: 2,
                borderRadius: 4,
                borderSkipped: false,
                barThickness: 16,
                pointHitRadius: 0,
                pointBorderWidth: 0,
                pointRadius: 0,

                fill: '1'
            },
            {
                label: 'Profit',
                data: graphValues,
                stack: 'Stack 1',
                borderColor: '#16A049',
                backgroundColor: '#46BD0F80',
                borderWidth: 0,
                borderRadius: 4,
                borderSkipped: false,
                barThickness: 16,
                pointHitRadius: 2,
                pointBorderWidth: 0,
                pointRadius: 4,
                fill: false
            },
            {
                label: 'Total: ',
                data: osa1,
                stack: 'Stack 1',
                borderColor: 'transparent',
                backgroundColor: 'rgba(196, 234, 178, 0.3)',
                borderWidth: 2,
                borderRadius: 4,
                borderSkipped: false,
                barThickness: 16,
                pointHitRadius: 0,
                pointBorderWidth: 0,
                pointRadius: 0,

                fill: '-1'
            }
            // {
            //     label: 'Filled',
            //     backgroundColor: 'rgba(196, 234, 178, 0.3)',
            //     borderColor: 'rgba(196, 234, 178, 0.3)',
            //     data: Object.values(days ?? {}),
            //     fill: true,
            //   }
        ]
    };

    return (
        <div className='mt-32px'>
            {
                //@ts-expect-error
                <Line height={180} options={options} data={dummyData} />
            }

        </div>
    )
}

export default PointsGraph
