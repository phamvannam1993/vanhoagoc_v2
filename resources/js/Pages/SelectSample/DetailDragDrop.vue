<script setup>
import { Head, useForm as useInertiaForm, usePage } from '@inertiajs/vue3';
import MasterLayout from '@/Layouts/MasterLayout.vue';
import { ErrorMessage, Field, Form } from 'vee-validate';
import InputError from '@/Components/InputError.vue';
import { computed, defineProps, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import PreviewDragDropOne from '@/Pages/SelectSample/Partials/DragDrop/PreviewDragDropOne.vue';
import PreviewDragDropTwo from '@/Pages/SelectSample/Partials/DragDrop/PreviewDragDropTwo.vue';
import PreviewDragDropThree from '@/Pages/SelectSample/Partials/DragDrop/PreviewDragDropThree.vue';
import PreviewDragDropFour from '@/Pages/SelectSample/Partials/DragDrop/PreviewDragDropFour.vue';

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
        totalInput: 2,
        componentMap: PreviewDragDropOne,
    },
    {
        value: 2,
        totalInput: 3,
        componentMap: PreviewDragDropTwo,
    },
    {
        value: 3,
        totalInput: 4,
        componentMap: PreviewDragDropThree,
    },
    {
        value: 4,
        totalInput: 4,
        componentMap: PreviewDragDropFour,
    },
]);
const currentComponent = computed(() => {
    const item = optionsInput.value.find((item) => item.value === parseInt(sample_id));
    return item.componentMap || [];
});
const form = useInertiaForm({
    // todo chi tiết sample
    title: 'Kéo đáp án đúng',
    audio: null,
    image: null,
    video: null,
    pcnl: '',
    ndgd: '',
    content:
        '[Nếu bạn vội vã phủ nhận thông tin mà không "    <#>0,75</#>          thì sẽ là "    <#>0,75</#>          . Nhưng tin ngày mà không dám kiểm chứng cũng là thiếu trí tuệ.\n' +
        'Giáo dục khai phóng nghĩa là phải vượt thoát khỏi chiếc hộp "    <#>0,75</#>         "]',
    answer: '["Kiểm chúng", "định kiến", "thiếu", "sức sống", "thiếu trí tuệ"]',
    answerCorrect: '["Chánh mạng"]',
});
const previewImage = ref(props.sample?.image || null); // URL preview ảnh

const openPreview = ref(false);
const showPreview = () => {
    openPreview.value = true;
};
const fileNameVideo = ref(props.sample?.fileNameVideo || '');
</script>

<template>
    <Head title="Detail Question Drag Drop" />

    <MasterLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Detail Question Drag Drop
            </h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-11/12 sm:px-6 lg:px-8">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">
                    Chi tiết câu hỏi
                </h1>
                <Form class="w-full">
                    <div class="flex w-full">
                        <div class="content-page w-3/4 sm:px-6 lg:px-8">
                            <div
                                class="custom-height relative mt-6 w-full rounded-2xl border border-solid border-blue-400 bg-white pt-2 max-md:max-w-full"
                            >
                                <div class="mt-2 w-2/4">
                                    <label
                                        for="appNameInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                        Nội dung câu hỏi
                                    </label>
                                    <div class="mt-2 flex gap-4">
                                        <div class="w-full">
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
                                        </div>
                                        <div class="flex items-center">
                                            <img
                                                class="h-[30px] w-[30px]"
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
                                <div class="mt-2 flex w-11/12 gap-4">
                                    <img
                                        v-if="previewImage"
                                        :src="previewImage"
                                        alt="Preview"
                                        class="h-[212px] w-[376px] cursor-pointer object-contain"
                                    />
                                    <div class="w-full">
                                        <label
                                            for="appNameInput"
                                            class="block text-base font-semibold text-black"
                                        >
                                            Điền nội dung theo mẫu:
                                        </label>
                                        <div>
                                            <Field
                                                name="content"
                                            >
                                                <a-textarea
                                                    placeholder="Bạn nhập nội dung "
                                                    :allow-clear="true"
                                                    v-model:value="form.content"
                                                    :rows="8"
                                                    disabled
                                                >
                                                </a-textarea>
                                            </Field>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 w-11/12 gap-4">
                                    <label
                                        for="appNameInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                        Các đáp án
                                    </label>
                                    <div class="mt-2">
                                        <Field
                                            name="answer"
                                        >
                                            <a-input
                                                placeholder="Bạn nhập nội dung "
                                                :allow-clear="true"
                                                v-model:value="form.answer"
                                                size="large"
                                                disabled
                                            >
                                            </a-input>
                                        </Field>
                                    </div>
                                </div>
                                <div class="mt-2 w-11/12 gap-4">
                                    <label
                                        for="appNameInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                        Đáp án đúng
                                    </label>
                                    <div class="mb-6 mt-2">
                                        <Field
                                            name="answerCorrect"
                                        >
                                            <a-input
                                                placeholder="Bạn nhập nội dung "
                                                :allow-clear="true"
                                                v-model:value="
                                                    form.answerCorrect
                                                "
                                                size="large"
                                                disabled
                                            >
                                            </a-input>
                                        </Field>
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
                                    '/images/games/game_keo_' +
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
            v-model:preview-image="previewImage"
        >
        </component>
    </MasterLayout>
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
