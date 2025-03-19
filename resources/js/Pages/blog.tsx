import Img from '@/Components/Image'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import React from 'react'

interface Props { }

function Blog(props: Props) {
    const { } = props

    return (
        <AuthenticatedLayout>
            <div className='w-full'>
                <div className='grid w-full h-40vh'>
                    <Img className='object-cover w-full h-40vh col-start-1 row-start-1' src="/assets/img/blog-guy.png" />
                    <div className='w-full h-40vh bg-gradient-to-t from-black to-transparent col-start-1 row-start-1'></div>
                    <div className='w-full h-40vh col-start-1 row-start-1 flex flex-col justify-end pb-64px px-24px'>
                        <div>
                            <h1 className='text-left w-full mt-auto text-white max-w-[890px] text-4xl font-bold mx-auto'>Přinášíme vám zcela novou platformu, kterou si zamilujete! ❤️</h1>
                        </div>
                    </div>
                </div>
                <div className='max-w-[890px] py-64px mx-auto mob:px-24px'>
                    <div className='text-[#4D4D4D]'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Cras elementum. Proin mattis lacinia justo. Sed elit dui, pellentesque a, faucibus vel, interdum nec, diam. Fusce aliquam vestibulum ipsum. Aenean fermentum risus id tortor. Sed vel lectus. Donec odio tempus molestie, porttitor ut, iaculis quis, sem. Etiam ligula pede, sagittis quis, interdum ultricies, scelerisque eu. Integer rutrum, orci vestibulum ullamcorper ultricies, lacus quam ultricies odio, vitae placerat pede sem sit amet enim. Duis risus.</div>
                    <div className='font-bold text-3xl mt-24px'>Lorem ipsum dolor sit amet</div>
                    <div>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Cras elementum. Proin mattis lacinia justo. Sed elit dui, pellentesque a, faucibus vel, interdum nec, diam.</div>
                    <div className='flex w-full mt-32px gap-24px'>
                        <div className='w-full'>
                            <div className='font-bold text-2xl'>Lorem ipsum dolor sit amet</div>
                            <div className='text-[#4D4D4D]'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Cras elementum. Proin mattis lacinia justo. Sed elit dui, pellentesque a, faucibus vel, interdum nec, diam.</div>
                        </div>
                        <div className='w-full'>
                            <div className='font-bold text-2xl'>Lorem ipsum dolor sit amet</div>
                            <div className='text-[#4D4D4D]'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Cras elementum. Proin mattis lacinia justo. Sed elit dui, pellentesque a, faucibus vel, interdum nec, diam.</div>
                        </div>
                    </div>
                    <div className='mt-24px bg-[#F5F5F5] w-full h-50vh'></div>
                    <div className='mt-24px text-[#4D4D4D]'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Cras elementum. Proin mattis lacinia justo. Sed elit dui, pellentesque a, faucibus vel, interdum nec, diam. Fusce aliquam vestibulum ipsum. Aenean fermentum risus id tortor. Sed vel lectus. Donec odio tempus molestie, porttitor ut, iaculis quis, sem. Etiam ligula pede, sagittis quis, interdum ultricies, scelerisque eu. Integer rutrum, orci vestibulum ullamcorper ultricies, lacus quam ultricies odio, vitae placerat pede sem sit amet enim. Duis risus.</div>
                    <div className='flex w-full mt-32px gap-24px'>
                        <div className='w-full'>
                            <div className='font-bold text-2xl'>Lorem ipsum dolor sit amet</div>
                            <ul className='ml-16px'>
                                <li className='list-disc text-[#4D4D4D]'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Cras elementum. Proin mattis lacinia justo. Sed elit dui, pellentesque a, faucibus vel, interdum nec, diam.</li>
                                <li className='list-disc text-[#4D4D4D]'> Proin mattis lacinia justo. Sed elit dui, pellentesque a, faucibus vel, interdum nec, diam.</li>
                            </ul>
                            
                        </div>
                        <div className='w-full'>
                            <div className='font-bold text-2xl'>Lorem ipsum dolor sit amet</div>
                            <ol className='ml-16px'>
                                <li className='list-decimal text-[#4D4D4D]'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Cras elementum. Proin mattis lacinia justo. Sed elit dui, pellentesque a, faucibus vel, interdum nec, diam.</li>
                                <li className='list-decimal text-[#4D4D4D]'> Proin mattis lacinia justo. Sed elit dui, pellentesque a, faucibus vel, interdum nec, diam.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    )
}

export default Blog
