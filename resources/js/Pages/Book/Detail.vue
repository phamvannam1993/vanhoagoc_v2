<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { Field, Form } from 'vee-validate';
import { Link } from '@inertiajs/vue3';

const { appList } = defineProps({
    appList: {
        type: Array,
    },
});
const options = appList.map((v) => {
    return { value: v.id, label: v.name };
});

const bookOptions = [
    {
        value: 'canhdieu',
        label: 'Cánh diều',
    },
    {
        value: 'kntt',
        label: 'Kết nối tri thức',
    },
];

const classOptions = [
    {
        value: 1,
        label: 'lớp 1',
    },
    {
        value: 2,
        label: 'lớp 2',
    },
    {
        value: 3,
        label: 'lớp 3',
    },
    {
        value: 4,
        label: 'lớp 4',
    },
    {
        value: 5,
        label: 'lớp 5',
    },
];

const page = usePage();
const record = page.props.record;
const form = useForm({
    title: record.title,
    name: record.name,
    image: null,
    app_id: record.app_id,
    bo_sach: record.bo_sach,
    lop: parseInt(record.lop),
    delete_file: false,
});
const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head title="Detail Book" />

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                App
            </h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-4/5 sm:px-6 lg:px-8">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">
                    Chi tiết sách/ khóa học
                </h1>
                <Form>
                    <div class="relative mt-6 w-4/5">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Tên khóa học/sách<span
                                class="text-red-600"
                                aria-hidden="true"
                                >*</span
                            >
                        </label>
                        <div class="mt-2 w-4/5">
                            <Field
                                name="title"
                            >
                                <p>
                                    {{ form.title }}
                                </p>
                            </Field>
                        </div>
                    </div>
                    <div class="relative mt-6 w-4/5">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            ID Sách<span class="text-red-600" aria-hidden="true"
                                >*</span
                            >
                        </label>
                        <div class="mt-2 w-4/5">
                            <Field name="name">
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
                            App
                        </label>
                        <div class="mt-2 w-4/5">
                            <a-select
                                class="input-search w-full"
                                v-model:value="form.app_id"
                                show-search
                                placeholder="Tất cả app"
                                size="large"
                                :options="options"
                                disabled
                            ></a-select>
                        </div>
                    </div>
                    <div class="relative mt-6 w-4/5">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Bộ sách
                        </label>
                        <div class="mt-2 w-4/5">
                            <a-select
                                class="input-search w-full"
                                v-model:value="form.bo_sach"
                                show-search
                                placeholder="Tất cả bộ sách"
                                size="large"
                                :options="bookOptions"
                                disabled
                            ></a-select>
                        </div>
                    </div>
                    <div class="relative mt-6 w-4/5">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Lớp
                        </label>
                        <div class="mt-2 w-4/5">
                            <a-select
                                class="input-search w-full"
                                v-model:value="form.lop"
                                show-search
                                placeholder="Tất cả lớp"
                                size="large"
                                :options="classOptions"
                                disabled
                            ></a-select>
                        </div>
                    </div>
                    <div class="relative mt-6 w-4/5">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Ảnh đại diện
                        </label>
                        <div
                            v-if="record.img"
                            class="image-container mt-2 w-4/5"
                        >
                            <img
                                :src="record.img"
                                alt="Preview"
                                class="image cursor-pointer object-contain"
                            />
                        </div>
                        <div v-else class="relative mt-2 w-4/5">
                            <img
                                src="/images/icon-select-image.png"
                                alt="image-select"
                                class="cursor-pointer"
                            />
                            <div
                                class="absolute inset-0 top-3/4 ml-[5rem] cursor-pointer lg:ml-[10rem]"
                            >
                                Chọn tệp
                            </div>
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
<style scoped>
i {
    font-size: 24px;
    color: red;
}
/* Định dạng container */
.image-container {
    position: relative; /* Để định vị icon trên hình ảnh */
    display: inline-block;
    width: 376px; /* Kích thước của hình ảnh */
    height: 195px; /* Kích thước của hình ảnh */
    overflow: hidden; /* Đảm bảo phần thừa bị cắt */
}

/* Hình ảnh */
.image {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Để hình ảnh không bị méo */
    border-radius: 8px; /* Tùy chọn bo góc */
}
</style>
