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


const dummyData = {
    labels: daysLabel,
    datasets: [
        {
            label: 'Profit',
            data: Object.values(days ?? {}),
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
        // {
        //     label: 'Filled',
        //     backgroundColor: 'rgba(196, 234, 178, 0.3)',
        //     borderColor: 'rgba(196, 234, 178, 0.3)',
        //     data: Object.values(days ?? {}),
        //     fill: true,
        //   }
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

interface Props { }

function PointsGraph(props: Props) {
    const { } = props

    return (
        <div className='mt-32px'>
            <Line height={180} options={options} data={dummyData} />
        </div>
    )
}

export default PointsGraph
