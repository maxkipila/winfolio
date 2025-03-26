import { BellSimple, Medal } from '@phosphor-icons/react'
import React from 'react'

function NotifCard() {

    return (
        <div className='bg-[#F5F5F5] flex justify-between items-center px-8px py-14px gap-8px'>
            <div className='bg-white w-40px h-40px rounded-full flex items-center justify-center'>
                <Medal size={24} />
            </div>

            <div>
                <div className='font-nunito font-bold'>Název nového rekordu</div>
                <div className='font-nunito text-[#4D4D4D]'>Dnes</div>
            </div>

            <div className='bg-[#ED2E1B] h-12px w-12px rounded-full'></div>
        </div>
    )
}

interface Props { }

function NotificationsModal(props: Props) {
    const { } = props

    return (
        <div className="bg-black bg-opacity-80  fixed top-0 left-0 w-full h-screen items-start justify-end flex z-max p-24px mob:pb-0" >
            <div>
                <div className='relative mb-21px'>
                    <BellSimple className='ml-auto' weight='fill' color="white" size={24} />
                    <div className='bg-[#ED2E1B] w-8px h-8px rounded-full absolute top-4px right-4px'></div>
                </div>
                <div className="bg-white p-24px max-w-sm mob:w-full border-2 border-black">
                    <div className='font-bold text-xl'>Nové</div>
                    <div className='flex flex-col gap-16px'>
                        <NotifCard />
                        <NotifCard />
                    </div>
                    <div className='font-bold text-xl mt-40px'>Starší</div>
                    <div className='flex flex-col gap-16px'>
                        <NotifCard />
                        <NotifCard />
                    </div>
                </div>
            </div>
        </div>
    )
}

export default NotificationsModal
