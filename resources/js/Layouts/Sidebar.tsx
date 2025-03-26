import Icon from "@/Components/Icon";
import Img from "@/Components/Image";
import usePageProps from "@/hooks/usePageProps";
import { Link } from "@inertiajs/react";
import { ChartPie, Users, Medal, Cards, Lego, LegoSmiley, Newspaper } from "@phosphor-icons/react";
import { ReactNode, useEffect, useRef, useState } from "react";


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
    const [userOptions, setUserOptions] = useState(false);
    const menuRef = useRef(null);

    useEffect(() => {
        function handleClickOutside(event) {
            if (menuRef.current && !menuRef.current.contains(event.target)) {
                setUserOptions(false);
            }
        }

        document.addEventListener("mousedown", handleClickOutside);
        return () => document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    return (
        <div className='bg-white w-[80px] border-r-[1px] border-[#DEDFE5] h-screen flex flex-col items-center  justify-between sticky '>
            <Link href={route('dashboard')}>
                <Icon name='Logo' />
            </Link>

            <div className='flex flex-col text-bold gap-16px p-16px'>
                <MenuLink activeName='admin.dashboard' name="Statistiky" icon={<ChartPie className="text-black" size={24} weight="bold" />} href={route('admin.dashboard')} />
                <MenuLink activeName='admin.users.index' name="Uživatelé" icon={<Users size={24} className="text-black" weight="bold" />} href={route('admin.users.index')} />
                <MenuLink activeName='admin.awards.index' name="Ocenění" icon={<Medal size={24} className="text-black" weight="bold" />} href={route("admin.awards.index")} />
                <MenuLink activeName='admin.news.index' name="Novinky a analýzy" icon={<Newspaper className="text-black" weight="bold" size={24} />} href={route("admin.news.index")} />
                <MenuLink activeName='admin.products.index.set' name="Sety" icon={<Lego size={24} className="text-black" weight="bold" />} href={route("admin.products.index.set")} />
                <MenuLink activeName='admin.profucts.index.minifig' name="Minifigurky" icon={<LegoSmiley className="text-black" weight="bold" size={24} />} href={route("admin.products.index.minifig")} />
            </div>

            <div className="relative" ref={menuRef}>
                <Img
                    onClick={() => setUserOptions(prev => !prev)}
                    src={auth?.user?.thumbnail ?? "/assets/img/user.png"}
                    alt="Profilový obrázek uživatele"
                    className="object-cover object-center rounded-full w-full h-full cursor-pointer"
                />
                {userOptions && (
                    <div className="absolute left-0 top-12 z-10 bg-white shadow-lg border rounded-md flex flex-col gap-[12px] p-[12px] min-w-[120px]">
                        <div className="px-2 py-1" role="menuitem">
                            {auth?.user?.first_name} {auth?.user?.last_name}
                        </div>
                        <Link
                            href={route('admin.logout.account')}
                            as="button"
                            method="post"
                            className="flex items-center hover:bg-gray-200 p-2 rounded-md whitespace-nowrap"
                        >
                            Odhlásit se
                        </Link>
                    </div>
                )}
            </div>
        </div>
    )
}


export default Sidebar
