<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import MasterLayout from '@/Layouts/MasterLayout.vue';
import { ErrorMessage, Field, Form } from "vee-validate";
import { defineProps, ref } from "vue";
import InputError from "@/Components/InputError.vue";
import * as yup from "yup";
import { useToast } from 'vue-toastification';
import PreviewChooseCorrectOne from "@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectOne.vue";
import PreviewChooseCorrectTwo from "@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectTwo.vue";
import PreviewChooseCorrectThree from "@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectThree.vue";
import PreviewChooseCorrectFour from "@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectFour.vue";
import PreviewChooseCorrectFive from "@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectFive.vue";
import PreviewChooseCorrectSix from "@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectSix.vue";
import PreviewChooseCorrectSeven from "@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectSeven.vue";
import PreviewChooseCorrectEight from "@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectEight.vue";
import PreviewChooseCorrectNine from "@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectNine.vue";
import PreviewChooseCorrectTen from "@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectTen.vue";
import PreviewChooseCorrectEleven from "@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectEleven.vue";
import PreviewChooseCorrectTwelve from "@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectTwelve.vue";
import PreviewDragDropOne from "@/Pages/SelectSample/Partials/DragDrop/PreviewDragDropOne.vue";
import PreviewDragDropTwo from "@/Pages/SelectSample/Partials/DragDrop/PreviewDragDropTwo.vue";
import PreviewDragDropThree from "@/Pages/SelectSample/Partials/DragDrop/PreviewDragDropThree.vue";
import PreviewDragDropFour from "@/Pages/SelectSample/Partials/DragDrop/PreviewDragDropFour.vue";
import PreviewConnectSentenceOne from "@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceOne.vue";
import PreviewConnectSentenceTwo from "@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceTwo.vue";
import PreviewConnectSentenceThree from "@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceThree.vue";
import PreviewConnectSentenceFour from "@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceFour.vue";
import PreviewConnectSentenceFive from "@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceFive.vue";
import PreviewConnectSentenceSix from "@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceSix.vue";
import PreviewConnectSentenceSeven from "@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceSeven.vue";
import PreviewConnectSentenceEight from "@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceEight.vue";
import PreviewConnectSentenceNine from "@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceNine.vue";
import PreviewArrangeOne from "@/Pages/SelectSample/Partials/Arrange/PreviewArrangeOne.vue";
import PreviewArrangeTwo from "@/Pages/SelectSample/Partials/Arrange/PreviewArrangeTwo.vue";
import PreviewArrangeThree from "@/Pages/SelectSample/Partials/Arrange/PreviewArrangeThree.vue";

const props = defineProps({
    listType: {
        type: Object,
    },
    listApp: {
        type: Array,
    },
});
const page = usePage();
const query = page.props.query;
const app_id = query.app_id;
const toast = useToast();
const rules = {
    name: yup.string().required('Tên template không được để trống'),
    type: yup.string().required('Loại game không được để trống'),
    image_show: yup.string().required('Ảnh mẫu game không được để trống'),
};
const optionsType = [
    { label: 'Text', value: 1 },
    { label: 'Ảnh', value: 2 },
    { label: 'Âm thanh', value: 3 },
]
const form = useForm({
    code: '',
    type: '',
    type_label: '',
    template: {
        name: '',
        number_answer: 1,
        number_answer_connect: 1,
        max_length_question: null,
        max_length_answer: null,
        add_answer: 2,
        sub_question: 1,
        reading: 1,
        video: 1,
        audio: 1,
        preview: null,
        type_answer: null,
        type_question: null
    },
    app_id: parseInt(app_id),
    image_show: null,
    image_db: null,
    image_name: '',
    status: 'on',
});

const handleSubmit = () => {
    axios
        .post(route('templates.json.store'), form)
        .then((response) => {
            if (response.data.status) {
                toast.success('Tạo template thành công');
                setTimeout(function () {
                    location.href = route('templates.index', { app_id: app_id })
                }, 500);
            } else {
                // form error
            }
        })
        .catch((error) => {
            toast.error(error);
        });
};
const filterOption = (input, option) => {
    return option.value.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};
const options = ref(Object.entries(props.listType).map(([value, label]) => ({
    value,
    label
})));
const optionsNumber = ref([
    {
        value: 1,
        label: '1',
    },
    {
        value: 2,
        label: '2',
    },
    {
        value: 3,
        label: '3',
    },
    {
        value: 4,
        label: '4',
    },
    {
        value: 5,
        label: '5',
    },
    {
        value: 6,
        label: '6',
    },
    {
        value: 7,
        label: '7',
    },
    {
        value: 8,
        label: '8',
    },
    {
        value: 9,
        label: '9',
    },
    {
        value: 10,
        label: '10',
    },
]);
const optionsChoose = ref([
    {
        value: 1,
        label: 'Có',
    },
    {
        value: 2,
        label: 'Không',
    },
]);
const optionsApp = props.listApp.map((v) => {
    return { value: v.id, label: v.name };
});
const optionsInput = ref([
    {
        value: 13,
        totalInput: 2,
        componentMap: PreviewDragDropOne,
    },
    {
        value: 14,
        totalInput: 3,
        componentMap: PreviewDragDropTwo,
    },
    {
        value: 15,
        totalInput: 4,
        componentMap: PreviewDragDropThree,
    },
    {
        value: 16,
        totalInput: 4,
        componentMap: PreviewDragDropFour,
    },
]);
const optionsPreview = ref([
    {
        value: 'PreviewChooseCorrectOne',
        label: 'Game chọn đáp án đúng 1',
        type: 'game_chon',
    },
    {
        value: 'PreviewChooseCorrectTwo',
        label: 'Game chọn đáp án đúng 2',
        type: 'game_chon',
    },
    {
        value: 'PreviewChooseCorrectThree',
        label: 'Game chọn đáp án đúng 3',
        type: 'game_chon',
    },
    {
        value: 'PreviewChooseCorrectFour',
        label: 'Game chọn đáp án đúng 4',
        type: 'game_chon',
    },
    {
        value: 'PreviewChooseCorrectFive',
        label: 'Game chọn đáp án đúng 5',
        type: 'game_chon',
    },
    {
        value: 'PreviewChooseCorrectSix',
        label: 'Game chọn đáp án đúng 6',
        type: 'game_chon',
    },
    {
        value: 'PreviewChooseCorrectSeven',
        label: 'Game chọn đáp án đúng 7',
        type: 'game_chon',
    },
    {
        value: 'PreviewChooseCorrectEight',
        label: 'Game chọn đáp án đúng 8',
        type: 'game_chon',
    },
    {
        value: 'PreviewChooseCorrectNine',
        label: 'Game chọn đáp án đúng 9',
        type: 'game_chon',
    },
    {
        value: 'PreviewChooseCorrectTen',
        label: 'Game chọn đáp án đúng 10',
        type: 'game_chon',
    },
    {
        value: 'PreviewChooseCorrectEleven',
        label: 'Game chọn đáp án đúng 11',
        type: 'game_chon',
    },
    {
        value: 'PreviewDragDropThree',
        label: 'Game chọn đáp án đúng 12',
        type: 'game_chon',
    },
    {
        value: 'PreviewDragDropOne',
        label: 'Game kéo thả 1',
        type: 'game_keo',
    },
    {
        value: 'PreviewDragDropTwo',
        label: 'Game kéo thả 2',
        type: 'game_keo',
    },
    {
        value: 'PreviewConnectSentenceOne',
        label: 'Game nối 1',
        type: 'game_noi'
    },
    {
        value: 'PreviewConnectSentenceTwo',
        label: 'Game nối 2',
        type: 'game_noi'
    },
    {
        value: 'PreviewConnectSentenceThree',
        label: 'Game nối 3',
        type: 'game_noi'
    },
    {
        value: 'PreviewConnectSentenceFour',
        label: 'Game nối 4',
        type: 'game_noi'
    },
    {
        value: 'PreviewConnectSentenceFive',
        label: 'Game nối 5',
        type: 'game_noi'
    },
    {
        value: 'PreviewConnectSentenceSix',
        label: 'Game nối 6',
        type: 'game_noi'
    },
    {
        value: 'PreviewConnectSentenceSeven',
        label: 'Game nối 7',
        type: 'game_noi'
    },
    {
        value: 'PreviewConnectSentenceEight',
        label: 'Game nối 8',
        type: 'game_noi'
    },
    {
        value: 'PreviewConnectSentenceNine',
        label: 'Game nối 9',
        type: 'game_noi'
    },
    {
        value: 'PreviewArrangeOne',
        label: 'Game sắp xếp 1',
        type: 'game_sap_xep',
    },
    {
        value: 'PreviewArrangeTwo',
        label: 'Game sắp xếp 2',
        type: 'game_sap_xep',
    },
    {
        value: 'PreviewArrangeThree',
        label: 'Game sắp xếp 3',
        type: 'game_sap_xep',
    },
    {
        value: 'PreviewChooseArrangeOne',
        label: 'Game chọn và sắp xếp 1',
        type: 'game_chon_sap_xep',
    },
]);
const optionPreviewType = ref([]);
const isUploading = ref(false);
const goBack = () => {
    window.history.back();
};
const fileInput = ref(null);
const triggerFileInput = () => {
    if (fileInput.value) {
        fileInput.value.value = '';
        fileInput.value.click();
    }
};
const handleFileChange = async (event) => {
    const file = event.target.files[0];

    if (!file) return;
    const isImage = file.type.startsWith('image/');

    if (!isImage) {
        return;
    }
    try {
        isUploading.value = true;
        const formData = new FormData();
        formData.append('file', file);
        const res = await axios.post('/api/upload-file', formData);
        form.image_name = file.name;
        form.image_show = res.data.s3_file_url;
        form.image_db = res.data.file_url;
    } catch (error) {
        console.error("Lỗi upload:", error);
    } finally {
        isUploading.value = false;
    }
};
const removeImage = () => {
    form.image_show = '';
    form.image_db = '';
};
const selectType = (value) => {
    const selectedOption = options.value.find(option => option.value === value);
    optionPreviewType.value = optionsPreview.value.filter(item => item.type === selectedOption.value)
    form.type = selectedOption.value;
    form.type_label = selectedOption.label;
}
</script>

<template>
    <Head title="Create Template" />
    <MasterLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                App
            </h2>
        </template>

        <div class="app-page mx-auto flex w-4/5 gap-4 py-4">
            <div class="content-page w-full">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Thêm mới template</h1>
                <Form @submit="handleSubmit" v-slot="{ errors }" class="w-full">
                    <div class="flex w-full gap-8">
                        <div class="w-full">
                            <div class="flex gap-8 mt-6">
                                <div class="w-1/2 relative">
                                    <label
                                        for="appNameInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                        Tên template (bắt buộc)
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <Field
                                                    name="name"
                                                    :rules="rules.name"
                                                    v-model="form.template.name"
                                                >
                                                    <a-input
                                                        placeholder="Bạn điền tên"
                                                        :allow-clear="true"
                                                        v-model:value="form.template.name"
                                                        size="large"
                                                    >
                                                    </a-input>
                                                </Field>
                                                <ErrorMessage
                                                    class="text-sm text-red-600"
                                                    name="name"
                                                />
                                                <InputError
                                                    class="mt-2"
                                                    :message="form.errors.name"
                                                />
                                            </a-col>
                                        </a-row>
                                    </div>
                                </div>
                                <div class="w-1/2 relative">
                                    <label
                                        for="appNameInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                        Mã (ví dụ: TPL4_01)
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <a-input
                                                    placeholder="Bạn điền mã"
                                                    :allow-clear="true"
                                                    v-model:value="form.code"
                                                    size="large"
                                                >
                                                </a-input>
                                            </a-col>
                                        </a-row>
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-8 mt-6">
                                <div class="w-1/2 relative">
                                    <label
                                        for="appNameInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                        Loại game
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <a-select
                                                    class="input-search w-full"
                                                    v-model:value="form.type"
                                                    show-search
                                                    placeholder="Chọn loại game"
                                                    size="large"
                                                    :options="options"
                                                    :filter-option="filterOption"
                                                    @change="selectType"
                                                ></a-select>
                                                <Field
                                                    class="hidden"
                                                    name="type"
                                                    :rules="rules.type"
                                                    v-model="form.type"
                                                >
                                                </Field>
                                                <ErrorMessage
                                                    class="text-sm text-red-600"
                                                    name="type"
                                                />
                                                <InputError
                                                    class="mt-2"
                                                    :message="form.errors.type"
                                                />
                                            </a-col>
                                        </a-row>
                                    </div>
                                </div>
                                <div class="w-1/2 relative">
                                    <label
                                        for="appNameInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                        Mẫu preview (nếu có)
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <a-select
                                                    class="input-search w-full"
                                                    v-model:value="form.template.preview"
                                                    show-search
                                                    placeholder="Chọn mẫu hiển thị"
                                                    size="large"
                                                    :options="optionPreviewType"
                                                    :filter-option="filterOption"
                                                ></a-select>
                                            </a-col>
                                        </a-row>
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-8 mt-6">
                                <div class="w-1/2 relative">
                                    <label
                                        for="appNameInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                        Số lượng đáp án
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <a-select
                                                    class="input-search w-full"
                                                    v-model:value="form.template.number_answer"
                                                    show-search
                                                    placeholder="Chọn số lượng đáp án"
                                                    size="large"
                                                    :options="optionsNumber"
                                                    :filter-option="filterOption"
                                                ></a-select>
                                            </a-col>
                                        </a-row>
                                    </div>
                                </div>
                                <div class="w-1/2 relative">
                                    <label
                                        for="appNameInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                        Thêm đáp án
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <a-select
                                                    class="input-search w-full"
                                                    v-model:value="form.template.add_answer"
                                                    show-search
                                                    placeholder="Chọn"
                                                    size="large"
                                                    :options="optionsChoose"
                                                    :filter-option="filterOption"
                                                ></a-select>
                                            </a-col>
                                        </a-row>
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-8 mt-6">
                                <div class="w-1/2 relative">
                                    <label
                                        for="appNameInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                        Số ký tự tối đa cho câu hỏi
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <a-input
                                                    placeholder="Nhập số ký tự"
                                                    :allow-clear="true"
                                                    v-model:value="form.template.max_length_question"
                                                    size="large"
                                                >
                                                </a-input>
                                            </a-col>
                                        </a-row>
                                    </div>
                                </div>
                                <div class="w-1/2 relative">
                                    <label
                                        for="appNameInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                       Số ký tự tối đa cho mỗi đáp án
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <a-input
                                                    placeholder="Nhập số ký tự"
                                                    :allow-clear="true"
                                                    v-model:value="form.template.max_length_answer"
                                                    size="large"
                                                >
                                                </a-input>
                                            </a-col>
                                        </a-row>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.type === 'game_noi'" class="relative mt-6">
                                <div class="flex gap-8">
                                    <div class="w-1/2 relative">
                                        <label
                                            for="appNameInput"
                                            class="block text-base font-semibold text-black"
                                        >
                                            Số lượng đáp án (nối)
                                        </label>
                                        <div class="mt-2">
                                            <a-row>
                                                <a-col :span="24">
                                                    <a-select
                                                        class="input-search w-full"
                                                        v-model:value="form.template.number_answer_connect"
                                                        show-search
                                                        placeholder="Chọn số lượng đáp án"
                                                        size="large"
                                                        :options="optionsNumber"
                                                        :filter-option="filterOption"
                                                    ></a-select>
                                                </a-col>
                                            </a-row>
                                        </div>
                                    </div>
                                    <div class="w-1/2 relative">

                                    </div>
                                </div>

                            </div>
                            <div class="mt-6">
                                <div class="flex gap-8">
                                    <div class="w-1/2 relative">
                                        <label
                                            for="appNameInput"
                                            class="block text-base font-semibold text-black"
                                        >
                                            Câu hỏi phụ
                                        </label>
                                        <div class="mt-2">
                                            <a-row>
                                                <a-col :span="24">
                                                    <a-select
                                                        class="input-search w-full"
                                                        v-model:value="form.template.sub_question"
                                                        show-search
                                                        placeholder="Chọn"
                                                        size="large"
                                                        :options="optionsChoose"
                                                        :filter-option="filterOption"
                                                    ></a-select>
                                                </a-col>
                                            </a-row>
                                        </div>
                                    </div>
                                    <div class="w-1/2 relative">
                                        <label
                                            for="appNameInput"
                                            class="block text-base font-semibold text-black"
                                        >
                                            Bài đọc
                                        </label>
                                        <div class="mt-2">
                                            <a-row>
                                                <a-col :span="24">
                                                    <a-select
                                                        class="input-search w-full"
                                                        v-model:value="form.template.reading"
                                                        show-search
                                                        placeholder="Chọn"
                                                        size="large"
                                                        :options="optionsChoose"
                                                        :filter-option="filterOption"
                                                    ></a-select>
                                                </a-col>
                                            </a-row>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-8">
                                    <div class="w-1/2 relative">
                                        <label
                                            for="appNameInput"
                                            class="block text-base font-semibold text-black"
                                        >
                                            Video
                                        </label>
                                        <div class="mt-2">
                                            <a-row>
                                                <a-col :span="24">
                                                    <a-select
                                                        class="input-search w-full"
                                                        v-model:value="form.template.video"
                                                        show-search
                                                        placeholder="Chọn"
                                                        size="large"
                                                        :options="optionsChoose"
                                                        :filter-option="filterOption"
                                                    ></a-select>
                                                </a-col>
                                            </a-row>
                                        </div>
                                    </div>
                                    <div class="w-1/2 relative">
                                        <label
                                            for="appNameInput"
                                            class="block text-base font-semibold text-black"
                                        >
                                            Audio
                                        </label>
                                        <div class="mt-2">
                                            <a-row>
                                                <a-col :span="24">
                                                    <a-select
                                                        class="input-search w-full"
                                                        v-model:value="form.template.audio"
                                                        show-search
                                                        placeholder="Chọn"
                                                        size="large"
                                                        :options="optionsChoose"
                                                        :filter-option="filterOption"
                                                    ></a-select>
                                                </a-col>
                                            </a-row>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-8">
                                    <div class="w-1/2 relative">
                                        <label
                                            for="appNameInput"
                                            class="block text-base font-semibold text-black"
                                        >
                                            Lọai đáp án
                                        </label>
                                        <div class="mt-2">
                                            <a-row>
                                                <a-col :span="24">
                                                    <a-checkbox-group v-model:value="form.template.type_answer" :options="optionsType" >
                                                        <template #default="{ label, value }">
                                                            <a-checkbox :value="value" class="text-base">
                                                                {{ label }}
                                                            </a-checkbox>
                                                        </template>
                                                    </a-checkbox-group>
                                                </a-col>
                                            </a-row>
                                        </div>
                                    </div>
                                    <div class="w-1/2 relative">
                                        <label
                                            for="appNameInput"
                                            class="block text-base font-semibold text-black"
                                        >
                                            Loại câu hỏi phụ
                                        </label>
                                        <div class="mt-2">
                                            <a-row>
                                                <a-col :span="24">
                                                    <a-checkbox-group v-model:value="form.template.type_question" :options="optionsType" >
                                                        <template #default="{ label, value }">
                                                            <a-checkbox :value="value" class="text-base">
                                                                {{ label }}
                                                            </a-checkbox>
                                                        </template>
                                                    </a-checkbox-group>
                                                </a-col>
                                            </a-row>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="w-1/3">
                            <div class="relative mt-6">
                                <label
                                    for="appNameInput"
                                    class="block text-base font-semibold text-black"
                                >
                                    App
                                </label>
                                <div class="mt-2">
                                    <a-row>
                                        <a-col :span="24">
                                            <a-select
                                                class="input-search w-full"
                                                v-model:value="form.app_id"
                                                show-search
                                                placeholder="Chọn app"
                                                size="large"
                                                :options="optionsApp"
                                                :filter-option="filterOption"
                                                disabled
                                            ></a-select>
                                        </a-col>
                                    </a-row>
                                </div>
                            </div>
                            <div class="relative mt-6">
                                <label
                                    for="appNameInput"
                                    class="block text-base font-semibold text-black"
                                >
                                    Ảnh mẫu
                                </label>
                                <div class="mt-2">
                                    <a-row>
                                        <a-col :span="24">
                                            <div
                                                v-if="form.image_show"
                                                class="image-container mt-2 w-4/5"
                                            >
                                                <img
                                                    :src="form.image_show"
                                                    @click="triggerFileInput"
                                                    alt="Preview"
                                                    class="image h-[174px] w-[376px] cursor-pointer object-contain"
                                                />
                                                <img
                                                    @click="removeImage"
                                                    class="delete-icon"
                                                    src="/images/icon-game-choose-correct/icon-delete.png"
                                                    alt=""
                                                />
                                            </div>
                                            <div v-else class="relative mt-2 w-4/5">
                                                <img @click="triggerFileInput" src="/images/icon-select.png" alt="" class="cursor-pointer">
                                            </div>
                                            <Field
                                                class="hidden"
                                                name="image_show"
                                                :rules="rules.image_show"
                                                v-model="form.image_show"
                                            >
                                            </Field>
                                            <ErrorMessage
                                                class="text-sm text-red-600"
                                                name="image_show"
                                            />
                                            <InputError
                                                class="mt-2"
                                                :message="form.errors.image_show"
                                            />
                                            <input
                                                id="file-input"
                                                type="file"
                                                accept="image/*"
                                                class="hidden"
                                                ref="fileInput"
                                                @change="handleFileChange"
                                            />
                                        </a-col>
                                    </a-row>
                                </div>
                            </div>
                            <div class="relative mt-6">
                                <label
                                    for="appNameInput"
                                    class="block text-base font-semibold text-black"
                                >
                                    Trạng thái
                                </label>
                                <div class="mt-2">
                                    <a-row>
                                        <a-col :span="24">
                                            <a-radio-group v-model:value="form.status">
                                                <a-radio :value="'on'">Công khai</a-radio>
                                                <a-radio :value="'off'">Khóa</a-radio>
                                            </a-radio-group>
                                        </a-col>
                                    </a-row>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-center gap-4">
                        <img
                            @click="goBack"
                            class="h-[34px] cursor-pointer"
                            src="/images/icon-button-back.png"
                            alt=""
                        />
                        <div class="text-center">
                            <a-row>
                                <a-col :span="24">
                                    <a-button
                                        class="w-[100px] text-white"
                                        size="middle"
                                        type="primary"
                                        html-type="submit"
                                    >
                                        Tạo
                                    </a-button>
                                </a-col>
                            </a-row>
                        </div>
                    </div>
                </Form>
            </div>

        </div>
        <a-modal
            v-model:open="isUploading"
            centered
            :closable="false"
            :footer="null"
            :maskClosable="false"
        >
            <div class="flex flex-col items-center justify-center p-4">
                <a-spin size="large" />
                <p class="mt-4 text-lg font-medium">Đang tải lên...</p>
            </div>
        </a-modal>
    </MasterLayout>
</template>
<style scoped lang="scss">
.custom-input {
    border-color: #5fb2ff;
}

.textarea-with-icon {
    position: relative;
    width: 100%;
}

.microphone-icon {
    position: absolute;
    right: 10px;
    bottom: 10px;
    cursor: pointer;
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

/* Icon delete */
.delete-icon {
    position: absolute;
    top: 10px; /* Khoảng cách từ trên cùng */
    right: 10px; /* Khoảng cách từ bên phải */
    font-size: 24px; /* Kích thước icon */
    color: white; /* Màu icon */
    background-color: rgba(0, 0, 0, 0.5); /* Nền mờ cho icon */
    border-radius: 50%; /* Làm icon thành hình tròn */
    padding: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

/* Hiệu ứng khi hover vào icon */
.delete-icon:hover {
    background-color: rgba(255, 0, 0, 0.8); /* Đổi màu nền khi hover */
}
</style>
