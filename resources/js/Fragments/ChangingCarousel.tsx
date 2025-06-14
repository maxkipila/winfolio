import Img from '@/Components/Image'
import { t } from '@/Components/Translator';
import React, { useEffect, useRef, useState } from 'react'



interface Props { }

function ChangingCarousel(props: Props) {
    const { } = props
    let slides = [
        { src: '/assets/img/architect.png', headline: t('Kostky tvého investičního impéria padají správně'), text: t('Sleduj hodnotu svých setů v reálném čase, analyzuj vývoj cen, získej predikce a tipy na nákup nebo prodej.') },
        { src: '/assets/img/harry-potter-welcome.png', headline: t('Hraj, plň mise a staň se LEGO šampionem'), text: t('Investování může být i zábava. Winfolio je nejen analytický nástroj, ale i herní platforma – s výzvami, misemi a odměnami, které tě provedou světem LEGO investic. Získej odznaky, postupuj úrovněmi a buduj si reputaci investora, kterého bude komunita sledovat.') },
        { src: '/assets/img/friends.png', headline: t('Komunita, která staví na stejných základech'), text: t('Správné investice nejsou náhoda. Winfolio ti nabízí přehled o hodnotě LEGO setů v reálném čase, sleduje jejich cenový vývoj a poskytuje predikce založené na datech z desítek ověřených zdrojů. Díky chytrým grafům a cenovým alertům budeš vždy vědět, kdy nakoupit a kdy prodat.') },

    ]

    let interval;
    const [slide, setSlide] = useState(0);
    const [elapsed, setElapsed] = useState(0);
    const [paused, setPaused] = useState(false);

    useEffect(() => {

        if (!paused)
            interval = setInterval(() => {
                setElapsed(e => Math.min(e + 1, 10));
            }, 500);

        return () => clearInterval(interval);

    }, [paused]);

    useEffect(() => {
        if (elapsed >= 9) {
            setSlide(s => {
                setElapsed(0);
                return ((s + 1) % slides.length)
            });

        }
    }, [elapsed])
    // console.log(slide, slides?.length - 1)
    return (
        <div className='grid h-full'>
            <div className='row-start-1 col-start-1 w-full h-full flex max-h-screen justify-center relative'>
                <Img className='w-full object-cover' src={slides[slide == 1 ? 2 : 2 - slide].src} />
                
                <div className='absolute top-0 left-0 w-full h-full  text-white p-40px'>
                    <div className='font-bold text-3xl text-center'>{slides[slide == 1 ? 2 : 2 - slide].headline}</div>
                    <div className='text-center font-nunito'>{slides[slide == 1 ? 2 : 2 - slide].text}</div>
                </div>

            </div>
            <div className='row-start-1 col-start-1  w-full h-full max-h-screen flex  justify-center relative'>
                <Img className='w-full object-cover mb-24px' src={slides[slide == 2 ? 1 : 1 - slide].src} />
                <div className='absolute top-0 left-0 w-full h-full  text-white p-40px'>
                    <div className='font-bold text-3xl text-center'>{slides[slide == 2 ? 1 : 1 - slide].headline}</div>
                    <div className='text-center font-nunito'>{slides[slide == 2 ? 1 : 1 - slide].text}</div>
                </div>

            </div>
            <div className={`row-start-1 col-start-1  w-full h-full max-h-screen flex justify-center relative overflow-hidden `}>
                <div className='w-full h-full grid overflow-hidden'>
                    
                    <Img className='w-full h-full object-cover col-start-1 row-start-1' src={slides[slide].src} />
                    <div className='bg-black bg-opacity-30 col-start-1 row-start-1 w-full h-full'></div>
                </div>
                
                <div className='absolute top-0 left-0 w-full h-full text-white p-40px'>
                    <div className='font-bold text-3xl text-center'>{slides[slide].headline}</div>
                    <div className='text-center font-nunito'>{slides[slide].text}</div>
                </div>
                <div className='flex absolute bottom-[100px] w-full gap-8px justify-center'>
                    <div className={`w-8px h-8px ${slide == 0 ? "bg-white" : "bg-white bg-opacity-50"}  rounded-sm`}></div>
                    <div className={`w-8px h-8px ${slide == 1 ? "bg-white" : "bg-white bg-opacity-50"}  rounded-sm`}></div>
                    <div className={`w-8px h-8px ${slide == 2 ? "bg-white" : "bg-white bg-opacity-50"}  rounded-sm`}></div>
                </div>
            </div>

        </div>
    )
}

export default ChangingCarousel
