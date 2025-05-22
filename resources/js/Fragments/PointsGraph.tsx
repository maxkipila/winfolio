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
    Filler,
    TimeScale,
    TimeUnit,
    TimeSeriesScale,
    DateAdapter,

} from 'chart.js';
import { Line } from 'react-chartjs-2';
import moment from 'moment';
import { externalTooltipHandler } from '@/Pages/chest';
import { callback } from 'chart.js/helpers';
ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
    PointElement,
    LineElement,
    Filler,
    TimeScale
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






interface Props {
    product: Product,
    priceHistory: any
}

function PointsGraph(props: Props) {
    const { product, priceHistory } = props

    if (!priceHistory || !priceHistory.history) {
        return <div className='mt-32px'>Není dostatek dat pro zobrazení grafu</div>;
    }

    // console.log(priceHistory, 'priceHistory')
    function getMonths(number: number) {
        if (number / 3 > 6) {
            return 6
        } else {
            return Math.floor(number / 3)
        }

    }
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
    let amountOfMonths = getMonths(prevValues.length)
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
    let formatedGraphDates = graphDates.map((gD) => moment(gD).format('MM. YYYY'))
    console.log('formatedDates', formatedGraphDates)
    let graphValues = [...values]
    let osaValues = [...values, ...nextValues]
    let down = avarageValue - min
    let up = max - avarageValue
    let shouldRise = up > down
    let OsaDiff = max - min
    function getOsaValues(number: number, avarage: number) {
        if (number < avarage) {
            return avarage
        } else {
            return number
        }
    }
    let osa1 = osaValues.map((g, i) => getOsaValues((graphValues[0] + OsaDiff) + (shouldRise ? (i * (OsaDiff / 100)) : (-i * (OsaDiff / 100))), OsaDiff))
    let osa2 = osaValues.map((g, i) => getOsaValues(0 + (shouldRise ? (i * (OsaDiff / 100)) : (-i * (OsaDiff / 100))), 0))
    // console.log( 'osa:', graphDates)

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
                // ticks: {
                //     callback: function (value: any) {
                //         return value;
                //     },
                //     stepSize: max / 4
                // },

                ticks: {
                    callback: (value, index, values) => moment(graphDates[index]).format('MM. YYYY'),
                    maxTicksLimit: 4,

                },
                grid: {
                    display: false
                }
            },
            y: {
                display: true,
                ticks: {
                    callback: function (value: any) {

                        return Math.floor(value) + ' $';
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

    const dummyData = {
        labels: graphDates,
        datasets: [

            {
                label: 'Total: ',
                data: osa2,
                stack: 'Stack 2',
                borderColor: 'transparent',
                backgroundColor: 'rgba(196, 234, 178, 0.15)',
                borderWidth: 2,
                borderRadius: 4,
                borderSkipped: false,
                barThickness: 16,
                pointHitRadius: 0,
                pointBorderWidth: 0,
                pointRadius: 0,

                fill: '2'
            },
            {
                label: 'Price',
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
                stack: 'Stack 3',
                borderColor: 'transparent',
                backgroundColor: 'rgba(196, 234, 178, 0.15)',
                borderWidth: 2,
                borderRadius: 4,
                borderSkipped: false,
                barThickness: 16,
                pointHitRadius: 0,
                pointBorderWidth: 0,
                pointRadius: 0,

                fill: '-2'
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
