import Img from '@/Components/Image'
import ChangingCarousel from '@/Fragments/ChangingCarousel';
import Form from '@/Fragments/forms/Form'
import Checkbox from '@/Fragments/forms/inputs/Checkbox';
import PasswordField from '@/Fragments/forms/inputs/PasswordField';
import TextField from '@/Fragments/forms/inputs/TextField';
import Toggle from '@/Fragments/forms/inputs/Toggle';
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
            post('exists', {
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

    const login = (e) => {
        post(route('login.account'), { preserveState: false });
    };

    const register = (e) => {
        post(route('register.account'));
    };
    return (
        <div className='flex items-center p-40px h-screen font-teko'>
            <Head title="Login" />
            <div className='w-full h-full flex flex-col'>
                <div className='flex items-center justify-center'>
                    <Link href={route('welcome')}><Img src="/assets/img/logo.png" /></Link>
                </div>
                <div className='h-full justify-center items-center flex p-80px'>
                    <Form className='w-full gap-12px flex-col flex' form={form}>
                        <div className='text-xl font-bold mb-16px text-center'>{inDB == null ? "Začněte zadáním e-mailu" : inDB == true ? "Přihlásit se" : "Začněte zadáním e-mailu"}</div>
                        <TextField placeholder={'Váš e-mail'} className='w-full' name="email" />
                        {
                            inDB === true &&
                            <>
                                <PasswordField label={'Heslo'} className='w-full' type='password' name="password" placeholder='Heslo' />
                                <div className='flex gap-8px items-center justify-between w-full mb-32px'>
                                    <Checkbox name="agree" label={"Zapamatuj si mě"} />
                                </div>
                                <Button href="#" onClick={(e) => { e.preventDefault(); login(e) }}>Přihlásit se</Button>
                            </>
                        }
                        {
                            inDB === false &&
                            <>
                                <TextField className='w-full mt-16px' name="name" placeholder={'Jméno'} />
                                {/* <PasswordField label={'Heslo'} className='w-full' type='password' name="password" placeholder='Heslo' />
                                <PasswordField label={'Heslo znovu'} className='w-full' type='password' name="password_confirmation" placeholder='Heslo znovu' /> */}
                                {/* <div className='flex gap-8px items-center justify-between w-full mb-32px'>
                                    <Checkbox name="agree" label={"Souhlasím s podmínkami"} />
                                </div> */}
                                <Toggle label={"Odebírat newsletter"} name="newsletter" />

                                <Button href="#" onClick={(e) => { e.preventDefault(); register(e) }}>Registrovat se</Button>

                            </>
                        }
                    </Form>
                </div>
            </div>
            <div className='w-full h-full'>
                <ChangingCarousel />
            </div>
        </div>
    )
}

export default Login
