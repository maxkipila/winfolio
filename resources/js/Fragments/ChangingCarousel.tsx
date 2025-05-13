import Img from '@/Components/Image'
import { t } from '@/Components/Translator';
import React, { useEffect, useRef, useState } from 'react'



interface Props { }

function ChangingCarousel(props: Props) {
    const { } = props
    let slides = [
        { src: '/assets/img/brick-placeholder.png', headline: t('Vzácný Harry Potter'), text: 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non.' },
        { src: '/assets/img/heads-placeholder.jpg', headline: t('Vzácný Harry Potter'), text: 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non.' },
        { src: '/assets/img/orange-bricks-placeholder.jpg', headline: t('Vzácný Harry Potter'), text: 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non.' },

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
                <Img className='w-[90%] object-cover' src={slides[slide == 1 ? 2 : 2 - slide].src} />
                <div className='absolute top-0 left-0 w-full h-full  text-white p-40px'>
                    <div className='font-bold text-3xl text-center'>{slides[slide == 1 ? 2 : 2 - slide].headline}</div>
                    <div className='text-center font-nunito'>{slides[slide == 1 ? 2 : 2 - slide].text}</div>
                </div>

            </div>
            <div className='row-start-1 col-start-1  w-full h-full max-h-screen flex  justify-center relative'>
                <Img className='w-[95%] object-cover mb-24px' src={slides[slide == 2 ? 1 : 1 - slide].src} />
                <div className='absolute top-0 left-0 w-[95%] h-[90%]  text-white p-40px'>
                    <div className='font-bold text-3xl text-center'>{slides[slide == 2 ? 1 : 1 - slide].headline}</div>
                    <div className='text-center font-nunito'>{slides[slide == 2 ? 1 : 1 - slide].text}</div>
                </div>

            </div>
            <div className={`row-start-1 col-start-1  w-full h-full max-h-screen flex justify-center relative`}>
                <Img className='w-full object-cover mb-48px' src={slides[slide].src} />
                <div className='absolute top-0 left-0 w-full h-[90%]  text-white p-40px'>
                    <div className='font-bold text-3xl text-center'>{slides[slide].headline}</div>
                    <div className='text-center font-nunito'>{slides[slide].text}</div>
                </div>
                <div className='flex absolute bottom-[80px] w-full gap-8px justify-center'>
                    <div className={`w-8px h-8px ${slide == 0 ? "bg-white" : "bg-white bg-opacity-50"}  rounded-sm`}></div>
                    <div className={`w-8px h-8px ${slide == 1 ? "bg-white" : "bg-white bg-opacity-50"}  rounded-sm`}></div>
                    <div className={`w-8px h-8px ${slide == 2 ? "bg-white" : "bg-white bg-opacity-50"}  rounded-sm`}></div>
                </div>
            </div>

        </div>
    )
}

export default ChangingCarousel
