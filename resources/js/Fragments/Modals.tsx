import { ModalsContext } from '@/Components/contexts/ModalsContext';
import { useContext, useEffect, useState } from 'react';
import NotificationsModal from './NotificationsModal';
import ReviewsModal from './ReviewsModal';
import ReviewPostedModal from './ReviewPostedModal';
import BuyPremiumModal from './BuyPremiumModal';
import SuccessModal from './SuccessModal';

interface Props { }

export enum MODALS {
    NOTIFICATION,
    REVIEW,
    SUCCESS,
    REVIEW_POSTED,
    GET_PREMIUM
}

export function ModalsProvider(props) {

    const [modal, setModal] = useState<{ modal: MODALS, data?: any } | null>(null)
    const [stack, setStack] = useState<Array<{ modal: MODALS, goBack: boolean, data?: any }>>([]);

    const open = (_modal: MODALS, goBack = false, data = {}) => {
        setModal({ modal: _modal, data: data });
        setStack(s => [...s, { modal: _modal, goBack, data }]);
        console.log('something happening?:', _modal)
    }

    const back = () => {
        setStack((s) => {
            let ss = [...s];
            let prev = ss?.pop() ?? null;
            ss = prev?.goBack ? ss : [];

            let penu = ss?.pop() ?? null;

            setModal(penu);

            return [...ss, ...(penu ? [penu] : [])];
        });
    }

    const close = (_: any, forced = false) => {
        if (forced) {
            setModal(null)
            setStack([])
        }
        else
            back();
    }

    return (
        <ModalsContext.Provider value={{ modal, open, close, setModal }} >
            {props.children}
        </ModalsContext.Provider>
    )
}



function Modals(props: Props) {
    const { } = props

    const { modal, open, close } = useContext(ModalsContext)
    
    const closeOnEsc = (e: KeyboardEvent) => {
        // console.log(e.key)
        if(e.key == 'Escape')
            close()
    }

    useEffect(() => {
        try {
            window?.addEventListener('keydown', closeOnEsc)
        } catch (error) {}
      

        return () => {
            try {
                window?.removeEventListener('keydown', closeOnEsc)
            } catch (error) { }
        }
    }, [])

    return (
        <>
            {(modal?.modal == MODALS.NOTIFICATION) && <NotificationsModal  {...modal.data} />}
            {(modal?.modal == MODALS.REVIEW) && <ReviewsModal  {...modal.data} />}
            {(modal?.modal == MODALS.SUCCESS) && <SuccessModal  {...modal.data} />}
            {(modal?.modal == MODALS.GET_PREMIUM) && <BuyPremiumModal  {...modal.data} />}
            {(modal?.modal == MODALS.REVIEW_POSTED) && <ReviewPostedModal  {...modal.data} />}
        </>
    )
}

export default Modals
