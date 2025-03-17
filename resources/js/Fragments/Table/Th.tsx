import React, { ReactNode } from 'react'
import OrderBy from '../forms/inputs/OrderBy'

interface Props {
    children?: ReactNode
    order_by?: string
}

function Th(props: Props) {
    const { children, order_by } = props

    return (
        <th className='p-12px text-left last:text-right align-middle whitespace-nowrap'>
            {children}
            {
                order_by &&
                <OrderBy value={order_by} name={''} />
            }
        </th>
    )
}

export default Th
