import React, { DetailedHTMLProps, useCallback, useEffect, useRef, useState } from 'react'
import axios from 'axios';
import usePageProps from '@/hooks/usePageProps';

export interface ResponsiveImage {
    uuid?: string
    srcset?: string,
    srcset_webp?: string,
    url: string,
    width?: string,
    height?: string
    seo_image?: string
    alt_text?: string
}

interface Props extends Omit<DetailedHTMLProps<React.ImgHTMLAttributes<HTMLImageElement>, HTMLImageElement>, 'src'> {
    src: string | ResponsiveImage
    children?: any
}

function Img(prps: Props) {
    const { children, ...props } = prps;
    const { src } = props

    const { page_media } = usePageProps<{ page_media?: Array<ResponsiveImage> }>();
    let media = {}

    try {
        //@ts-expect-error
        media = getMedia();
    } catch (error) {
        media = page_media ?? {};
     }
    // console.log("media", media);

    const [image, setimg] = useState<ResponsiveImage>((typeof src === "string" ? media?.[src] : src))
    const ref = useRef<HTMLImageElement>(null);
    const [sizes, setsizes] = useState('1px');

    const [replacement, setReplacement] = useState<string | null>(null)


    const updateSizes = () => {
        try {
            window?.requestAnimationFrame(function () {
                if (ref.current == null)
                    return

                let size = ref.current.getBoundingClientRect().width;

                if (!size)
                    return;

                ref.current.sizes = Math.ceil(size / window?.innerWidth * 100) + 'vw';
                setsizes(Math.ceil(size / window?.innerWidth * 100) + 'vw');

            })
        } catch (error) {

        }
    }


    useEffect(() => {
        getImage();
    }, [src])


    const getImage = async () => {

        let img = (typeof src === "string" ? media?.[src] : src);

        if (typeof src === "string" && !img?.url) {
            setimg({ srcset: undefined, url: src, width: undefined, height: undefined })
            if (!(src.startsWith('http://') || src.startsWith('https://')))
                img = (await axios.get(`/images/${btoa(src)}/${route().current()}` as string, undefined)).data
        }

        setimg(img);
        updateSizes();
    }

    const getLiveImage =  () => {

        let img = (typeof src === "string" ? media?.[src] : src);

        let live_url = import.meta.env?.VITE_LIVE_URL ?? null;
        let dev_url = import.meta.env?.VITE_IMG_HOST ?? null;
        
        if(live_url && dev_url && live_url != dev_url && img.url && !img?.url?.includes(live_url))
        {
            img.url = img?.url?.replace(dev_url, live_url);
            img.seo_image = img?.seo_image?.replace(dev_url, live_url);
            img.srcset = img?.srcset?.replaceAll(dev_url, live_url);
            img.srcset_webp = img?.srcset_webp?.replace(dev_url, live_url);
        }

        return img;

    }

    const { srcset, srcset_webp, url, width, height, alt_text } = image ?? { srcset: undefined, srcset_webp: undefined, url: undefined, width: undefined, height: undefined };

    const getSmall = () => {
        return srcset?.split(", ")?.find(s => s.includes('data:image'))?.replace(' 1w', '') ?? url;
    }

    const onError = (e) => {
        let img = getLiveImage();
        if (img) {
            setReplacement(img.url);
            // updateSizes();
        }
        else
            setReplacement(r => r ? getSmall() : url);
    }

    return (
        replacement ? <img  {...props} onError={onError} src={replacement} />
            : (
                (srcset || url)
                    ?
                    <picture /* className={props.className} */ className='contents'  >
                        {children && (typeof children === 'function' ? children({ sizes }) : children)}
                        {srcset_webp && <source type="image/webp" srcSet={srcset_webp} sizes={sizes} />}
                        <img ref={ref} {...props} {...(alt_text ? { alt: alt_text } : {})} onError={onError} sizes="1px" srcSet={srcset == "" ? undefined : srcset} src={url} width={width} height={height} />
                    </picture>
                    : <img  {...props} src={src as string} />
            )
    )
}

export default Img

interface SProps extends Omit<DetailedHTMLProps<React.SourceHTMLAttributes<HTMLSourceElement>, HTMLSourceElement>, 'src'> {
    src: string | ResponsiveImage
}

export function Source(props: SProps) {
    const { src, sizes } = props
    
    const { page_media } = usePageProps<{ page_media?: Array<ResponsiveImage> }>();
    let media = {}

    try {
        //@ts-expect-error
        media = getMedia();
    } catch (error) {
        media = page_media ?? {};
     }

    const [image, setimg] = useState<ResponsiveImage>((typeof src === "string" ? media?.[src] : src))
    const ref = useRef<HTMLSourceElement>(null);


    useEffect(() => {
        getImage();
    }, [src])


    const getImage = async () => {

        let img = (typeof src === "string" ? media?.[src] : src);

        if (typeof src === "string" && !img?.url) {
            setimg({ srcset: undefined, url: src, width: undefined, height: undefined })
            if (!(src.startsWith('http://') || src.startsWith('https://')))
                img = (await axios.get(`/images/${btoa(src)}/${route().current()}` as string, undefined)).data
        }

        setimg(img);
        // updateSizes();

    }

    const { srcset, srcset_webp, url, width, height, alt_text } = image ?? { srcset: undefined, srcset_webp: undefined, url: undefined, width: undefined, height: undefined };

    return (
        (srcset || url)
            ? (

                <>
                    {srcset_webp && <source type="image/webp" srcSet={srcset_webp} sizes={sizes} media={props.media} />}
                    <source {...props} sizes={sizes} {...(alt_text ? { alt: alt_text } : {})} srcSet={srcset == "" ? undefined : srcset} src={url} /* width={width} height={height} */ />
                </>
            )


            : <source  {...props} src={src as string} />
    )

}

