import React, { useContext } from 'react';
import { ConfirmContext } from './ConfirmContext';
import Button from '../forms/Buttons/ModalsButton';



interface Props {
    href: string,
    onSuccess: (res?: any) => void,
    onCancel: () => void
}

export function DefaultButtons({ href, onSuccess, onCancel }: Props) {

    const { seterrs } = useContext(ConfirmContext)

    return (
        <>
            <Button
                href={href}
                method={"post"}
                preserveScroll
                preserveState
                only={['errors']}
                onSuccess={onSuccess}
                as="button"
                className="focus:outline-black"
                autoFocus
                tabIndex={0}
            >
                Ano
            </Button>
            <Button
                external
                className="!bg-black hover:!bg-gray-800 text-black ml-12px focus:outline-black cursor-pointer"
                onClick={(e) => { onCancel(); seterrs({}); }}
            >
                Zrušit
            </Button>
        </>
    )
}
