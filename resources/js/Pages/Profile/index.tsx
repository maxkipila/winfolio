import Img from '@/Components/Image';
import { t } from '@/Components/Translator';
import Form from '@/Fragments/forms/Form';
import CSelect from '@/Fragments/forms/inputs/CSelect';
import PasswordField from '@/Fragments/forms/inputs/PasswordField';
import Select from '@/Fragments/forms/inputs/Select';
import TextField from '@/Fragments/forms/inputs/TextField';
import usePageProps from '@/hooks/usePageProps';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import ProfileLayout from '@/Layouts/ProfileLayout'
import { Head, useForm } from '@inertiajs/react';
import { Lock } from '@phosphor-icons/react';
import React from 'react'

interface Props { }

function Index(props: Props) {
    const { } = props
    const { auth } = usePageProps<{ auth: { user: User } }>();
    const form = useForm({
        first_name: auth?.user?.first_name,
        last_name: auth?.user?.last_name,
        username: auth?.user?.nickname,
        prefix: auth?.user?.prefix,
        phone: auth?.user?.phone,
        day: auth?.user?.day,
        month: auth?.user?.month,
        year: auth?.user?.year,
        street: auth?.user?.street,
        postal_code: auth?.user?.psc,
        city: auth?.user?.city,
        country: auth?.user?.country

    });
    const { data } = form;

    return (
        <AuthenticatedLayout>
            <ProfileLayout>
                <Head title="Profile | Winfolio" />
                <div className='pt-24px flex flex-col'>
                    <div className='text-center font-bold text-xl'>{t('Upravit profil')}</div>
                    <Form className='w-1/2 mob:w-full mob:px-24px mx-auto mt-32px flex flex-col gap-8px' form={form}>
                        <TextField name="first_name" placeholder={t('Jméno')} />
                        <TextField name="last_name" placeholder={t('Příjmení')} />
                        <TextField prefix="@" name="username" placeholder={t('@username')} />
                        <div className='mt-24px'>{t('Telefonní číslo')}</div>
                        <div className='flex gap-8px'>
                            <div className='flex-shrink-0 min-w-[100px]'>
                                <CSelect name="prefix" placeholder={t('Prefix')} defaultValue={"+420"} options={[
                                    { value: "+420", text: <div className='flex items-center gap-8px'>{<Img src="/assets/img/cz.png" />} +420</div> },
                                    { value: "+421", text: <div className='flex items-center gap-8px'>{<Img src="/assets/img/sk.png" />} +421</div> }
                                ]} />
                            </div>
                            <TextField name="phone" placeholder={t('Telefon')} />
                        </div>
                        <div className='mt-24px'>{t('Datum narození')}</div>
                        <div className='flex gap-8px'>
                            <div className='max-w-1/3 flex-shrink-0'>
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
                            </div>
                            <div className='max-w-1/3 flex-shrink-0'>
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
                            </div>
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
                            <div className='max-w-1/3'>
                                <TextField name="postal_code" placeholder={t('PSČ')} />
                            </div>

                            <TextField className='w-full' name="city" placeholder={t('Město')} />
                        </div>
                        <Select name="country" placeholder={t('Stát')} options={[
                            { text: 'Česká Republika', value: 'CZE' }
                        ]} />
                        <div className='mt-24px flex gap-8px'>
                            <Lock size={24} />
                            <div>{t('Zabezpečení')}</div>
                        </div>
                        <PasswordField name="password" placeholder={t('Heslo')} />
                        <PasswordField name="password_confirm" placeholder={t('Heslo (znova)')} />
                    </Form>
                </div>
            </ProfileLayout>
        </AuthenticatedLayout>
    )
}

export default Index
