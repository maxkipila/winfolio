import React, { ReactNode } from 'react'

interface Props {
    children?: ReactNode
}

function Td(props: Props) {
    const { children } = props

    return (
        <td className='px-12px group-odd:bg-[#F5F5F5] leading-[16px] text-[14px] first:rounded-l font-medium last:rounded-r last:text-right text-left'>
            {children}
        </td>
    )
}

export default Td
