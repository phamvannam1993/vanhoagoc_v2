<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { Form } from 'vee-validate';
import { computed, defineProps, reactive, ref } from "vue";
import { Link } from '@inertiajs/vue3';
import PreviewConnectSentenceOne from '@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceOne.vue';
import PreviewConnectSentenceTwo from '@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceTwo.vue';
import PreviewConnectSentenceThree from '@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceThree.vue';
import PreviewConnectSentenceFour from '@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceFour.vue';
import PreviewConnectSentenceFive from '@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceFive.vue';
import PreviewConnectSentenceSix from '@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceSix.vue';
import PreviewConnectSentenceSeven from '@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceSeven.vue';
import PreviewConnectSentenceEight from '@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceEight.vue';
import PreviewConnectSentenceNine from '@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceNine.vue';

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
        totalInputQuestion: 3,
        totalInputAnswer: 3,
        componentMap: PreviewConnectSentenceOne,
    },
    {
        value: 2,
        totalInputQuestion: 3,
        totalInputAnswer: 3,
        componentMap: PreviewConnectSentenceTwo,
    },
    {
        value: 3,
        totalInputQuestion: 3,
        totalInputAnswer: 3,
        componentMap: PreviewConnectSentenceThree,
    },
    {
        value: 4,
        totalInputQuestion: 4,
        totalInputAnswer: 4,
        componentMap: PreviewConnectSentenceFour,
    },
    {
        value: 5,
        totalInputQuestion: 4,
        totalInputAnswer: 4,
        componentMap: PreviewConnectSentenceFive,
    },
    {
        value: 6,
        totalInputQuestion: 4,
        totalInputAnswer: 4,
        componentMap: PreviewConnectSentenceSix,
    },
    {
        value: 7,
        totalInputQuestion: 2,
        totalInputAnswer: 3,
        componentMap: PreviewConnectSentenceSeven,
    },
    {
        value: 8,
        totalInputQuestion: 2,
        totalInputAnswer: 4,
        componentMap: PreviewConnectSentenceEight,
    },
    {
        value: 9,
        totalInputQuestion: 2,
        totalInputAnswer: 4,
        componentMap: PreviewConnectSentenceNine,
    },
]);
const createListQuestion = () => {
    const item = optionsInput.value.find(
        (item) => item.value === parseInt(sample_id),
    );
    let length = item.totalInputQuestion;
    return Array.from({ length }, () => ({
        inputNumber: '',
        inputQuestion: '',
        audio: null,
        image: null,
    }));
};
const createListAnswer = () => {
    const item = optionsInput.value.find(
        (item) => item.value === parseInt(sample_id),
    );
    let length = item.totalInputAnswer;
    return Array.from({ length }, () => ({
        inputNumber: '',
        inputAnswer: '',
        audio: null,
        image: null,
    }));
};
const currentComponent = computed(() => {
    const item = optionsInput.value.find((item) => item.value === parseInt(sample_id));
    return item.componentMap || [];
});
const form = useForm({
    // todo thêm thông tin chi tiết của sample
    title: 'Điền câu hỏi vào đây',
    listQuestion: reactive(createListQuestion() || []),
    listAnswer: reactive(createListAnswer() || []),
    video: null,
    pcnl: '',
    ndgd: '',
});
const fileName = ref(props.sample?.filename || '');
const openPreview = ref(false);
const showPreview = () => {
    openPreview.value = true;
};
</script>

<template>
    <Head title="Detail Question Connect" />

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Detail Question Connect
            </h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-5/6 sm:px-6 lg:px-8">
                <h1 class="custom-ml text-[30px] font-bold text-[#2C75E3]">
                    Chi tiết câu hỏi
                </h1>
                <Form class="w-full">
                    <div class="flex w-full">
                        <div class="content-page w-full sm:px-6 lg:px-8">
                            <div
                                class="custom-height relative mt-6 w-full rounded-2xl border border-solid border-blue-400 bg-white pt-2 max-md:max-w-full"
                            >
                                <div class="mt-2 w-full gap-4">
                                    <label
                                        for="appNameInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                        Nội dung câu hỏi
                                    </label>
                                    <div class="mt-2 flex gap-4">
                                        <a-input
                                            placeholder="Điền câu hỏi vào đây"
                                            :allow-clear="true"
                                            v-model:value="form.title"
                                            size="large"
                                            class="w-2/4"
                                            disabled
                                        >
                                        </a-input>
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
                                <div class="mt-2 w-full gap-4">
                                    <div class="custom-gap mt-4 flex">
                                        <div class="flex w-1/2 gap-4">
                                            <div class="w-full">
                                                <div
                                                    v-for="(
                                                        item, index
                                                    ) in form.listQuestion"
                                                    :key="index"
                                                    class="mt-2 flex items-center gap-4"
                                                >
                                                    <input
                                                        v-model="
                                                            item.inputNumber
                                                        "
                                                        class="h-[40px] w-[40px] rounded-md border-2 border-solid border-[#E5E5E5]"
                                                        disabled
                                                    />
                                                    <a-input
                                                        :placeholder="`Câu hỏi ${index + 1}`"
                                                        :allow-clear="true"
                                                        v-model:value="
                                                            item.inputQuestion
                                                        "
                                                        size="large"
                                                        disabled
                                                    >
                                                    </a-input>
                                                    <div class="custom-grid">
                                                        <img
                                                            class="h-[30px] w-[30px]"
                                                            src="/images/icon-sound.png"
                                                            alt=""
                                                        />
                                                        <img
                                                            class="h-[30px] w-[30px]"
                                                            src="/images/icon-image-blue.png"
                                                            alt=""
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex w-1/3 gap-4">
                                            <div class="w-full">
                                                <div
                                                    v-for="(
                                                        item, index
                                                    ) in form.listAnswer"
                                                    :key="index"
                                                    class="mt-2 flex items-center gap-4"
                                                >
                                                    <input
                                                        v-model="
                                                            item.inputNumber
                                                        "
                                                        disabled
                                                        class="h-[40px] w-[40px] rounded-md border-2 border-solid border-[#E5E5E5]"
                                                    />
                                                    <a-input
                                                        :placeholder="`Câu trả lời ${index + 1}`"
                                                        :allow-clear="true"
                                                        v-model:value="
                                                            item.inputAnswer
                                                        "
                                                        size="large"
                                                        disabled
                                                    >
                                                    </a-input>
                                                    <div class="custom-grid">
                                                        <img
                                                            class="h-[30px] w-[30px]"
                                                            src="/images/icon-sound.png"
                                                            alt=""
                                                        />
                                                        <img
                                                            class="h-[30px] w-[30px]"
                                                            src="/images/icon-image-blue.png"
                                                            alt=""
                                                        />
                                                    </div>
                                                </div>
                                            </div>
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
                                    '/images/games/game_noi_' +
                                    sample_id +
                                    '.jpg'
                                "
                                alt=""
                            />
                        </div>
                    </div>
                    <div class="custom-ml mt-4 flex gap-4">
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
                            <a-button class="custom-bg text-black" size="large">
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
        >
        </component>
    </SchoolLayout>
</template>
<style scoped lang="scss">
.custom-height {
    min-height: 439px;
    padding-left: 2.5rem;
}
.custom-grid {
    display: inline-grid;
    row-gap: 0.5rem;
}
.custom-ml {
    margin-left: 2rem;
}
.custom-gap {
    gap: 2rem;
}
</style>
