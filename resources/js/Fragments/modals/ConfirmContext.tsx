import React, { createContext } from 'react';


export const ConfirmContext = createContext<{ seterrs: React.Dispatch<React.SetStateAction<{}>>; }>({ seterrs: (errs) => null });
