<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { Form } from 'vee-validate';
import { Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
const props = defineProps({
    app: {
        type: Object,
    },
});
const form = useForm({
    name: '',
});
onMounted(() => {
    form.name = props.app.name;
});
const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head title="Chi tiết App" />

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                App
            </h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-4/5 sm:px-6 lg:px-8">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">
                    Chi tiết App
                </h1>
                <Form>
                    <div class="relative mt-6 w-4/5">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Tên App<span class="text-red-600" aria-hidden="true"
                                >*</span
                            >
                        </label>
                        <div class="mt-2 w-4/5">
                            <p>{{ props.app.name }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex gap-4">
                        <a-button @click="goBack" class="custom-bg text-black" size="large">
                            Quay lại
                        </a-button>
                    </div>
                </Form>
            </div>
        </div>
    </SchoolLayout>
</template>
