<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Field, Form, ErrorMessage } from 'vee-validate';
import * as yup from 'yup';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

// const submit = () => {
//     form.post(route('register'), {
//         onFinish: () => form.reset('password', 'password_confirmation'),
//     });
// };

const rules = {
    name: yup.string().required('Tên không được để trống').min(3, "Tên phải có ít nhất 3 ký tự."),
    email: yup.string().required('Email không được để trống').email('Email không đúng định dạng')
}

const handleSubmit = (values) => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <Form @submit="handleSubmit" v-slot="{ errors }">
            <div>
                <InputLabel for="name" value="Name" />
                <Field name="name" :rules="rules.name" v-model="form.name">
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        autofocus
                        autocomplete="name"
                    />
                </Field>
                <ErrorMessage name="name" />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />
                <Field name="email" :rules="rules.email" v-model="form.email">
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        autocomplete="username"
                    />
                </Field>
                <ErrorMessage name="email" />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Already registered?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register
                </PrimaryButton>
            </div>
        </Form>
    </GuestLayout>
</template>
