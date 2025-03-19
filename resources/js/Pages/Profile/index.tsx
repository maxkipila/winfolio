import Form from '@/Fragments/forms/Form';
import PasswordField from '@/Fragments/forms/inputs/PasswordField';
import Select from '@/Fragments/forms/inputs/Select';
import TextField from '@/Fragments/forms/inputs/TextField';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import ProfileLayout from '@/Layouts/ProfileLayout'
import { useForm } from '@inertiajs/react';
import { Lock } from '@phosphor-icons/react';
import React from 'react'

interface Props { }

function Index(props: Props) {
    const { } = props
    const form = useForm({});
    const { data } = form;
    return (
        <AuthenticatedLayout>
            <ProfileLayout>
                <div className='pt-24px flex flex-col'>
                    <div className='text-center font-bold text-xl'>Upravit profil</div>
                    <Form className='w-1/2 mx-auto mt-32px flex flex-col gap-8px' form={form}>
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
                        <PasswordField name="password_confirm" placeholder='Heslo (znova)' />
                    </Form>
                </div>
            </ProfileLayout>
        </AuthenticatedLayout>
    )
}

export default Index
