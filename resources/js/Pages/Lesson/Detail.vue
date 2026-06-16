<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { Field, Form } from 'vee-validate';
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const query = page.props.query;
const app_id = query.app_id;
const book_id = query.book_id;
const week_id = query.week_id;
const props = defineProps({
    lesson: {
        type: Object,
    },
});
const form = useForm({
    name: props.lesson?.name,
    numberPractice: parseInt(props.lesson?.numberPractice),
    taptrung: props.lesson?.taptrung,
});
const options = ref([
    {
        value: 'true',
        label: 'Có',
    },
    {
        value: 'false',
        label: 'Không',
    },
]);
</script>

<template>
    <Head title="Detail Lesson" />

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                App
            </h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-4/5 sm:px-6 lg:px-8">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">
                    Chi tiết bài học
                </h1>
                <Form>
                    <div class="relative mt-6 w-4/5">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Tên bài học<span
                                class="text-red-600"
                                aria-hidden="true"
                                >*</span
                            >
                        </label>
                        <div class="mt-2 w-4/5">
                            <Field
                                name="name"
                            >
                                <p>
                                    {{ form.name }}
                                </p>
                            </Field>
                        </div>
                    </div>
                    <div class="relative mt-6 w-4/5">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            ID bài học<span
                                class="text-red-600"
                                aria-hidden="true"
                                >*</span
                            >
                        </label>
                        <div class="mt-2 w-4/5">
                            <Field name="idLesson">
                                <a-select
                                    class="input-search w-full"
                                    v-model:value="form.numberPractice"
                                    show-search
                                    placeholder="Tất cả app"
                                    size="large"
                                    :options="props.listNumberPractice"
                                    disabled
                                ></a-select>
                            </Field>
                        </div>
                    </div>
                    <div class="relative mt-6 w-4/5">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Tập trung
                        </label>
                        <div class="mt-2 w-4/5">
                            <a-select
                                class="input-search w-full"
                                v-model:value="form.taptrung"
                                show-search
                                placeholder="Có"
                                size="large"
                                :options="options"
                                disabled
                            ></a-select>
                        </div>
                    </div>
                    <div class="mt-4 flex gap-4">
                        <Link
                            :href="
                                route('lessons.index', {
                                    app_id: app_id,
                                    book_id: book_id,
                                    week_id: week_id,
                                })
                            "
                        >
                            <a-button class="custom-bg text-black" size="large" @click="() => window.history.back()">
                                Quay lại
                            </a-button>
                        </Link>
                    </div>
                </Form>
            </div>
        </div>
    </SchoolLayout>
</template>
