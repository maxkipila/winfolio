import { InertiaLinkProps, Link, router } from "@inertiajs/react";
import React, { useEffect, useState } from "react";
import { ReactElement } from "react";



interface ButtonProps extends InertiaLinkProps {
    icon?: ReactElement,
    primary?: boolean,
    outlined?: boolean,
    mini?: boolean,
    disabled?: boolean,
    disableWhenProcessing?: boolean
    color?: string,
    disabledWhite?: boolean
    wider?: boolean

}

export function Button(props: ButtonProps) {

    const [processing, setProcessing] = useState(false)

    const { icon, children, primary, outlined, wider = false, className, mini, color, disabledWhite, disableWhenProcessing, ...rest } = props

    useEffect(() => {
        let removeListener = router.on('finish', function (e) {
            if (e.detail.visit.url.toString() == rest.href)
                setProcessing(false)
        })

        return () => {
            removeListener()
        }
    }, [])


    return (
        <Link onClick={_ => setProcessing(true)} {...rest} className={getButtonStyles({ disabled: (props?.disabled || (disableWhenProcessing && processing)), primary, outlined, className, mini, color, disabledWhite, wider })}>
            {icon}
            {children}{/*  */}
        </Link>
    );
}

interface ButtonStylesProps {
    disabled?: boolean,
    primary?: boolean
    className?: string,
    outlined?: boolean,
    mini?: boolean,
    color?: string,
    disabledWhite?: boolean,
    wider?: boolean

}

function getButtonStyles({ disabled, primary, className, wider = false, outlined = false, mini, color, disabledWhite = false }: ButtonStylesProps) {
    return `${mini ? "h-32px px-16px rounded-full" : `h-48px ${wider ? "px-32px" : "px-24px"}  mob:px-8px`}
     flex whitespace-nowrap justify-center items-center ${disabled ? `${disabledWhite ? "bg-app-yellow border border-app-yellow text-black opacity-40" : "bg-app-lighter/20"}
      pointer-events-none text-app-lighter cursor-not-allowed ` :
            (primary ? (outlined ? " hover:bg-app-lighter text-app hover:text-white border-app border cursor-pointer" : " bg-[#539648]  hover:bg-app-lighter text-white cursor-pointer") :
                `${color ? ` bg-[#1A1A1A] text-black border-[#539648] border-app- bg-[${color}] hover:[#539648] border border-[${color}] hover:bg-opacity-80` : "text-[#4D3600] font-bold bg-[#FFB400] border-2 border-[#664800] w-full text-black hover:border-app-gray-999999"} cursor-pointer`)} gap-x-8px font-bold ${className}`
}
