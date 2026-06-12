<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { Field, Form } from 'vee-validate';
import { reactive, ref, defineProps, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import PreviewChooseCorrectOne from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectOne.vue';
import PreviewChooseCorrectTwo from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectTwo.vue';
import PreviewChooseCorrectThree from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectThree.vue';
import PreviewChooseCorrectFour from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectFour.vue';
import PreviewChooseCorrectFive from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectFive.vue';
import PreviewChooseCorrectSix from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectSix.vue';
import PreviewChooseCorrectSeven from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectSeven.vue';
import PreviewChooseCorrectEight from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectEight.vue';
import PreviewChooseCorrectNine from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectNine.vue';
import PreviewChooseCorrectTen from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectTen.vue';
import PreviewChooseCorrectEleven from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectEleven.vue';
import PreviewChooseCorrectTwelve from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectTwelve.vue';

const props = defineProps({
    // Todo Trong sample chứa số lượng input
    sample: {},
});
const toast = useToast();
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
        totalInput: 2,
        componentMap: PreviewChooseCorrectOne,
    },
    {
        value: 2,
        totalInput: 3,
        componentMap: PreviewChooseCorrectTwo,
    },
    {
        value: 3,
        totalInput: 4,
        componentMap: PreviewChooseCorrectThree,
    },
    {
        value: 4,
        totalInput: 4,
        componentMap: PreviewChooseCorrectFour,
    },
    {
        value: 5,
        totalInput: 6,
        componentMap: PreviewChooseCorrectFive,
    },
    {
        value: 6,
        totalInput: 3,
        componentMap: PreviewChooseCorrectSix,
    },
    {
        value: 7,
        totalInput: 4,
        componentMap: PreviewChooseCorrectSeven,
    },
    {
        value: 8,
        totalInput: 3,
        componentMap: PreviewChooseCorrectEight,
    },
    {
        value: 9,
        totalInput: 4,
        componentMap: PreviewChooseCorrectNine,
    },
    {
        value: 10,
        totalInput: 3,
        componentMap: PreviewChooseCorrectTen,
    },
    {
        value: 11,
        totalInput: 4,
        componentMap: PreviewChooseCorrectEleven,
    },
    {
        value: 12,
        totalInput: 4,
        componentMap: PreviewChooseCorrectTwelve,
    },
]);
const createListAnswer = () => {
    const item = optionsInput.value.find((item) => item.value === parseInt(sample_id));
    let length = item.totalInput;
    return Array.from({ length }, () => ({
        inputValue: '',
        checked: false,
        audio: '',
        image: '',
    }));
};
const currentComponent = computed(() => {
    const item = optionsInput.value.find((item) => item.value === parseInt(sample_id));
    return item.componentMap || [];
});
const form = useForm({
    title: props.sample?.title ? props.sample?.title : 'Chọn đáp án đúng',
    question: '',
    video: null,
    audio: null,
    image: null,
    sampleId: sample_id,
    type: type,
    listAnswer: reactive(createListAnswer() || []),
    pcnl: '',
    ndgd: '',
});
const fileName = ref(props.sample?.fileName ?? '');
const openPreview = ref(false);
const showPreview = () => {
    openPreview.value = true;
};
</script>

<template>
    <Head title="Detail Choose Correct" />

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Detail Choose Question
            </h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-5/6 sm:px-6 lg:px-8">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">
                    Chi tiết câu hỏi
                </h1>
                <Form class="w-full">
                    <div class="flex w-full">
                        <div class="content-page w-full sm:px-6 lg:px-8">
                            <div
                                class="custom-height relative mt-6 w-full rounded-2xl border border-solid border-blue-400 bg-white pt-2 max-md:max-w-full"
                            >
                                <div class="mt-2 flex w-full gap-4">
                                    <Field name="title">
                                        <a-input
                                            placeholder="Bạn điền tên"
                                            :allow-clear="true"
                                            v-model:value="form.title"
                                            size="large"
                                            class="w-2/4"
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
                                        <img
                                            class="cursor-pointer"
                                            src="/images/icon-game-choose-correct/upload-video.png"
                                            alt=""
                                        />
                                        <span class="ml-2" v-if="fileName">
                                            {{ fileName }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-2 w-4/5 gap-4">
                                    <div class="flex gap-4">
                                        <a-textarea
                                            placeholder="Bạn nhập nội dung hoặc tải ảnh ở đây"
                                            :allow-clear="true"
                                            v-model:value="form.question"
                                            :rows="8"
                                            disabled
                                        >
                                        </a-textarea>
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
                                            <img
                                                class="h-[30px] w-[30px]"
                                                src="/images/icon-video.png"
                                                alt=""
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 flex w-4/5 gap-4">
                                    <div
                                        v-for="(item, index) in form.listAnswer"
                                        :key="index"
                                        class="flex w-1/3 flex-col items-center"
                                    >
                                        <!-- Textarea -->
                                        <a-textarea
                                            placeholder="Bạn nhập nội dung hoặc tải ảnh ở đây"
                                            :allow-clear="true"
                                            v-model:value="item.inputValue"
                                            :rows="4"
                                            class="w-full"
                                            disabled
                                        >
                                        </a-textarea>
                                        <div class="mt-4 flex gap-4">
                                            <a-checkbox
                                                v-model:checked="item.checked"
                                                class="mt-2"
                                                disabled
                                            >
                                            </a-checkbox>
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
                        <div class="relative mt-6 w-1/3 sm:px-6 lg:px-8">
                            <img
                                class="h-[148px] w-[263px]"
                                :src="
                                    BASE_URL +
                                    '/images/games/game_chon_' +
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
}
</style>
