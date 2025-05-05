import Img from '@/Components/Image'
import { t } from '@/Components/Translator'
import ProductCard from '@/Fragments/ProductCard'
import PublicHeader from '@/Fragments/PublicHeader'
import { Button } from '@/Fragments/UI/Button'
import useLazyLoad from '@/hooks/useLazyLoad'
import { Link } from '@inertiajs/react'
import { Check, Ranking } from '@phosphor-icons/react'
import React from 'react'

interface Props {
    products: Array<Product>
}

function Welcome(props: Props) {
    const { products } = props

    // let [top_movers, button] = useLazyLoad<{ product: Product }>('top_movers');
    // let [trending_products, trendButton, meta, setItems] = useLazyLoad<{ product: Product }>('trending_products');

    return (
        <div className='pt-[82px] font-teko relative'>
            <PublicHeader />
            <div className='w-full'>
                <div className='grid'>
                    <Img className='w-full col-start-1 row-start-1 object-cover h-full' src={'/assets/img/lading-bg.png'} />
                    <div className='w-full col-start-1 row-start-1 p-24px flex items-center'>
                        <div className='text-white z-max'>
                            <div className='font-bold text-6xl'>{t(('Stav si sbírku. Sleduj její růst.'))}</div>
                            <div className='font-bold text-6xl'>{t('Zlepšuj se a vyhrávej.')}</div>
                            <div className='font-nunito mb-32px'>{t('Proměň svou sbírku v herní pole plné strategií, výzev a odměn. Sleduj tržní ceny, plň mise a staň se LEGO investičním šampionem.')}</div>
                            <Button className='max-w-[140px]' href={route('login')}>{t('Přijmout výzvu')}</Button>
                        </div>
                    </div>
                    <div className='w-full col-start-1 row-start-1 flex flex-col justify-end px-24px mob:hidden'>
                        <div className='grid grid-cols-4 mob:flex overflow-x-auto gap-17px transform translate-y-[60%]'>
                            {
                                products?.map((p) =>
                                    <ProductCard {...p} />
                                )
                            }
                        </div>
                    </div>

                </div>
            </div>
            <div className='w-full col-start-1 row-start-1 flex flex-col justify-end px-24px nMob:hidden mob:py-24px'>
                <div className='grid grid-cols-4 mob:grid-cols-1 overflow-x-auto gap-17px'>
                    {
                        products?.map((p) =>
                            <ProductCard {...p} />
                        )
                    }
                </div>
            </div>
            <div className='pt-[220px] mob:pt-48px px-24px pb-48px border-b border-[#DEDFE5]'>
                <div className='font-bold text-4xl text-center'>{t('Kostky tvého investičního impéria padají správně')}</div>
                <div className='font-nunito text-lg text-center'>{t('Sleduj hodnotu svých setů v reálném čase, analyzuj vývoj cen, získej predikce a tipy na nákup nebo prodej.')}</div>

                <div className='mt-48px flex items-center gap-64px mob:flex-col'>
                    <Img className='w-full' src="/assets/img/landing-heads.png" />
                    <div className='w-full'>
                        <div className='font-bold text-3xl'>{t('Rozhoduj se na základě dat, ne pocitů')}</div>
                        <div className='font-nunito my-32px'>{t('Správné investice nejsou náhoda. Winfolio ti nabízí přehled o hodnotě LEGO setů v reálném čase, sleduje jejich cenový vývoj a poskytuje predikce založené na datech z desítek ověřených zdrojů. Díky chytrým grafům a cenovým alertům budeš vždy vědět, kdy nakoupit a kdy prodat.')}</div>
                        <Button className='max-w-[140px]' href={route('login')}>{t('Vytvořit účet')}</Button>
                    </div>
                </div>

                <div className='mt-48px flex items-center gap-64px mob:flex-col-reverse'>
                    <div className='w-full'>
                        <div className='font-bold text-3xl'>{t('Hraj, plň mise a staň se LEGO šampionem')}</div>
                        <div className='font-nunito my-32px'>{t('Investování může být i zábava. Winfolio je nejen analytický nástroj, ale i herní platforma – s výzvami, misemi a odměnami, které tě provedou světem LEGO investic. Získej odznaky, postupuj úrovněmi a buduj si reputaci investora, kterého bude komunita sledovat.')}</div>
                        <Button className='max-w-[140px]' href={route('login')}>{t('Vytvořit účet')}</Button>
                    </div>
                    <Img className='w-full' src="/assets/img/lone-landing.png" />
                </div>

                <div className='mt-48px flex items-center gap-64px mob:flex-col'>
                    <Img className='w-full' src="/assets/img/beatles-landing.png" />
                    <div className='w-full'>
                        <div className='font-bold text-3xl'>{t('Komunita, která staví na stejných základech')}</div>
                        <div className='font-nunito my-32px'>{t('Správné investice nejsou náhoda. Winfolio ti nabízí přehled o hodnotě LEGO setů v reálném čase, sleduje jejich cenový vývoj a poskytuje predikce založené na datech z desítek ověřených zdrojů. Díky chytrým grafům a cenovým alertům budeš vždy vědět, kdy nakoupit a kdy prodat.')}</div>
                        <Button className='max-w-[140px]' href={route('login')}>{t('Vytvořit účet')}</Button>
                    </div>
                </div>


            </div>
            <div className='px-24px divide-x divide-[#DEDFE5] mob:divide-x-0 flex mob:flex-col'>
                <div className='py-24px pr-24px mob:pr-0 w-full'>
                    <div className='flex gap-8px items-center'>
                        <Ranking size={24} />
                        <div className='font-bold text-4xl'>{t('Momentálně trendují')}</div>
                    </div>
                    <div className='grid grid-cols-2 mob:grid-cols-1 gap-24px mob:mt-12px'>
                        {
                            products?.map((s) =>
                                <ProductCard {...s} />
                            )
                        }

                    </div>
                    {/* <div className='flex items-center justify-center w-full mt-24px'>
                        <div>
                            <Button {...trendButton}>{t('Zobrazit další')}</Button>
                        </div>
                    </div> */}
                </div>
                <div className='py-24px pl-24px mob:pl-0 w-full'>
                    <div className='flex gap-8px items-center'>
                        <Ranking size={24} />
                        <div className='font-bold text-4xl'>{t('Top Movers')}</div>
                    </div>
                    <div className='grid grid-cols-2 mob:grid-cols-1 gap-24px mob:mt-12px'>
                        {
                            products?.map((s) =>
                                <ProductCard {...s} />
                            )
                        }
                    </div>
                    {/* <div className='flex items-center justify-center w-full mt-24px'>
                        <div>
                            <Button {...button}>{t('Zobrazit další')}</Button>
                        </div>
                    </div> */}
                </div>

            </div>
            <div id="howItWorks" className='mt-64px grid h-80vh mob:h-auto'>
                <Img className='col-start-1 row-start-1 w-full  h-80vh mob:h-full object-cover ' src="/assets/img/storm-landing.jpg" />
                <div className='col-start-1 row-start-1 w-full  h-80vh mob:h-auto px-24px flex items-center  mob:py-24px'>
                    <div className='bg-white p-24px max-w-1/2 mob:max-w-max'>
                        <div className='text-4xl font-bold'>{t('Jak to funguje?')}</div>
                        <div className='my-32px font-nunito text-lg'>{t('Sleduj hodnotu své LEGO sbírky, dělej chytřejší nákupy a bav se při plnění investičních misí. Jednoduše a na jednom místě.')}</div>
                        <div>
                            <div className='flex mb-18px  gap-24px'>
                                <div className='flex items-center justify-center bg-[#F5F5F5] w-48px h-48px font-nunito text-lg flex-shrink-0'>1.</div>
                                <div>
                                    <div className='font-nunito text-lg font-bold'>{t('Vytvoř si svou vlastní investiční truhlu')}</div>
                                    <div className='font-nunito text-lg'>{t('Přidej své LEGO sety během pár minut a sleduj, jaká je jejich aktuální hodnota. Winfolio čerpá data z celosvětových trhů a automaticky ti ukáže, jak si tvoje sbírka vede.')}</div>
                                </div>

                            </div>
                            <div className='flex  gap-24px'>
                                <div className='flex items-center justify-center bg-[#F5F5F5] w-48px h-48px font-nunito text-lg flex-shrink-0'>2.</div>
                                <div>
                                    <div className='font-nunito text-lg font-bold'>{t('Dělej chytřejší nákupní rozhodnutí')}</div>
                                    <div className='font-nunito text-lg'>{t('Díky analýze tisíců cen a trendů ti Winfolio napoví, které sety mají investiční potenciál. Nakupuj ve správný čas a buduj sbírku, která získává na hodnotě.')}</div>
                                </div>

                            </div>
                            <div className='flex  gap-24px'>
                                <div className='flex items-center justify-center bg-[#F5F5F5] w-48px h-48px font-nunito text-lg flex-shrink-0'>3.</div>
                                <div>
                                    <div className='font-nunito text-lg font-bold'>{t('Buduj své sběratelské impérium a bav se u toho')}</div>
                                    <div className='font-nunito text-lg'>{t('Plň investiční mise, sbírej odměny a seznam se s dalšími sběrateli. Winfolio tě provede světem LEGO investic zábavným a hravým způsobem.')}</div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div className='bg-[#E9C784] pt-64px pb-[264px] mob:px-24px'>
                <div id="pricing" className='font-bold text-4xl text-center'>{t('Vyber si, jak chceš stavět své portfolio')}</div>
                <div className='font-nunito text-center'>{t('Začni zdarma s Free balíčkem, nebo odemkni pokročilé funkce s Premium verzí.')}</div>
                <div className='flex items-center gap-24px justify-center mt-32px max-w-3/4 mx-auto mob:flex-col mob:max-w-max'>
                    <div className='border-2 border-black bg-white w-full'>
                        <div className='p-24px'>
                            <div className='font-bold text-2xl'>{t('Zdarma navždy')}</div>
                            <div className='mt-4px font-nunito'>{t('Pro začínající sběratele')}</div>
                            <div className='font-bold text-lg'>{t('Zdarma')}</div>
                        </div>
                        <div className='border-t-2 border-black p-24px'>
                            <div className='flex gap-24px items-center mb-16px'>
                                <Check size={24} />
                                <div className='font-nunito'>{t('Portfolio maximálně 5 setů')}</div>
                            </div>
                            <div className='flex gap-24px items-center mb-16px'>
                                <Check size={24} />
                                <div className='font-nunito'>{t('Přístup k základním datům o trzích')}</div>
                            </div>
                            <div className='flex gap-24px items-center mb-[72px]'>
                                <Check size={24} />
                                <div className='font-nunito'>{t('Mise a úkoly')}</div>
                            </div>
                            <Button className='max-w-[160px]' href={route('login')}>{t('Začít zdarma')}</Button>
                        </div>
                    </div>
                    <div className='border-2 border-black bg-white h-full w-full'>
                        <div className='p-24px'>
                            <div className='font-bold text-2xl'>{t('Premium')}</div>
                            <div className='mt-4px font-nunito'>{t('Pro pokročilé sběratele')}</div>
                            <div className='font-bold text-lg'>{t('249 Kč / měs')}</div>
                        </div>
                        <div className='border-t-2 border-black p-24px'>
                            <div className='flex gap-24px items-center mb-16px'>
                                <Check size={24} />
                                <div className='font-nunito'>{t('Neomezeně setů v portfoliu')}</div>
                            </div>
                            <div className='flex gap-24px items-center mb-16px'>
                                <Check size={24} />
                                <div className='font-nunito'>{t('Přístup k pokročilým statistikám')}</div>
                            </div>
                            <div className='flex gap-24px items-center mb-16px'>
                                <Check size={24} />
                                <div className='font-nunito'>{t('Mise a úkoly')}</div>
                            </div>
                            <div className='flex gap-24px items-center mb-32px'>
                                <Check size={24} />
                                <div className='font-nunito'>{t('Přístup do uzavřené komunity')}</div>
                            </div>
                            <Button className='max-w-[160px]' href={route('login')}>{t('Začít naplno')}</Button>
                        </div>
                    </div>
                </div>

            </div>
            <div className='bg-white w-full h-[272px] max-h-[272px] relative'>
                <div className='transform -translate-y-1/2 px-24px'>
                    <div className='w-full bg-black grid '>
                        {/* <Img className='w-full col-start-1 row-start-1' src="/assets/img/john-landing.png" /> */}
                        <div className='bg-black px-24px h-[400px] items-center flex gap-[58px] text-white mob:flex-col mob:justify-center'>
                            <div className='w-full'>
                                <Img src="/assets/img/logo-white.png" />
                                <div className='font-nunito text-lg my-24px'>{t('Stav si sbírku. Sleduj její růst. Zlepšuj se a vyhrávej.')}</div>
                                <a className='font-nunito font-bold text-lg' href="mailto:info@winfolio.cz">info@winfolio.cz</a>
                            </div>
                            <div className='w-full text-white flex flex-col gap-8px'>
                                <Link className='font-nunito ' href="#howItWorks">{t('Jak to funguje')}</Link>
                                <Link className='font-nunito ' href="#pricing">{t('Pricing')}</Link>
                                <Link className='font-nunito font-bold' href={route('login')}>{t('Sign Up / Login')}</Link>
                            </div>
                        </div>

                    </div>
                </div>
                <div className='font-nunito text-lg px-24px absolute left-0 mob:bottom-12px bottom-24px'>{t('© 2025, winfolio. Všechna práva vyhrazena.')}</div>
            </div>
        </div>
    )
}

export default Welcome
