import usePageProps from '@/hooks/usePageProps';
import React, { createContext, useContext, useEffect, useState } from 'react'
import { ModalsContext } from './ModalsContext';
import { MODALS } from '@/Fragments/Modals';
import axios from 'axios';




const initialState: {
    products: Array<{ product: Product, purchase_date: string, price: number, status: string, currency: string }>,
    setProducts: React.Dispatch<React.SetStateAction<Array<{ product: Product, purchase_date: string, price: number, status: string, currency: string }>>>,
    hasProducts: boolean,
    setHasProducts: React.Dispatch<React.SetStateAction<boolean>>,
    displayModal: boolean,
    setDisplayModal: React.Dispatch<React.SetStateAction<boolean>>,
    selected: Product | undefined
    setSelected: React.Dispatch<React.SetStateAction<Product>>
} = {
    products: [],
    setProducts: {} as any,
    hasProducts: false,
    setHasProducts: {} as any,
    displayModal: false,
    setDisplayModal: {} as any,
    setSelected: {} as any,
    selected: undefined

}


export const PortfolioContext = createContext(initialState);

export default function PortfolioContextProvider(props: { children: any }) {

    const { } = props
    // const { auth } = usePageProps<{ auth: { user: User } }>();
    let { open, close } = useContext(ModalsContext)
    let [products, setProducts] = useState([])
    let [hasProducts, setHasProducts] = useState(false)
    let [displayModal, setDisplayModal] = useState(true)
    let [selected, setSelected] = useState(undefined)

    let [user, setUser] = useState(undefined)

    async function getUser() {

        let response = await axios.get(route('get_user')).then((r) => {
            // console.log(r.data)
            if (r?.data?.user?.products > 0) {
                close();
            }
            setUser(r.data)
        })
    }

    useEffect(() => {
        if(user == undefined){
            getUser()
        }
    }, [])

    useEffect(() => {

        if (user?.products?.length > 0) {
            setHasProducts(true);
            close();
        } else {
            if (displayModal) {
                console.log('nema produkty')
                open(MODALS.PORTFOLIO)
            }

        }
    }, [user])


    return (
        <PortfolioContext.Provider
            value={{
                products: products,
                setProducts: setProducts,
                hasProducts: hasProducts,
                setHasProducts: setHasProducts,
                displayModal: displayModal,
                setDisplayModal: setDisplayModal,
                selected: selected,
                setSelected: setSelected
            }} >
            {props.children}
        </PortfolioContext.Provider>
    )
}