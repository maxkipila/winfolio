import { ModalsContext } from '@/Components/contexts/ModalsContext';
import Img from '@/Components/Image';
import { _, t } from '@/Components/Translator';
import { MODALS } from '@/Fragments/Modals';
import ProductCard from '@/Fragments/ProductCard';
import { Button } from '@/Fragments/UI/Button';
import useLazyLoad from '@/hooks/useLazyLoad';
import usePageProps from '@/hooks/usePageProps';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowDownRight, ArrowUpRight, LegoSmiley, Ranking, TelegramLogo } from '@phosphor-icons/react';
import { useContext, useState } from 'react';
import { number } from '@/utils';

function splitParts(value: number): [string, string] {
    const formatted = String(number(value, 2, 'en-US'));
    const [intPart, decPart = ''] = formatted.split('.');
    return [intPart, decPart];
}

function SmallBlogCard() {

    return (
        <Link href={route('blog-layout')} className='flex items-center gap-16px'>
            <Img src="/assets/img/small-placeholder.png" />
            <div className='font-bold'>Lorem ipsum dolor sit amet, consectetuer adipiscing…</div>
        </Link>
    )
}

interface CardsProps {
    portfolioValue: number,
    portfolioStats?: {
        growth_percentage?: number
    }
}
function CardsDesktop(props: CardsProps) {
    const { portfolioValue, portfolioStats } = props
    const { auth } = usePageProps<{ auth: { user: User } }>();
    let wishlistValue = 0
    let wishListValues = auth?.user?.favourites?.flatMap((f) => f.favourite?.latest_price?.value)
    wishListValues.map((wV) => wishlistValue += parseFloat(wV))
    // console.log('wishlist values',auth?.user?.favourites?.flatMap((f) => f.favourite.latest_price.value))
    let { open } = useContext(ModalsContext)
    const [portfolioInt, portfolioDec] = splitParts(portfolioValue);
    const [wishInt, wishDec] = splitParts(wishlistValue);
    const [highInt, highDec] = splitParts(auth.user.highest_portfolio ?? 0);

    return (
        <div className='flex border-b border-[#DEDFE5] px-24px mob:hidden'>
            <div className='w-full py-48px'>
                <div className='font-nunito font-semibold'>{t('Hodnota portfolia')}</div>
                <div className='flex items-center'>
                    <div className='font-bold text-7xl'>$</div>
                    <div className='font-bold text-9xl'>{portfolioInt}</div>
                    {portfolioDec && <div className='text-[#999999] font-bold text-7xl'>.{portfolioDec}</div>}
                </div>
                {
                    portfolioValue > 0 &&
                    <div className={`${portfolioStats?.growth_percentage > 0 ? "bg-[#46BD0F]" : "bg-[#ED2E1B]"}  flex items-center  py-2px rounded w-[78px] text-center justify-center`}>
                        {
                            portfolioStats?.growth_percentage ?? 0 > 0 ?
                                <ArrowUpRight color="white" />
                                :
                                <ArrowDownRight color="white" />
                        }
                        <div className='text-white'>{Math.round(portfolioStats?.growth_percentage * 100) / 100} %</div>
                    </div>
                }
            </div>
            {
                wishlistValue > 0 &&
                <div className='flex-shrink-0 py-48px px-48px'>
                    <div className='font-nunito font-semibold'>{t('Hodnota wishlistu')}</div>
                    <div className='flex items-center py-34px'>
                        <div className='font-bold text-4xl'>$</div>
                        <div className='font-bold text-6xl'>{wishInt}</div>
                        {wishDec && <div className='text-[#999999] font-bold text-4xl'>.{wishDec}</div>}
                    </div>

                </div>
            }
            {
                auth?.user?.highest_portfolio > 0 &&
                <div className='flex-shrink-0 py-48px px-48px'>
                    <div className='font-nunito font-semibold'>{t("All time high portfolia")}</div>
                    <div className='flex items-center py-34px'>
                        <div className='font-bold text-4xl'>$</div>
                        <div className='font-bold text-6xl'>{highInt}</div>
                        {highDec && <div className='text-[#999999] font-bold text-4xl'>.{highDec}</div>}
                    </div>
                </div>
            }

            <div className='pl-48px py-48px'>
                <div className='bg-[#FFD266] flex flex-col justify-center items-center w-full p-52px'>
                    <div className='font-bold text-xl mb-12px'>{t("Join signals community")}</div>
                    <Button onClick={(e) => { e.preventDefault(); open(MODALS.GET_PREMIUM) }} icon={<TelegramLogo size={24} />} href={"#"}>{t('Join on Telegram')}</Button>
                </div>
            </div>
        </div>
    )
}

function CardsMobile(props: CardsProps) {
    let [index, setIndex] = useState(0)
    const { portfolioValue, portfolioStats } = props
    const { auth } = usePageProps<{ auth: { user: User } }>();
    let wishlistValue = 0
    let wishListValues = auth?.user?.favourites?.flatMap((f) => f.favourite?.latest_price?.value)
    wishListValues.map((wV) => wishlistValue += parseFloat(wV))
    let [scrollStart, setScrollStart] = useState(0)
    let [scrollEnd, setScrollEnd] = useState(0)
    try {
        let container = document?.getElementById('scrollable')
        let first = container.clientWidth / 2
        let second = container.clientWidth * 1.5

        container.addEventListener('scrollend', (e) => {
            setScrollEnd(container.scrollLeft)
            if (container.scrollLeft > first && container.scrollLeft < second) {
                container.scrollTo({ left: container.clientWidth - 24 })
            } else if (container.scrollLeft > second) {
                container.scrollTo({ left: (container.clientWidth - 24) * 2 })
            } else {
                container.scrollTo({ left: 0 })
            }
            // console.log('drag', e, container.scrollLeft, container.clientWidth)
        })

    } catch (error) {

    }


    const [pInt, pDec] = splitParts(portfolioValue);
    const [wInt, wDec] = splitParts(wishlistValue);
    const [hInt, hDec] = splitParts(auth.user.highest_portfolio ?? 0);

    return (
        <div id="scrollable" className='nMob:hidden w-full px-24px py-16px flex overflow-auto gap-24px'>
            <div className='w-full flex-shrink-0'>
                <div className='flex items-center w-full justify-between'>
                    <div className='font-nunito font-semibold'>{t('Hodnota portfolia')}</div>
                    <div className='flex items-center gap-8px'>
                        <div className='w-8px h-8px rounded-sm bg-black'></div>
                        <div className='w-8px h-8px rounded-sm bg-[#999999]'></div>
                        <div className='w-8px h-8px rounded-sm bg-[#999999]'></div>
                    </div>
                </div>
                <div className='flex items-center w-full justify-between'>
                    <div className='flex items-center'>
                        <div className='font-bold text-4xl'>$</div>
                        <div className='font-bold text-6xl'>{pInt}</div>
                        {pDec && <div className='text-[#999999] font-bold text-4xl'>.{pDec}</div>}
                    </div>
                    <div className={`${portfolioStats?.growth_percentage ?? 0 > 0 ? "bg-[#46BD0F]" : "bg-[#ED2E1B]"}  flex items-center  py-2px rounded w-[78px] text-center justify-center`}>
                        {
                            portfolioStats?.growth_percentage ?? 0 > 0 ?
                                <ArrowUpRight color="white" />
                                :
                                <ArrowDownRight color="white" />
                        }
                        <div className='text-white'>{Math.round(portfolioStats?.growth_percentage ?? 0 * 100) / 100} %</div>
                    </div>
                </div>
            </div>


            <div className='w-full flex-shrink-0'>
                <div className='flex items-center w-full justify-between'>
                    <div className='font-nunito font-semibold'>{t('Hodnota wishlistu')}</div>
                    <div className='flex items-center gap-8px'>
                        <div className='w-8px h-8px rounded-sm bg-[#999999]'></div>
                        <div className='w-8px h-8px rounded-sm bg-black'></div>
                        <div className='w-8px h-8px rounded-sm bg-[#999999]'></div>
                    </div>
                </div>
                <div className='flex items-center w-full justify-between'>
                    <div className='flex items-center'>
                        <div className='font-bold text-4xl'>$</div>
                        <div className='font-bold text-6xl'>{wInt}</div>
                        {wDec && <div className='text-[#999999] font-bold text-4xl'>.{wDec}</div>}
                    </div>
                </div>
            </div>


            <div className='w-full flex-shrink-0'>
                <div className='flex items-center w-full justify-between'>
                    <div className='font-nunito font-semibold'>{t("All time high portfolia")}</div>
                    <div className='flex items-center gap-8px'>
                        <div className='w-8px h-8px rounded-sm bg-[#999999]'></div>
                        <div className='w-8px h-8px rounded-sm bg-[#999999]'></div>
                        <div className='w-8px h-8px rounded-sm bg-black'></div>
                    </div>
                </div>
                <div className='flex items-center w-full justify-between'>
                    <div className='flex items-center'>
                        <div className='font-bold text-4xl'>$</div>
                        <div className='font-bold text-6xl'>{hInt}</div>
                        {hDec && <div className='text-[#999999] font-bold text-4xl'>.{hDec}</div>}
                    </div>
                </div>
            </div>
        </div>
    )
}

interface DashBoardProps {
    portfolioValue: number,
    portfolioStats?: {
        growth_percentage?: number
    }
}
export default function Dashboard(props: DashBoardProps) {

    // const [products, button, meta, setItems] = useLazyLoad<Product>('products');
    const { portfolioValue, portfolioStats } = props
    let [topMovers, button] = useLazyLoad<{ product: Product }>('topMovers');
    let [trendingProducts, trendButton, meta, setItems] = useLazyLoad<{ product: Product }>('trendingProducts');

    console.log('trendingProducts:', trendingProducts)
    let { open } = useContext(ModalsContext)
    return (
        <AuthenticatedLayout>
            <Head title="Dashboard | Winfolio" />
            <div className=''>

                <CardsDesktop {...props} />
                <CardsMobile {...props} />
                <div className='px-24px divide-x divide-[#DEDFE5] mob:divide-x-0 flex mob:flex-col'>
                    <div className='py-24px pr-24px mob:pr-0 w-full'>
                        <div className='flex gap-8px items-center'>
                            <Ranking size={24} />
                            <div className='font-bold text-xl'>{t('Momentálně trendují')}</div>
                        </div>
                        {
                            trendingProducts?.length > 0 ?
                                <>
                                    <div className='grid grid-cols-2 mob:grid-cols-1 gap-24px mt-12px'>
                                        {
                                            trendingProducts?.map((s) =>
                                                <ProductCard {...s.product} />
                                            )
                                        }

                                    </div>
                                    <div className='flex items-center justify-center w-full mt-24px'>
                                        <div>
                                            <Button wider {...trendButton}>{t('Zobrazit další')}</Button>
                                        </div>
                                    </div>
                                </>
                                :
                                <div className='w-full flex items-center justify-center bg-[#EEEFF2] mt-12px p-32px'>
                                    <div className=''>
                                        <div className='font-bold text-xl text-center'>{t('Zatím neexistují žádná data')}</div>
                                        {/* <div className='my-16px font-nunito text-[#4D4D4D] text-center'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non.</div> */}
                                        <Button className='max-w-150px mx-auto mt-24px' href={"#"} onClick={(e) => { e.preventDefault(); open(MODALS.PORTFOLIO, false, { create_portfolio: true }) }}>{t('Vytvořit portfolio')}</Button>
                                    </div>
                                </div>
                        }

                    </div>
                    <div className='py-24px pl-24px mob:pl-0 w-full'>
                        <div className='flex gap-8px items-center'>
                            <Ranking size={24} />
                            <div className='font-bold text-xl'>{t('Top Movers')}</div>
                        </div>
                        {
                            topMovers?.length > 0 ?
                                <>
                                    <div className='grid grid-cols-2 mob:grid-cols-1 gap-24px mt-12px'>
                                        {
                                            topMovers?.map((s) =>
                                                <ProductCard {...s.product} />
                                            )
                                        }
                                    </div>
                                    <div className='flex items-center justify-center w-full mt-24px'>
                                        <div>
                                            <Button wider {...button}>{t('Zobrazit další')}</Button>
                                        </div>
                                    </div>
                                </>
                                :
                                <div className='w-full flex items-center justify-center bg-[#EEEFF2] mt-12px p-32px'>
                                    <div className=''>
                                        <div className='font-bold text-xl text-center'>{t('Zatím neexistují žádná data')}</div>
                                        {/* <div className='my-16px font-nunito text-[#4D4D4D] text-center'>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Vestibulum erat nulla, ullamcorper nec, rutrum non.</div> */}
                                        <Button className='max-w-150px mx-auto mt-24px' href={"#"} onClick={(e) => { e.preventDefault(); open(MODALS.PORTFOLIO, false, { create_portfolio: true }) }}>{t('Vytvořit portfolio')}</Button>
                                    </div>
                                </div>
                        }

                    </div>

                </div>

                {/* <div className='p-24px border-t border-[#DEDFE5] mt-24px'>
                    <div className='font-bold text-xl'>{t('Novinky a analýzy')}</div>
                    <div className='flex w-full gap-48px mt-12px mob:flex-col'>
                        <Link href={route('blog-layout')} className='w-full grid mob:hidden'>
                            <Img className='w-full' src="/assets/img/blog-placeholder.png" />
                            <div className='w-full'></div>
                        </Link>
                        <Link href={route('blog-layout')} className='nMob:hidden mt-12px p-24px border border-[#E6E6E6]'>
                            <div className='font-bold'>{t('Přinášíme vám zcela novou platformu, kterou si zamilujete!')} ❤️</div>
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
                </div> */}
            </div>
        </AuthenticatedLayout>
    );
}
