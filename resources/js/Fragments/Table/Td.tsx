import React, { ReactNode } from 'react'

interface Props {
    children?: ReactNode
}

function Td(props: Props) {
    const { children } = props

    return (
        <td className='py-8px px-12px group-odd:bg-[#E5F6F7] first:rounded-l last:rounded-r font-bold last:text-right text-left'>
            {children}
        </td>
    )
}

export default Td
