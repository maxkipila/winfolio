import Img from '@/Components/Image'
import { t } from '@/Components/Translator'
import { Link } from '@inertiajs/react'
import React, { useEffect, useRef, useState } from 'react'
import { Button } from './UI/Button'
import { Globe, List, UserCircle, X } from '@phosphor-icons/react'



function LangPicker() {
    let [picker, setPicker] = useState(false)
    const [open, setopen] = useState(false);
    const ref = useRef<HTMLDivElement>(null);
    const height = useRef("0px");

    const collapseSection = () => {
        let element = ref.current;
        if (element && height?.current != '0px') {
            var sectionHeight = element.scrollHeight;
            var elementTransition = element.style.transition;
            element.style.transition = '';

            requestAnimationFrame(function () {
                if (element) {
                    height.current = sectionHeight + 'px';
                    element.style.height = height.current;
                    element.style.transition = elementTransition;
                }
                requestAnimationFrame(function () {
                    if (element) {
                        height.current = 0 + 'px';
                        element.style.height = height.current;
                    }
                });
            });

            setopen(false);
        }
    };

    const expandSection = () => {
        let element = ref.current;
        if (element) {
            var sectionHeight = element.scrollHeight;
            height.current = sectionHeight + 'px';
            element.style.height = height.current;
            setopen(true);
        }
    };

    const toggle = () => {
        if (open)
            collapseSection()
        else
            expandSection()
    }

    const closeOnClick = () => {

        collapseSection()
    }

    const checkOnClick = (event) => {
        event.stopPropagation();
    }

    useEffect(() => {

        try {
            window?.addEventListener('click', closeOnClick)
        } catch (error) { }

        return () => {
            try {
                window?.removeEventListener('click', closeOnClick)
            } catch (error) { }
        }
    }, [])

    return (
        <div onClick={checkOnClick}>
            <Globe onClick={toggle} className={`flex-shrink-0 cursor-pointer`} size={24} />
            {
                <div ref={ref} className='absolute collapsable -bottom-10px bg-white transform translate-y-full w-[100px] overflow-hidden mob:-translate-x-1/2' style={{ height: height.current }}>
                    <Link className='flex items-center gap-12px p-8px border-2 border-black' href={route('welcome', { locale: 'cs' })}>
                        <Img src="/assets/img/cz.svg" />
                        <div className='font-nunito font-bold'>CZ</div>
                    </Link>
                    <Link className='flex items-center gap-12px p-8px border-r-2 border-b-2 border-l-2 border-black' href={route('welcome', { locale: 'en' })}>
                        <Img src="/assets/img/gb.svg" />
                        <div className='font-nunito font-bold'>EN</div>
                    </Link>
                </div>
            }
        </div>
    )
}

interface Props { }

function PublicHeader(props: Props) {
    const { } = props
    let [picker, setPicker] = useState(false)
    let [mobile, setMobile] = useState(false)
    return (
        <div className='py-16px flex justify-between items-center px-24px border-b-2 bg-white border-black font-teko fixed top-0 w-full z-max mob:min-h-[82px]'>
            <Img src="/assets/img/logo.svg" />
            <div className='flex gap-24px items-center mob:flex-row-reverse relative'>
                <Link className='font-nunito font-bold mob:hidden' href="#howItWorks">{t('Jak to funguje')}</Link>
                {/* <Link className='font-nunito font-bold' href="">{t('Knihovna')}</Link> */}
                <Link className='font-nunito font-bold mob:hidden' href="#pricing">{t('Pricing')}</Link>
                {/* <Link className='font-nunito font-bold' href="">{t('Blog')}</Link> */}
                {/* <Link className='font-nunito font-bold' href="">{t('How It Works')}</Link> */}
                <div className='relative mob:flex gap-16px items-center'>
                    <LangPicker />
                    {/* <Globe onClick={() => { setPicker((p) => !p) }} className={`flex-shrink-0 cursor-pointer ${mobile && "hidden"}`} size={24} />
                    {
                        picker &&
                        <div className='absolute bottom-0 bg-white transform translate-y-full w-64px mob:-translate-x-1/2'>
                            <Link className='flex items-center gap-12px p-8px' href={route('welcome', { locale: 'cs' })}>
                                <Img src="/assets/img/cz.svg" />
                                <div>CZ</div>
                            </Link>
                            <Link className='flex items-center gap-12px p-8px' href={route('welcome', { locale: 'en' })}>
                                <Img src="/assets/img/gb.svg" />
                                <div>EN</div>
                            </Link>
                        </div>
                    } */}
                    {
                        mobile ?
                            <X className='nMob:hidden' onClick={() => { setMobile((p) => !p) }} size={24} />
                            :
                            <List className='nMob:hidden' onClick={() => { setMobile((p) => !p) }} size={24} />
                    }
                </div>
                <Button className='max-w-[160px] mob:hidden' href={route('login')} icon={<UserCircle className='flex-shrink-0' size={24} />}>{t('Vytvořit účet')}</Button>
                <Link className='font-bold text-lg mob:hidden' href={route('login')}>{t('Přihlásit se')}</Link>

            </div>
            <div className={`absolute top-[80px] left-0 w-full h-screen-no-header p-24px z-max flex flex-col justify-between bg-white transform duration-300 nMob:hidden  ${mobile ? "" : "translate-x-full"}`}>
                <div></div>
                <div className='flex flex-col gap-24px'>
                    <Link onClick={() => { setMobile(false) }} className='font-nunito font-bold text-2xl' href="#howItWorks">{t('Jak to funguje')}</Link>
                    <Link onClick={() => { setMobile(false) }} className='font-nunito font-bold text-2xl' href="#pricing">{t('Pricing')}</Link>
                </div>
                <div className='flex items-center justify-between'>

                    <Button className='w-full' href={route('login')} icon={<UserCircle className='flex-shrink-0' size={24} />}>{t('Vytvořit účet')}</Button>
                    <Link className='font-bold text-lg w-full text-center' href={route('login')}>{t('Přihlásit se')}</Link>
                </div>
            </div>
        </div>
    )
}

export default PublicHeader
