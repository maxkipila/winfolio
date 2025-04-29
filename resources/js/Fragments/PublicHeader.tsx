import Img from '@/Components/Image'
import { t } from '@/Components/Translator'
import { Link } from '@inertiajs/react'
import React, { useState } from 'react'
import { Button } from './UI/Button'
import { Globe, UserCircle } from '@phosphor-icons/react'

interface Props { }

function PublicHeader(props: Props) {
    const { } = props
    let [picker, setPicker] = useState(false)
    return (
        <div className='py-16px flex justify-between items-center px-24px border-b-2 bg-white border-black font-teko fixed top-0 w-full z-max mob:min-h-[82px]'>
            <Img src="/assets/img/logo.png" />
            <div className='flex gap-24px items-center mob:flex-row-reverse'>
                <Link className='font-nunito font-bold mob:hidden' href="#howItWorks">{t('Jak to funguje')}</Link>
                {/* <Link className='font-nunito font-bold' href="">{t('Knihovna')}</Link> */}
                <Link className='font-nunito font-bold mob:hidden' href="#pricing">{t('Pricing')}</Link>
                {/* <Link className='font-nunito font-bold' href="">{t('Blog')}</Link> */}
                {/* <Link className='font-nunito font-bold' href="">{t('How It Works')}</Link> */}
                <div className='relative'>
                    <Globe onClick={() => { setPicker((p) => !p) }} className='flex-shrink-0 cursor-pointer' size={24} />
                    {
                        picker &&
                        <div className='absolute bottom-0 bg-white transform translate-y-full w-64px'>
                            <Link className='flex items-center gap-12px p-8px' href={route('welcome', { locale: 'cs' })}>
                                <Img src="/assets/img/cz.svg" />
                                <div>CZ</div>
                            </Link>
                            <Link className='flex items-center gap-12px p-8px' href={route('welcome', { locale: 'en' })}>
                                <Img src="/assets/img/gb.svg" />
                                <div>EN</div>
                            </Link>
                        </div>
                    }
                </div>
                <Button className='max-w-[160px] mob:hidden' href={route('login')} icon={<UserCircle className='flex-shrink-0' size={24} />}>{t('Vytvořit účet')}</Button>
                <Link className='font-bold text-lg mob:hidden' href={route('login')}>{t('Přihlásit se')}</Link>
            </div>
        </div>
    )
}

export default PublicHeader
