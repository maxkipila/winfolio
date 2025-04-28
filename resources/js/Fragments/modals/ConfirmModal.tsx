import { ReactNode, useEffect, useState } from 'react'
import { ConfirmContext } from './ConfirmContext';
import usePageProps from '@/hooks/usePageProps';

interface Props {
    title: string | any
    message: string | any
    buttons?: ReactNode
}

function ConfirmModal(props: Props) {
    const { title, message, buttons } = props

    const { errors } = usePageProps<{ errors: Record<string, any> }>();

    const [errs, seterrs] = useState({})
    const [loaded, setloaded] = useState(false)

    useEffect(() => {
        if (loaded)
            seterrs(errors);
        else
            setloaded(true);
    }, [errors])

    return (
        <div className="bg-black bg-opacity-40  fixed top-0 left-0 w-full h-screen items-center justify-center flex z-99999 px-50px mob:px-24px mob:pb-0" >
            <div className="bg-white rounded p-24px max-w-sm shadow-card">
                <h3 className="font-bold text-h2 text-green leading-none">{title}</h3>
                <div className="mb-24px mt-12px">{message}</div>
                <div className={`min-h-12px text-14 mb-8px text-C86B28 leading-tight ${errs?.['message'] ? "" : "opacity-0"}`}>{errs?.['message']}</div>
                <ConfirmContext.Provider value={{ seterrs }}>
                    <div className="flex items-center justify-center" onClick={e => { e.preventDefault(); seterrs({}); }} >
                        {buttons}
                    </div>
                </ConfirmContext.Provider >
            </div>
        </div>
    )
}

export default ConfirmModal


