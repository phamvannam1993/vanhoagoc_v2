<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { Field, Form } from 'vee-validate';
import { defineProps, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    // Todo Trong sample chứa số lượng input
    sample: {},
});
const page = usePage();
const query = page.props.query;
const app_id = query.app_id;
const book_id = query.book_id;
const week_id = query.week_id;
const practice_id = query.practice_id;
const sample_id = query.sample_id;
const type = query.type;
const BASE_URL = ref(window.location.origin);
const form = useForm({
    // todo data sample trả ra
    title: 'Sắp xếp để được bức tranh hoàn thiện',
    image: null,
    audio: null,
    video: null,
    pcnl: '',
    ndgd: '',
});

const previewImage = ref(props.sample?.image || null); // URL preview ảnh
const convertAudio = () => {
    console.log('Audio');
};
const openPreview = ref(false);
const showPreview = () => {
    openPreview.value = true;
};
const fileNameVideo = ref(props.sample?.fileNameVideo || '');
const handleOk = () => {
    openPreview.value = false; // Đóng modal
};
</script>

<template>
    <Head title="Detail Question Arrange" />

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Detail Question Arrange
            </h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-4/5 sm:px-6 lg:px-8">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">
                    Chi tiết câu hỏi
                </h1>
                <Form class="w-full">
                    <div class="flex w-full">
                        <div class="content-page w-4/5 sm:px-6 lg:px-8">
                            <div
                                class="custom-height relative mt-6 w-full rounded-2xl border border-solid border-blue-400 bg-white pt-2 max-md:max-w-full"
                            >
                                <div class="mt-2 w-4/5">
                                    <label
                                        for="appNameInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                        Nội dung câu hỏi
                                    </label>
                                    <div class="mt-2 flex gap-4">
                                        <Field
                                            name="title"
                                        >
                                            <a-input
                                                placeholder="Điền câu hỏi vào đây"
                                                :allow-clear="true"
                                                v-model:value="form.title"
                                                size="large"
                                                disabled
                                            >
                                            </a-input>
                                        </Field>
                                        <div class="flex items-center">
                                            <img
                                                @click="convertAudio"
                                                class="h-[30px] w-[30px] cursor-pointer"
                                                src="/images/icon-sound.png"
                                                alt=""
                                            />
                                        </div>
                                        <div class="flex items-center">
                                            <span
                                                class="ml-2"
                                                v-if="fileNameVideo"
                                            >
                                                {{ fileNameVideo }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="custom-gap mt-8 w-4/5">
                                    <div class="flex justify-center">
                                        <img
                                            v-if="previewImage"
                                            :src="previewImage"
                                            alt="Preview"
                                            class="cursor-pointer object-contain"
                                        />
                                    </div>
                                    <div class="mb-8 mt-4 flex justify-center">
                                        <img
                                            class="h-[38px] cursor-pointer"
                                            src="/images/icon-select.png"
                                            alt=""
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div>
                                    <p class="font-bold">PCNL</p>
                                    <a-input
                                        placeholder="PCNL"
                                        :allow-clear="true"
                                        v-model:value="form.pcnl"
                                        size="large"
                                        class="w-full"
                                        disabled
                                    >
                                    </a-input>
                                </div>
                                <div class="mt-4">
                                    <p class="font-bold">NDGD</p>
                                    <a-input
                                        placeholder="NDGD"
                                        :allow-clear="true"
                                        v-model:value="form.ndgd"
                                        size="large"
                                        class="w-full"
                                        disabled
                                    >
                                    </a-input>
                                </div>
                            </div>
                        </div>
                        <div class="relative mt-6 w-1/4 sm:px-6 lg:px-8">
                            <img
                                class="h-[148px] w-[263px]"
                                :src="
                                    BASE_URL +
                                    '/images/games/game_ghep_anh_' +
                                    sample_id +
                                    '.jpg'
                                "
                                alt=""
                            />
                        </div>
                    </div>
                    <div class="mt-4 flex gap-4">
                        <Link
                            :href="
                                route('questionEditors.createExercise', {
                                    app_id: app_id,
                                    book_id: book_id,
                                    week_id: week_id,
                                    practice_id: practice_id,
                                })
                            "
                        >
                            <a-button class="custom-bg text-black" size="large" @click="() => window.history.back()">
                                Quay lại
                            </a-button>
                        </Link>
                        <a-button
                            @click="showPreview"
                            class="custom-bg text-black"
                            size="large"
                        >
                            Preview
                        </a-button>
                    </div>
                </Form>
            </div>
        </div>
        <a-modal v-model:open="openPreview" @ok="handleOk" :width="1000">
            <template #title>
                <h1 class="text-[18px] font-bold text-[#000000]">
                    {{ form.title }}
                </h1>
            </template>
            <div
                v-if="previewImage"
                class="image-container mt-16 flex justify-center gap-4"
            >
                <img
                    class="relative h-[300px] w-[300px] object-scale-down"
                    :src="previewImage"
                    alt=""
                />
                <div class="overlay absolute"></div>
                <img
                    class="absolute h-[300px] w-[300px]"
                    src="/images/games/match-photo/ghep_anh.png"
                    alt="Puzzle piece"
                />
            </div>
            <div v-else class="mt-16 flex justify-center gap-4">
                Không có dữ liệu hiển thị
            </div>
            <template #footer>
            </template>
        </a-modal>
    </SchoolLayout>
</template>
<style scoped lang="scss">
.custom-height {
    min-height: 439px;
    padding-left: 2.5rem;
}
.custom-arrange {
    height: 247px;
    width: 220px;
    border: 1px solid;
    font-size: 64px;
}
.custom-grid {
    display: inline-grid;
}
.custom-gap {
    gap: 2rem;
}
.grid {
    display: grid;
    gap: 10px;
}
.overlay {
    top: 60px;
    left: 0;
    right: 0;
    bottom: 20px;
    background-color: rgba(255, 255, 255, 0.5); /* Màu trắng mờ (opacity 50%) */
    pointer-events: none; /* Cho phép click xuyên qua lớp overlay */
}
</style>
