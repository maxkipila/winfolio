import React from 'react'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, PointElement, LineElement, Filler, TimeScale, TimeUnit, TimeSeriesScale, DateAdapter, } from 'chart.js';
import { Line } from 'react-chartjs-2';
import moment from 'moment';
import { externalTooltipHandler } from '@/Pages/chest';
import { callback } from 'chart.js/helpers';
import * as math from 'mathjs';
import { SimpleLinearRegression } from 'ml-regression';

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

    const times = dates.map(d => moment(d).toDate().getTime());
    const t0 = Math.min(...times);
    const t1 = Math.max(...times);
    const t_days = Math.max(Math.round(((t1 - t0) / (1000 * 60 * 60 * 24 * 30)) * 0.2), 1);

    const prediction = moment();

    const predictedDates = Array(t_days).fill(0).map((_, i) => prediction.clone().add(i, 'month').format('YYYY-MM-DD'));

    let graphDates = [...dates];
    let graphValues = [...values];

    const generateBand = () => {
        let dates = graphDates.map((date) => new Date(date))
        const values = graphValues

        // Turn dates into numeric: days since first observation
        let t0 = Math.min(...dates.map(d => d.getTime()));
        let t_days = dates.map(d => Math.round((d.getTime() - t0) / (1000 * 60 * 60 * 24)));

        // === FIT LINEAR MODEL ===
        let x = t_days;
        let y = values;

        const regression = new SimpleLinearRegression(x, y);
        graphDates = [...graphDates, ...predictedDates];

        dates = graphDates.map((date) => new Date(date))
        t0 = Math.min(...dates.map(d => d.getTime()));
        t_days = dates.map(d => Math.round((d.getTime() - t0) / (1000 * 60 * 60 * 24)));

        // === FIT LINEAR MODEL ===
        x = t_days;

        const y_pred = x.map(xi => regression.predict(xi));


        // === BAND ===
        const coverage = 0.90;
        const residuals = y.map((v, i) => v - y_pred[i]);
        const low_pct = (1 - coverage) / 2 * 100;
        const high_pct = (1 + coverage) / 2 * 100;

        const r_lo = math.quantileSeq(residuals, low_pct / 100);
        const r_hi = math.quantileSeq(residuals, high_pct / 100);

        const y_lower = y_pred.map(v => v + r_lo);
        const y_upper = y_pred.map(v => v + r_hi);

        return { y_pred, y_lower, y_upper };
    }

    const { y_pred, y_lower, y_upper } = generateBand();

    // Make sure these arrays are the same length as your dates
    // const bandLabels = prevValues.map((pV) => pV.date); // or use your graphDates if you want to extend

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
                data: y_lower,
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
                data: y_upper,
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
            },
           /*  {
                label: 'Linear Fit',
                data: y_pred,
                stack: 'Stack 4',
                borderColor: '#16A049',
                backgroundColor: 'rgba(22,160,73,0.1)',
                fill: false,
                pointRadius: 0,
                borderWidth: 2,
                order: 2,
            }, */

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
