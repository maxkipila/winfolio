import Img from '@/Components/Image'
import { t } from '@/Components/Translator'
import PublicHeader from '@/Fragments/PublicHeader'
import { Button } from '@/Fragments/UI/Button'
import React from 'react'

interface Props {}

function Welcome(props: Props) {
    const {} = props

    return (
        <div className='pt-[85px]'>
            <PublicHeader />
            <div className='grid'>
                <Img className='w-full col-start-1 row-start-1 object-cover' src={'/assets/img/landing-bg.png'} />
                <div className='w-full col-start-1 row-start-1 p-24px flex items-center'>
                    <div>
                        <div>{t(('Stav si sbírku. Sleduj její růst.'))}</div>
                        <div>{t('Zlepšuj se a vyhrávej.')}</div>
                        <div>{t('Proměň svou sbírku v herní pole plné strategií, výzev a odměn. Sleduj tržní ceny, plň mise a staň se LEGO investičním šampionem.')}</div>
                        <Button href="">{t('Přijmout výzvu')}</Button>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default Welcome
