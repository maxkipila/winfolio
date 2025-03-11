import Img from '@/Components/Image'
import React, { useEffect, useRef, useState } from 'react'

interface Props { }

function ChangingCarousel(props: Props) {
    const { } = props
    let slides = [
        { src: 'assets/img/brick-placeholder.png', headline: 'Vzácný Harry Potter', text: 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non.' },
        { src: 'assets/img/heads-placeholder.jpg', headline: 'Vzácný Harry Potter', text: 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non.' },
        { src: 'assets/img/orange-bricks-placeholder.jpg', headline: 'Vzácný Harry Potter', text: 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non.' },
    ]
    let index = useRef(0)
    useEffect(() => {

        let timer = setInterval(() => {
            if (index.current + 1 > slides.length - 1) {
                index.current = 0
            } {
                index.current = index.current + 1
            }

        }, 1200)

        return () => {
            clearInterval(timer)
        }
    }, [])

    return (
        <div className='grid h-full'>
            <div className='row-start-1 col-start-1 w-full h-full flex  justify-center relative'>
                <Img className='w-[90%] object-cover' src={slides[0].src} />
                <div className='absolute top-0 left-0 w-full h-full  text-white p-40px'>
                    <div className='font-bold text-3xl text-center'>{slides[0].headline}</div>
                    <div className='text-center'>{slides[0].text}</div>
                </div>
            </div>
            <div className='row-start-1 col-start-1  w-full h-full flex  justify-center relative'>
                <Img className='w-[95%] object-cover mb-24px' src={slides[1].src} />
                <div className='absolute top-0 left-0 w-[95%] h-[90%]  text-white p-40px'>
                    <div className='font-bold text-3xl text-center'>{slides[1].headline}</div>
                    <div className='text-center'>{slides[1].text}</div>
                </div>
            </div>
            <div className='row-start-1 col-start-1  w-full h-full flex justify-center relative '>
                <Img className='w-full object-cover mb-48px' src={slides[2].src} />
                <div className='absolute top-0 left-0 w-full h-[90%]  text-white p-40px'>
                    <div className='font-bold text-3xl text-center'>{slides[2].headline}</div>
                    <div className='text-center'>{slides[2].text}</div>
                </div>
            </div>

        </div>
    )
}

export default ChangingCarousel
