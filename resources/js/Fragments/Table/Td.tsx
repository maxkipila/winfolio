import React, { ReactNode } from 'react'

interface Props {
    children?: ReactNode
}

function Td(props: Props) {
    const { children } = props

    return (
        <td className='py-8px  px-12px group-odd:bg-[#F5F5F5] first:rounded-l last:rounded-r font-bold last:text-right text-left'>
            {children}
        </td>
    )
}

export default Td
