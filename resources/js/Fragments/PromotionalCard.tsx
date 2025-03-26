import Img from '@/Components/Image'
import React from 'react'

interface Props { }

function PromotionalCard(props: Props) {
    const { } = props

    return (
        <div className='p-32px border-2 border-black mt-32px'>
            <div className='flex justify-between w-full items-center'>
                <div className='font-bold text-xl'>About Animal Crossing Promotional</div>
                <Img src="/assets/img/animal-crossing.png" />
            </div>
            <div className='text-[#4D4D4D]'>
                The LEGO Animal Crossing theme was introduced in 2024 and consists of sets based on the popular life simulation video game series developed and published by Nintendo. In the game, players live in a village inhabited by anthropomorphic animals and engage in various activities like fishing, bug catching, and fossil hunting. The LEGO sets, just like the video game is known for its charming, relaxed gameplay and has garnered a dedicated fanbase since its debut.
            </div>
            <div className='mt-16px text-[#4D4D4D]'>Promotional was introduced in 2024 and currently consists of 3 sets.</div>
        </div>
    )
}

export default PromotionalCard
