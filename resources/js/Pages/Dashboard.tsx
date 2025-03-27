import Img from '@/Components/Image';
import ProductCard from '@/Fragments/ProductCard';
import { Button } from '@/Fragments/UI/Button';
import useLazyLoad from '@/hooks/useLazyLoad';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { ArrowUpRight, Ranking, TelegramLogo } from '@phosphor-icons/react';
import { useState } from 'react';

function SmallBlogCard() {

    return (
        <Link href={route('blog-layout')} className='flex items-center gap-16px'>
            <Img src="/assets/img/small-placeholder.png" />
            <div className='font-bold'>Lorem ipsum dolor sit amet, consectetuer adipiscing…</div>
        </Link>
    )
}

function CardsDesktop() {
    return (
        <div className='flex border-b border-[#DEDFE5] px-24px mob:hidden'>
            <div className='w-full py-48px'>
                <div>Hodnota portfolia</div>
                <div className='flex items-center'>
                    <div className='font-bold text-7xl'>$</div>
                    <div className='font-bold text-9xl'>1 102</div>
                    <div className='text-[#999999] font-bold text-7xl'>.13</div>
                </div>
                <div className='bg-[#46BD0F] flex items-center  py-2px rounded w-[78px] text-center justify-center'>
                    <ArrowUpRight color="white" />
                    <div className='text-white'>+4,1 %</div>
                </div>
            </div>
            <div className='flex-shrink-0 py-48px px-48px'>
                <div>Hodnota portfolia</div>
                <div className='flex items-center py-34px'>
                    <div className='font-bold text-4xl'>$</div>
                    <div className='font-bold text-6xl'>1 102</div>
                    <div className='text-[#999999] font-bold text-4xl'>.13</div>
                </div>
                <div className='bg-[#46BD0F] flex items-center w-[78px] text-center py-2px rounded justify-center'>
                    <ArrowUpRight color="white" />
                    <div className='text-white'>+4,1 %</div>
                </div>
            </div>
            <div className='flex-shrink-0 py-48px px-48px'>
                <div>Hodnota portfolia</div>
                <div className='flex items-center py-34px'>
                    <div className='font-bold text-4xl'>$</div>
                    <div className='font-bold text-6xl'>1 102</div>
                    <div className='text-[#999999] font-bold text-4xl'>.13</div>
                </div>
                <div className='bg-[#46BD0F] flex items-center w-[78px] text-center py-2px rounded justify-center'>
                    <ArrowUpRight color="white" />
                    <div className='text-white'>+4,1 %</div>
                </div>
            </div>
            <div className='pl-48px py-48px'>
                <div className='bg-[#E9C784] flex flex-col justify-center items-center w-full p-52px'>
                    <div className='font-bold text-xl mb-12px'>Join signals community</div>
                    <Button icon={<TelegramLogo size={24} />} href={"#"}>Join on Telegram</Button>
                </div>
            </div>
        </div>
    )
}

function CardsMobile() {
    let [index, setIndex] = useState(0)

    return (
        <div className='nMob:hidden w-full px-24px py-16px'>
            <div className='w-full'>
                <div className='flex items-center w-full justify-between'>
                    <div>Hodnota portfolia</div>
                    <div className='flex items-center gap-8px'>
                        <div className='w-8px h-8px rounded-sm bg-black'></div>
                        <div className='w-8px h-8px rounded-sm bg-[#999999]'></div>
                        <div className='w-8px h-8px rounded-sm bg-[#999999]'></div>
                    </div>
                </div>
                <div className='flex items-center w-full justify-between'>
                    <div className='flex items-center'>
                        <div className='font-bold text-4xl'>$</div>
                        <div className='font-bold text-6xl'>1 102</div>
                        <div className='text-[#999999] font-bold text-4xl'>.13</div>
                    </div>
                    <div className='bg-[#46BD0F] flex items-center  py-2px rounded w-[78px] text-center justify-center'>
                        <ArrowUpRight color="white" />
                        <div className='text-white'>+4,1 %</div>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default function Dashboard() {

    const [products, button, meta, setItems] = useLazyLoad<Product>('products');
    // const [minifigs, button: _button, meta, setItems] = useLazyLoad<SetLego>('sets');
    return (
        <AuthenticatedLayout>
            <Head title="Dashboard" />
            <div className=''>

                <CardsDesktop />
                <CardsMobile />
                <div className='px-24px divide-x divide-[#DEDFE5] mob:divide-x-0 flex mob:flex-col'>
                    <div className='py-24px pr-24px mob:pr-0 w-full'>
                        <div className='flex gap-8px items-center'>
                            <Ranking size={24} />
                            <div className='font-bold text-xl'>Momentálně trendují</div>
                        </div>
                        <div className='grid grid-cols-2 mob:grid-cols-1 gap-24px mob:mt-12px'>
                            {
                                products?.map((s) =>
                                    <ProductCard {...s} />
                                )
                            }

                        </div>
                    </div>
                    <div className='py-24px pl-24px mob:pl-0 w-full'>
                        <div className='flex gap-8px items-center'>
                            <Ranking size={24} />
                            <div className='font-bold text-xl'>Top Movers</div>
                        </div>
                        <div className='grid grid-cols-2 mob:grid-cols-1 gap-24px mob:mt-12px'>
                            {
                                products?.map((s) =>
                                    <ProductCard {...s} />
                                )
                            }
                        </div>
                    </div>

                </div>
                <div className='flex items-center justify-center w-full'>
                    <div>
                        <Button href="#">Zobrazit další</Button>
                    </div>
                </div>
                <div className='p-24px border-t border-[#DEDFE5] mt-24px'>
                    <div className='font-bold text-xl'>Novinky a analýzy</div>
                    <div className='flex w-full gap-48px mt-12px mob:flex-col'>
                        <Link href={route('blog-layout')} className='w-full grid mob:hidden'>
                            <Img className='w-full' src="/assets/img/blog-placeholder.png" />
                            <div className='w-full'></div>
                        </Link>
                        <Link href={route('blog-layout')} className='nMob:hidden mt-12px p-24px border border-[#E6E6E6]'>
                            <div className='font-bold'>Přinášíme vám zcela novou platformu, kterou si zamilujete! ❤️</div>
                            <div>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non, nonummy ac, erat. In convallis.</div>
                        </Link>
                        <div className='w-full grid grid-cols-2 mob:grid-cols-1 gap-12px'>
                            <SmallBlogCard />
                            <SmallBlogCard />
                            <SmallBlogCard />
                            <SmallBlogCard />
                            <SmallBlogCard />
                            <SmallBlogCard />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
