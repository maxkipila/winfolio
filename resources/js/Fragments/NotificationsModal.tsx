import { ModalsContext } from '@/Components/contexts/ModalsContext'
import { t } from '@/Components/Translator'
import usePageProps from '@/hooks/usePageProps'
import { Link } from '@inertiajs/react'
import { BellSimple, Medal } from '@phosphor-icons/react'
import moment from 'moment'
import React, { useContext, useEffect } from 'react'

interface Notification {
    data: string,
    id: number,
    read_at: string | null,
    type: string,
    created_at: string
}

interface NotificationCardProps extends Notification {

}
function NotifCard(props: NotificationCardProps) {
    const { created_at, id, read_at, data, type } = props
    let { close } = useContext(ModalsContext)
    useEffect(() => {
        console.log('tady by mel byt zpusob precteni notifikace')
    }, [])

    return (
        <Link onClick={() => { close() }} href={route('awards')} className='bg-[#F5F5F5] flex justify-between items-center px-8px py-14px gap-8px'>
            <div className='bg-white w-40px h-40px rounded-full flex items-center justify-center'>
                <Medal size={24} />
            </div>

            <div>
                <div className='font-nunito font-bold'>{t('Obdrželi jste nový record')}</div>
                <div className='font-nunito text-[#4D4D4D]'>{moment(created_at).format('DD. MM. YYYY')}</div>
            </div>

            <div className='bg-[#ED2E1B] h-12px w-12px rounded-full'></div>
        </Link>
    )
}

interface Props { }

function NotificationsModal(props: Props) {
    const { } = props
    let { close } = useContext(ModalsContext)
    const { notifications } = usePageProps<{ notifications: Array<Notification> }>();
    return (
        <div onClick={() => { close() }} className="bg-black bg-opacity-80  fixed top-0 left-0 w-full h-screen items-start justify-end flex z-max p-24px mob:pb-0" >
            <div>
                <div className='relative mb-21px'>
                    <BellSimple className='ml-auto' weight='fill' color="white" size={24} />
                    <div className='bg-[#ED2E1B] w-8px h-8px rounded-full absolute top-4px right-4px'></div>
                </div>
                <div onClick={(e) => { e.stopPropagation() }} className="bg-white p-24px max-w-sm mob:w-full border-2 border-black">
                    <div className='font-bold text-xl'>{t('Nové')}</div>
                    <div className='flex flex-col gap-16px'>
                        {
                            notifications?.filter((no) => no.read_at == null).map((n) =>
                                <NotifCard {...n} />
                            )
                        }
                    </div>
                    <div className='font-bold text-xl mt-40px'>{t('Starší')}</div>
                    <div className='flex flex-col gap-16px'>
                        {
                            notifications?.filter((no) => no.read_at != null).map((n) =>
                                <NotifCard {...n} />
                            )
                        }
                    </div>
                </div>
            </div>
        </div>
    )
}

export default NotificationsModal
