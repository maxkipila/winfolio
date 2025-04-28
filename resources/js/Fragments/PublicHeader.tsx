import Img from '@/Components/Image'
import { t } from '@/Components/Translator'
import { Link } from '@inertiajs/react'
import React from 'react'
import { Button } from './UI/Button'
import { Globe, UserCircle } from '@phosphor-icons/react'

interface Props { }

function PublicHeader(props: Props) {
    const { } = props

    return (
        <div className='py-16px flex justify-between items-center px-24px border-b-2 bg-white border-black font-teko fixed top-0 w-full z-max'>
            <Img src="/assets/img/logo.png" />
            <div className='flex gap-24px items-center'>
                <Link className='font-nunito font-bold' href="">{t('Jak to funguje')}</Link>
                {/* <Link className='font-nunito font-bold' href="">{t('Knihovna')}</Link> */}
                <Link className='font-nunito font-bold' href="">{t('Pricing')}</Link>
                {/* <Link className='font-nunito font-bold' href="">{t('Blog')}</Link> */}
                {/* <Link className='font-nunito font-bold' href="">{t('How It Works')}</Link> */}
                <Globe size={24} />
                <Button className='max-w-[160px] ' href={route('login')} icon={<UserCircle className='flex-shrink-0' size={24} />}>{t('Vytvořit účet')}</Button>
                <Link className='font-bold text-lg ' href={route('login')}>Log in</Link>
            </div>
        </div>
    )
}

export default PublicHeader
