import Img from '@/Components/Image'
import ChangingCarousel from '@/Fragments/ChangingCarousel';
import Form from '@/Fragments/forms/Form'
import Checkbox from '@/Fragments/forms/inputs/Checkbox';
import CodeField from '@/Fragments/forms/inputs/CodeField';
import PasswordField from '@/Fragments/forms/inputs/PasswordField';
import Select from '@/Fragments/forms/inputs/Select';
import TextField from '@/Fragments/forms/inputs/TextField';
import Toggle from '@/Fragments/forms/inputs/Toggle';
import { Button } from '@/Fragments/UI/Button';
import { useDebouncedCallback } from '@/hooks/useDebounceCallback';
import usePageProps from '@/hooks/usePageProps';
import { Head, Link, useForm } from '@inertiajs/react';
import { Lock } from '@phosphor-icons/react';
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
    const { auth } = usePageProps<{ auth: { user: User } }>();
    let [preRegistered, setPreRegistered] = useState(false)
    let [emailConfirmed, setEmailConfirmed] = useState(false)
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
        post(route('preregister.account'), {
            onSuccess: () => {
                setPreRegistered(true)
            }
        });
    };

    const confirmEmail = (e) => {
        post(route('confirmEmail.account'), {
            onSuccess: () => {
                setEmailConfirmed(true)
            }
        });
    };

    const finishRegistration = (e) => {
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
                        {
                            !emailConfirmed &&
                            <div className='text-xl font-bold mb-16px text-center'>{inDB == null ? "Začněte zadáním e-mailu" : (preRegistered ? "Potvrďte e-mail zadáním kódu" : inDB == true ? "Přihlásit se" : "Začněte zadáním e-mailu")}</div>
                        }
                        {
                            !preRegistered &&
                            <TextField placeholder={'Váš e-mail'} className='w-full' name="email" />
                        }
                        {
                            (inDB === true && !preRegistered) &&
                            <>
                                <PasswordField label={'Heslo'} className='w-full' type='password' name="password" placeholder='Heslo' />
                                <div className='flex gap-8px items-center justify-between w-full mb-32px'>
                                    <Checkbox name="agree" label={"Zapamatuj si mě"} />
                                </div>
                                <Button href="#" onClick={(e) => { e.preventDefault(); login(e) }}>Přihlásit se</Button>
                            </>
                        }
                        {
                            (inDB === false && !preRegistered) &&
                            <>
                                {/* <TextField className='w-full mt-16px' name="name" placeholder={'Jméno'} /> */}
                                {/* <PasswordField label={'Heslo'} className='w-full' type='password' name="password" placeholder='Heslo' />
                                <PasswordField label={'Heslo znovu'} className='w-full' type='password' name="password_confirmation" placeholder='Heslo znovu' /> */}
                                {/* <div className='flex gap-8px items-center justify-between w-full mb-32px'>
                                    <Checkbox name="agree" label={"Souhlasím s podmínkami"} />
                                </div> */}
                                {/* <Toggle label={"Odebírat newsletter"} name="newsletter" /> */}

                                <Button href="#" onClick={(e) => { e.preventDefault(); register(e) }}>Registrovat se</Button>

                            </>
                        }
                        {
                            (preRegistered && !emailConfirmed) &&
                            <>
                                <CodeField name="code" length={6} />
                                <Button href="#" onClick={(e) => { e.preventDefault(); confirmEmail(e) }}>Potvrdit</Button>
                            </>
                        }
                        {
                            emailConfirmed &&
                            <>
                                <TextField name="first_name" placeholder={'Jméno'} />
                                <TextField name="last_name" placeholder={'Příjmení'} />
                                <TextField name="username" placeholder={'@username'} />
                                <div className='mt-24px'>Telefonní číslo</div>
                                <div className='flex gap-8px'>
                                    <Select name="prefix" options={[
                                        { text: '+420', value: '+420' }
                                    ]} />
                                    <TextField name="phone" placeholder={'Telefon'} />
                                </div>
                                <div className='flex gap-8px'>
                                    <Select name="day" placeholder='DD' options={[
                                        { text: '+420', value: '+420' }
                                    ]} />
                                    <Select name="month" placeholder='MM' options={[
                                        { text: '+420', value: '+420' }
                                    ]} />
                                    <Select name="year" placeholder='YYYY' options={[
                                        { text: '+420', value: '+420' }
                                    ]} />

                                </div>
                                <TextField name="street" placeholder={'Ulice a č. popisné'} />
                                <div className='flex gap-8px'>
                                    <TextField name="postal_code" placeholder={'PSČ'} />
                                    <TextField name="city" placeholder={'Město'} />
                                </div>
                                <Select name="country" placeholder='Stát' options={[
                                    { text: 'CZE', value: 'CZE' }
                                ]} />
                                <div className='mt-24px flex gap-8px'>
                                    <Lock size={24} />
                                    <div>Zabezpečení</div>
                                </div>
                                <PasswordField name="password" placeholder='Heslo' />
                                <PasswordField name="password_confirmation" placeholder='Heslo (znova)' />
                                <Button href="#" onClick={(e) => { e.preventDefault(); finishRegistration(e) }}>Dokončit</Button>
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
