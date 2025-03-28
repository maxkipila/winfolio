import { ModalsContext } from '@/Components/contexts/ModalsContext'
import { Check, X } from '@phosphor-icons/react'
import React, { useContext } from 'react'
import { Button } from './UI/Button'

interface Props {}

function ReviewPostedModal(props: Props) {
    const {} = props

    let { close } = useContext(ModalsContext)
    return (
        <div onClick={() => { close() }} className="bg-black bg-opacity-80 fixed top-0 left-0 w-full h-screen items-center justify-center mob:block mob:max-h-full flex z-max p-24px mob:pb-0">
            <div onClick={(e) => { e.stopPropagation(); }} className='bg-white border-2 border-black min-w-[480px] max-w-[480px] mob:w-full mob:max-h-90vh overflow-y-auto'>
                <div className='flex items-end justify-end'>
                    <div onClick={() => { close() }} className='w-40px h-40px bg-black flex items-center justify-center'>
                        <X color='white' size={24} />
                    </div>
                </div>
                <div className='p-48px'>
                    <div className='w-[88px] h-[88px] bg-[#46BD0F] border border-black rounded-full flex items-center justify-center mx-auto'>
                        <Check weight='bold' size={48} color="white" />
                    </div>
                    <div className='mt-16px font-bold font-teko text-center'>Review Posted</div>
                    <div className='font-nunito text-[#4D4D4D] my-16px text-center'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non.</div>
                    <Button href={"#"} icon={<Check size={24} />}>Dokončit</Button>
                </div>

            </div>
        </div>
    )
}

export default ReviewPostedModal
