import Img from '@/Components/Image'
import ChangingCarousel from '@/Fragments/ChangingCarousel';
import Form from '@/Fragments/forms/Form'
import Checkbox from '@/Fragments/forms/inputs/Checkbox';
import PasswordField from '@/Fragments/forms/inputs/PasswordField';
import PasswordFieldAdmin from '@/Fragments/forms/inputs/PasswordFieldAdmin';
import TextField from '@/Fragments/forms/inputs/TextField';
import TextFieldAdmin from '@/Fragments/forms/inputs/TextfieldAdmin';
import { Button } from '@/Fragments/UI/Button';
import { useDebouncedCallback } from '@/hooks/useDebounceCallback';
import { Head, Link, useForm } from '@inertiajs/react';
import React, { useState } from 'react'
import { useEffect } from 'react';

interface Props { }

function Login(props: Props) {

    const { } = props
    const form = useForm({
        email: ''
    });
    const { data, post, clearErrors } = form;
    let [inDB, setInDB] = useState(null)
    const search = useDebouncedCallback((d: string) => {

        if (d?.length > 0) {
            post(route('admin.exists'), {
                onSuccess: (res) => {
                    setInDB(true)
                },
                onError: () => { setInDB(false); clearErrors('email') }
            })
        } else {
            setInDB(null)
        }
    }, 700);

    useEffect(() => {
        if (data['email']?.includes('@')) {
            search(data["email"]);
        }

    }, [data["email"]])

    console.log(route('admin.login.account'))
    const login = (e) => {
        post(route('admin.login.account'), { preserveState: true });
    };

    const register = (e) => {
        post(route('register.account'));
    };

    return (
        <div className='flex items-center p-16px h-screen'>

            <Head title="Login" />
            <div className='w-1/2 h-full flex flex-col'>
                <div className='h-full flex-col  justify-center items-center flex p-80px'>
                    <div className='flex items-center justify-center'>
                        <Link href={route('welcome')}><Img src="/assets/img/logo.png" /></Link>
                    </div>
                    <Form className='w-full ' form={form}>
                        {/* <div className='text-xl  font-bold mb-16px text-center'>Začněte zadáním e-mailu</div> */}
                        <div className='flex flex-col mt-16px gap-8px'>
                            <TextFieldAdmin wraperClassName='font-nunito font-regular ring-color-black' label={'E-mail'} placeholder={'E-mail'} className='w-full' name="email" />
                            {
                                inDB === true &&
                                <>
                                    <PasswordFieldAdmin label={'Heslo'} className='w-full font-nunito tex-black font-regular ring-color-black' type='password' name="password" placeholder='Heslo' />
                                    <div className='flex gap-8px items-center justify-between w-full mb-32px'>
                                        <Checkbox className='font-nunito font-regular text-16px leading-24px' name="agree" label={"Zapamatovat si heslo?"} />
                                        <div className='underline font-nunito cursor-pointer text-black font-black text-[16px] leading-[24px]'>
                                            Zapomenuté heslo
                                        </div>
                                    </div>
                                    <Button className='font-teko text-lg font-bold' href="#" onClick={(e) => { e.preventDefault(); login(e) }}>Přihlásít se</Button>
                                </>
                            }
                            {
                                inDB === false &&
                                <>
                                    <TextFieldAdmin label={'E-mail'} className='w-full my-16px ' name="name" />
                                    <PasswordField className='w-full font-black' type='password' name="password" placeholder='Heslo' />
                                    <PasswordField className='w-full' type='password' name="password_confirmation" placeholder='Heslo znovu' />
                                    <div className='flex gap-8px items-center justify-between w-full mb-32px'>
                                        <Checkbox name="agree" label={"Souhlasím s podmínkami"} />
                                    </div>
                                    <Button href="#" onClick={(e) => { e.preventDefault(); register(e) }}>Registrovat se</Button>
                                </>
                            }
                        </div>
                    </Form>
                </div>
            </div>
            <div className="text-white relative flex h-full w-full justify-center items-center">
                <Img src={'/assets/img/legoAdminAuth.png'} className="w-full h-full object-cover" />
            </div>
        </div>
    )
}

export default Login
