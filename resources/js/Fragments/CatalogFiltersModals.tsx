import { ModalsContext } from '@/Components/contexts/ModalsContext'
import { X } from '@phosphor-icons/react'
import React, { useContext } from 'react'

interface Props {
    priceRange: { from: number, to: number },
    priceTrend: string,
    reviews: string,
    favourited: string,
    status: string,
    releaseYear: string

    setPriceRange: React.Dispatch<React.SetStateAction<{ from: number, to: number }>>,
    setPriceTrend: React.Dispatch<React.SetStateAction<string>>,
    setRevies: React.Dispatch<React.SetStateAction<string>>,
    setFavourited: React.Dispatch<React.SetStateAction<string>>,
    setStatus: React.Dispatch<React.SetStateAction<string>>,
    setReleaseYear: React.Dispatch<React.SetStateAction<number>>
}

function CatalogFiltersModals(props: Props) {
    const { priceRange, priceTrend, releaseYear, reviews, favourited, status, setReleaseYear, setFavourited, setPriceRange, setPriceTrend, setRevies, setStatus } = props
    let { close } = useContext(ModalsContext)
    return (
        <div onClick={() => { close() }} className="bg-black bg-opacity-80 fixed top-0 left-0 w-full h-screen mob:flex items-center justify-center mob:items-start mob:max-h-full flex z-max p-24px mob:p-0">
            <div onClick={(e) => { e.stopPropagation(); }} className='bg-white border-2 border-black min-w-[480px] mob:min-w-0 mob:w-full mob:max-h-90vh overflow-y-auto grid p-48px'>
                <div className='flex w-full justify-between'>
                    <div className='font-bold'>Filtr</div>
                    <div className='flex items-center gap-16px'>
                        <div className='text-[#4D4D4D] font-nunito underline font-bold'>Resetovat</div>
                        <X size={24} className='cursor-pointer' onClick={() => { close() }} />
                    </div>
                </div>
            </div>
        </div>
    )
}

export default CatalogFiltersModals
