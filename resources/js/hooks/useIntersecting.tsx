import { useCallback, useState } from "react";

interface Intersections {
    top: number,
    bottom: number,
    right: number,
    left: number
}

export function useIntersecting(): [boolean, any, Intersections, string, Partial<Intersections>] {

    const [isNotVisible, setNotVisible] = useState(false);
    const [intersecting, setIntersecting] = useState({ top: 0, bottom: 0, right: 0, left: 0 });
    const [observed, setObserved] = useState(false);

    const observer = new IntersectionObserver(
        ([entry]) => {
            setNotVisible(i => (i || (entry?.intersectionRatio < 1)));
            if (entry.rootBounds && entry.boundingClientRect)
                setIntersecting({
                    top: Math.max(entry.rootBounds.top - entry.boundingClientRect.top, 0),
                    bottom: Math.max(entry.boundingClientRect.bottom - entry.rootBounds.bottom, 0),
                    right: Math.max(entry.boundingClientRect.right - entry.rootBounds.right, 0),
                    left: Math.max(entry.rootBounds.left - entry.boundingClientRect.left, 0)
                })

            setObserved(true);
        }
    )

    const ref = useCallback((node) => {
        if (node)
            observer.observe(node);

        return () => { observer.disconnect() }
    }, []);

    let classes = "";

    if (!observed)
        classes += 'opacity-0'

    if (intersecting.bottom)
        classes += ' -translate-y-full -mt-2px'
    else if (intersecting.top)
        classes += ' translate-y-full'

    if (intersecting.left)
        classes += ' translate-x-full'
    else if (intersecting.right)
        classes += ' -translate-x-full'

    let styles = { top: Math.max((intersecting.top - intersecting.bottom), 0), left: Math.max((intersecting.left - intersecting.right), 0) };

    return [isNotVisible, ref as any, intersecting, classes, styles];
}