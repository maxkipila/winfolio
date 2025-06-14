import React, { ReactNode } from 'react'
import OrderBy from '../forms/inputs/OrderBy'

interface Props {
    children?: ReactNode
    order_by?: string
    custom?: string
}

function Th(props: Props) {
    const { children, order_by } = props

    return (
        <th className={`p-8px text-left last:text-right align-middle whitespace-nowrap ${props.custom}`}>
            <div className='flex items-center '>
                {children}
                {
                    order_by &&
                    <OrderBy value={order_by} name={''} />
                }
            </div>
        </th>
    )
}

export default Th
