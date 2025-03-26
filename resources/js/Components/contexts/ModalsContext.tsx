import { MODALS } from '@/Fragments/Modals';
import React, { createContext } from 'react';


export const ModalsContext = createContext({
    modal: null as { modal: MODALS; data?: any; } | null,
    open: (_modal: MODALS, goBack = false, data = {} as any) => { },
    close: (_: any = null, forced = false) => { },
    setModal: ((value) => { }) as React.Dispatch<React.SetStateAction<{
        modal: MODALS;
        data?: any;
    } | null>>
});
