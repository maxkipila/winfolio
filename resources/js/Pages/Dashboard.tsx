import ProductCard from '@/Fragments/ProductCard';
import { Button } from '@/Fragments/UI/Button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { ArrowUpRight, Ranking, TelegramLogo } from '@phosphor-icons/react';

export default function Dashboard() {
    return (
        <AuthenticatedLayout>
            <Head title="Dashboard" />
            <div className=''>
                <div className='flex border-b border-[#DEDFE5] px-24px'>
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

                <div className='px-24px divide-x divide-[#DEDFE5] flex'>
                    <div className='py-24px pr-24px w-full'>
                        <div className='flex gap-8px items-center'>
                            <Ranking size={24} />
                            <div className='font-bold text-xl'>Momentálně trendují</div>
                        </div>
                        <div className='grid grid-cols-2 gap-24px'>
                            <ProductCard />
                            <ProductCard />
                            <ProductCard />
                            <ProductCard />
                        </div>
                    </div>
                    <div className='py-24px pl-24px w-full'>
                        <div className='flex gap-8px items-center'>
                            <Ranking size={24} />
                            <div className='font-bold text-xl'>Top Movers</div>
                        </div>
                        <div className='grid grid-cols-2 gap-24px'>
                            <ProductCard />
                            <ProductCard />
                            <ProductCard />
                            <ProductCard />
                        </div>
                    </div>

                </div>
                <div className='flex items-center justify-center w-full'>
                    <div>
                        <Button href="#">Zobrazit další</Button>
                    </div>
                </div>
                <div className='p-24px border-t border-[#DEDFE5] mt-24px'></div>
            </div>
        </AuthenticatedLayout>
    );
}
