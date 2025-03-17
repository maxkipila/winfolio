import Icon from "@/Components/Icon";
import Img from "@/Components/Image";
import usePageProps from "@/hooks/usePageProps";
import { Link } from "@inertiajs/react";
import { ChartPie, Users, Medal, Cards, Lego, LegoSmiley } from "@phosphor-icons/react";
import { ReactNode, useState } from "react";


interface MenuLinkProps {
    href: string,
    name: string,
    icon: ReactNode,
    activeName: string
    auth?: User
}

function MenuLink(props: MenuLinkProps) {
    const { href, name, icon, activeName } = props


    return (
        <Link title={name} href={href} className={`p-12px max-h-[48px] ${route()?.current()?.startsWith(activeName) ? 'bg-[#F7AA1A] text-black rounded-sm border-2 border-black' : "text-[#667A7B]"}`}>
            {icon}
        </Link>
    )
}

interface Props {
    auth?: User
}

function Sidebar(props: Props) {
    const { auth } = props;
    let [userOptions, setUserOptions] = useState(false);

    return (
        <div className='bg-white w-[80px] border-r-[1px] border-[#DEDFE5] h-screen flex flex-col items-center p-16px justify-between sticky top-16px'>
            <Link href={route('dashboard')}>
                <Icon name='Logo' />
            </Link>

            <div className='flex flex-col text-bold gap-12px'>
                <MenuLink activeName='admin.dashboard' name="Statistiky" icon={<ChartPie size={24} />} href={route('admin.dashboard')} />
                <MenuLink activeName='admin.users.index' name="Uživatelé" icon={<Users size={24} />} href={route('admin.users.index')} />
                <MenuLink activeName='admin.awards.index' name="Ocenění" icon={<Medal size={24} />} href={route("admin.awards.index")} />
                <MenuLink activeName='admin.news.index' name="Novinky a analýzy" icon={<Cards size={24} />} href={route("admin.news.index")} />
                <MenuLink activeName='admin.sets.index' name="Sety" icon={<Lego size={24} />} href={route("admin.sets.index")} />
                <MenuLink activeName='admin.minifigs.index' name="Minifigurky" icon={<LegoSmiley size={24} />} href={route("admin.minifigs.index")} />
            </div>

            <div className='relative'>
                <Img onClick={() => setUserOptions((p) => !p)} src={auth?.user?.thumbnail ?? "/assets/img/user.png"} className='object-cover object-center rounded-full w-full h-full cursor-pointer' />
                {
                    userOptions &&
                    <div className='absolute left-0 top-12 bg-white shadow-lg border rounded-md flex flex-col gap-12px p-12px min-w-[120px]'>
                        <div>{auth?.user?.first_name} {auth?.user?.last_name}</div>
                        <Link href={route('logout')} as="button" method='post' className='flex hover:bg-gray-200 p-8px rounded-md'>
                            <div className='whitespace-nowrap'>Odhlásit se</div>
                        </Link>


                    </div>
                }
            </div>
        </div>
    );
}

export default Sidebar
