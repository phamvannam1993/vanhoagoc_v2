<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { ErrorMessage, Field, Form } from 'vee-validate';
import InputError from '@/Components/InputError.vue';
import { computed, defineProps, reactive, ref } from "vue";
import { Link } from '@inertiajs/vue3';
import PreviewArrangeOne from '@/Pages/SelectSample/Partials/Arrange/PreviewArrangeOne.vue';
import PreviewArrangeTwo from '@/Pages/SelectSample/Partials/Arrange/PreviewArrangeTwo.vue';
import PreviewArrangeThree from '@/Pages/SelectSample/Partials/Arrange/PreviewArrangeThree.vue';

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
const optionsInput = ref([
    {
        value: 1,
        totalInput: 3,
        componentMap: PreviewArrangeOne,
    },
    {
        value: 2,
        totalInput: 4,
        componentMap: PreviewArrangeTwo,
    },
    {
        value: 3,
        totalInput: 5,
        componentMap: PreviewArrangeThree,
    },
]);
const createListQuestion = () => {
    const item = optionsInput.value.find(
        (item) => item.value === parseInt(sample_id),
    );
    let length = item.totalInput;
    return Array.from({ length }, () => ({
        inputValue: '',
    }));
};
const currentComponent = computed(() => {
    const item = optionsInput.value.find((item) => item.value === parseInt(sample_id));
    return item.componentMap || [];
});
const form = useForm({
    // todo data sample trả ra
    title: 'Sắp xếp các số sau theo thứ tự từ bé đến lớn',
    audio: null,
    listValue: reactive(createListQuestion() || []),
    video: null,
    pcnl: '',
    ndgd: '',
});
const fileName = ref(props.sample?.fileName || '');
const openPreview = ref(false);
const showPreview = () => {
    openPreview.value = true;
};
</script>

<template>
    <Head title="Create Question Arrange" />

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Create Question Arrange
            </h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-4/5 sm:px-6 lg:px-8">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">
                    Tạo câu hỏi
                </h1>
                <Form
                    class="w-full"
                >
                    <div class="flex w-full">
                        <div class="content-page w-4/5 sm:px-6 lg:px-8">
                            <div
                                class="custom-height relative mt-6 w-full rounded-2xl border border-solid border-blue-400 bg-white pt-2 max-md:max-w-full"
                            >
                                <div class="mt-2 w-11/12">
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
                                                class="h-[30px] w-[30px]"
                                                src="/images/icon-sound.png"
                                                alt=""
                                            />
                                        </div>
                                        <div class="flex items-center">
                                            <span class="ml-2" v-if="fileName">
                                                {{ fileName }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="custom-gap mt-8 flex w-11/12">
                                    <div
                                        v-for="(item, index) in form.listValue"
                                        :key="index"
                                        role="article"
                                        tabindex="0"
                                    >
                                        <div
                                            class="flex min-h-full items-center justify-center font-bold"
                                        >
                                            <a-input
                                                class="custom-arrange rounded-2xl border border-[#000000] bg-white"
                                                placeholder=""
                                                :allow-clear="true"
                                                v-model:value="item.inputValue"
                                                size="large"
                                                disabled
                                            >
                                            </a-input>
                                        </div>
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
                                    '/images/games/game_sap_xep_' +
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
        <component
            :is="currentComponent"
            v-model:open-preview="openPreview"
            v-model:data="form"
        ></component>
    </SchoolLayout>
</template>
<style scoped lang="scss">
.custom-height {
    min-height: 439px;
    padding-left: 2.5rem;
}
.custom-arrange {
    height: 247px;
    border: 1px solid;
    font-size: 64px;
}
.custom-grid {
    display: inline-grid;
}
.custom-gap {
    gap: 2rem;
}
</style>
