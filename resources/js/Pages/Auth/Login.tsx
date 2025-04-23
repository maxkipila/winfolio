import Img from '@/Components/Image'
import { t } from '@/Components/Translator';
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
        post(route('login.account'));
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
                            <div className='text-xl font-bold mb-16px text-center'>{inDB == null ? t("Začněte zadáním e-mailu") : (preRegistered ? t("Potvrďte e-mail zadáním kódu") : inDB == true ? t("Přihlásit se") : t("Začněte zadáním e-mailu"))}</div>
                        }
                        {
                            !preRegistered &&
                            <TextField placeholder={t('Váš e-mail')} className='w-full' name="email" />
                        }
                        {
                            (inDB === true && !preRegistered) &&
                            <>
                                <PasswordField label={t('Heslo')} className='w-full' type='password' name="password" placeholder={t('Heslo')} />
                                <div className='flex gap-8px items-center justify-between w-full mb-32px'>
                                    <Checkbox name="agree" label={t("Zapamatuj si mě")} />
                                </div>
                                <Button href="#" onClick={(e) => { e.preventDefault(); login(e) }}>{t('Přihlásit se')}</Button>
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

                                <Button href="#" onClick={(e) => { e.preventDefault(); register(e) }}>{t('Registrovat se')}</Button>

                            </>
                        }
                        {
                            (preRegistered && !emailConfirmed) &&
                            <>
                                <CodeField name="code" length={6} />
                                <Button href="#" onClick={(e) => { e.preventDefault(); confirmEmail(e) }}>{t('Potvrdit')}</Button>
                            </>
                        }
                        {
                            emailConfirmed &&
                            <>
                                <TextField name="first_name" placeholder={t('Jméno')} />
                                <TextField name="last_name" placeholder={t('Příjmení')} />
                                <TextField name="nickname" placeholder={t('@username')} />
                                <div className='mt-24px'>{t('Telefonní číslo')}</div>
                                <div className='flex gap-8px'>
                                    <Select name="prefix" placeholder={t('Prefix')} options={[
                                        { text: '+420', value: '+420' }
                                    ]} />
                                    <TextField name="phone" placeholder={t('Telefon')} />
                                </div>
                                <div className='flex gap-8px'>
                                    <Select name="day" placeholder='DD' options={[
                                        { text: '01', value: '1' },
                                        { text: '02', value: '2' },
                                        { text: '03', value: '3' },
                                        { text: '04', value: '4' },
                                        { text: '05', value: '5' },
                                        { text: '06', value: '6' },
                                        { text: '07', value: '7' },
                                        { text: '08', value: '8' },
                                        { text: '09', value: '9' },
                                        { text: '10', value: '10' },
                                        { text: '11', value: '11' },
                                        { text: '12', value: '12' },
                                        { text: '13', value: '13' },
                                        { text: '14', value: '14' },
                                        { text: '15', value: '15' },
                                        { text: '16', value: '16' },
                                        { text: '17', value: '17' },
                                        { text: '18', value: '18' },
                                        { text: '19', value: '19' },
                                        { text: '20', value: '20' },
                                        { text: '21', value: '21' },
                                        { text: '22', value: '22' },
                                        { text: '23', value: '23' },
                                        { text: '24', value: '24' },
                                        { text: '25', value: '25' },
                                        { text: '26', value: '26' },
                                        { text: '27', value: '27' },
                                        { text: '28', value: '28' },
                                        { text: '29', value: '29' },
                                        { text: '30', value: '30' },
                                        { text: '31', value: '31' },
                                    ]} />
                                    <Select name="month" placeholder='MM' options={[
                                        { text: '01', value: '1' },
                                        { text: '02', value: '2' },
                                        { text: '03', value: '3' },
                                        { text: '04', value: '4' },
                                        { text: '05', value: '5' },
                                        { text: '06', value: '6' },
                                        { text: '07', value: '7' },
                                        { text: '08', value: '8' },
                                        { text: '09', value: '9' },
                                        { text: '10', value: '10' },
                                        { text: '11', value: '11' },
                                        { text: '12', value: '12' },
                                    ]} />
                                    <Select name="year" placeholder='YYYY' options={[
                                        { text: '1960', value: '1960' },
                                        { text: '1961', value: '1961' },
                                        { text: '1962', value: '1962' },
                                        { text: '1963', value: '1963' },
                                        { text: '1964', value: '1964' },
                                        { text: '1965', value: '1965' },
                                        { text: '1966', value: '1966' },
                                        { text: '1967', value: '1967' },
                                        { text: '1968', value: '1968' },
                                        { text: '1969', value: '1969' },

                                        { text: '1970', value: '1970' },
                                        { text: '1971', value: '1971' },
                                        { text: '1972', value: '1972' },
                                        { text: '1973', value: '1973' },
                                        { text: '1974', value: '1974' },
                                        { text: '1975', value: '1975' },
                                        { text: '1976', value: '1976' },
                                        { text: '1977', value: '1977' },
                                        { text: '1978', value: '1978' },
                                        { text: '1979', value: '1979' },

                                        { text: '1980', value: '1980' },
                                        { text: '1981', value: '1981' },
                                        { text: '1982', value: '1982' },
                                        { text: '1983', value: '1983' },
                                        { text: '1984', value: '1984' },
                                        { text: '1985', value: '1985' },
                                        { text: '1986', value: '1986' },
                                        { text: '1987', value: '1987' },
                                        { text: '1988', value: '1988' },
                                        { text: '1989', value: '1989' },

                                        { text: '1990', value: '1990' },
                                        { text: '1991', value: '1991' },
                                        { text: '1992', value: '1992' },
                                        { text: '1993', value: '1993' },
                                        { text: '1994', value: '1994' },
                                        { text: '1995', value: '1995' },
                                        { text: '1996', value: '1996' },
                                        { text: '1997', value: '1997' },
                                        { text: '1998', value: '1998' },
                                        { text: '1999', value: '1999' },

                                        { text: '2000', value: '2000' },
                                        { text: '2001', value: '2001' },
                                        { text: '2002', value: '2002' },
                                        { text: '2003', value: '2003' },
                                        { text: '2004', value: '2004' },
                                        { text: '2005', value: '2005' },
                                        { text: '2006', value: '2006' },
                                        { text: '2007', value: '2007' },
                                        { text: '2008', value: '2008' },
                                        { text: '2009', value: '2009' },
                                        { text: '2010', value: '2010' },
                                        { text: '2011', value: '2011' },
                                        { text: '2012', value: '2012' },
                                        { text: '2013', value: '2013' },
                                        { text: '2014', value: '2014' },
                                        { text: '2015', value: '2015' },
                                        { text: '2016', value: '2016' },
                                        { text: '2017', value: '2017' },
                                        { text: '2018', value: '2018' },
                                        { text: '2019', value: '2018' },
                                        { text: '2020', value: '2020' },
                                        { text: '2021', value: '2021' },
                                        { text: '2022', value: '2022' },
                                        { text: '2023', value: '2023' },
                                        { text: '2024', value: '2024' },
                                        { text: '2025', value: '2025' },
                                    ]} />

                                </div>
                                <TextField name="street" placeholder={t('Ulice a č. popisné')} />
                                <div className='flex gap-8px'>
                                    <TextField name="postal_code" placeholder={t('PSČ')} />
                                    <TextField name="city" placeholder={t('Město')} />
                                </div>
                                <Select name="country" placeholder={t('Stát')} options={[
                                    { text: 'CZE', value: 'CZE' }
                                ]} />
                                <div className='mt-24px flex gap-8px'>
                                    <Lock size={24} />
                                    <div>{t('Zabezpečení')}</div>
                                </div>
                                <PasswordField name="password" placeholder={t('Heslo')} />
                                <PasswordField name="password_confirmation" placeholder={t('Heslo (znova)')} />
                                <Button href="#" onClick={(e) => { e.preventDefault(); finishRegistration(e) }}>{t('Dokončit')}</Button>
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
