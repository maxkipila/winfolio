import { ModalsContext } from '@/Components/contexts/ModalsContext'
import { ShieldCheck, X } from '@phosphor-icons/react'
import React, { useContext } from 'react'
import { Button } from './UI/Button'
import { router } from '@inertiajs/react'
import { MODALS } from './Modals'


interface ClaimBadgeCardProps extends Award {

}

function ClaimBadgeCard(props: ClaimBadgeCardProps) {
    const { id, name, description } = props
    let { open, close } = useContext(ModalsContext)
    function claim() {
        router.post(route('awards.claim', { award: id }), {}, {
            onSuccess: () => {
                open(MODALS.SUCCESS);
            }
        })
    }
    return (
        <div className='border-2 border-black p-16px py-48px flex flex-col items-center justify-center w-1/2'>
            <div className='w-80px h-80px bg-black bg-opacity-35 rounded-full'></div>
            <div className='mt-16px font-bold text-xl'>{name}</div>
            <div className='font-nunito my-16px'>{description}</div>
            <Button icon={<ShieldCheck size={24} />} onClick={(e) => { e.preventDefault(); claim(); }} href="#">Claim Badge</Button>
        </div>
    )
}


interface Props {
    awards: Array<Award>
}

function UnclaimedModal(props: Props) {
    const { awards } = props
    let { close } = useContext(ModalsContext)
    console.log(awards)
    return (
        <div onClick={() => { close() }} className="bg-black bg-opacity-80 fixed top-0 left-0 w-full h-screen items-center justify-center mob:block mob:max-h-full flex z-max  mob:pb-0">
            <div onClick={(e) => { e.stopPropagation(); }} className='bg-white border-2 border-black w-full h-screen overflow-y-auto'>
                <div className='flex items-end justify-end'>
                    <div onClick={() => { close() }} className='w-40px h-40px bg-black flex items-center justify-center'>
                        <X color='white' size={24} />
                    </div>
                </div>
                <div className='flex items-center overflow-x-auto justify-center h-full max-h-screen-no-header gap-24px'>
                    {
                        awards?.map((a) =>
                            <ClaimBadgeCard {...a} />
                        )
                    }
                </div>

            </div>
        </div>
    )
}

export default UnclaimedModal
