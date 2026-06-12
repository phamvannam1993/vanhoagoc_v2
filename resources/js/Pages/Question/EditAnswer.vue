<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { Form } from 'vee-validate';
import { onMounted, reactive, ref } from "vue";
import { useToast } from 'vue-toastification';

const props = defineProps({
    question: {
        type: Object,
    },
});
const toast = useToast();
const page = usePage();
const query = page.props.query;
const record = page.props.record;
const app_id = query.app_id;
const book_id = query.book_id;
const week_id = query.week_id;
const practice_id = query.practice_id;
const question_id = query.question_id;
onMounted(() => {
    loadData();
});
const inputs = reactive([]);
const loadData = async () => {
    const params = {
        question_id: question_id,
    };

    const res = await axios.get(route('questions.json.answer', params));
    if (res.data.status) {
        let arrayAnswer = res.data.data.list;
        inputs.splice(0, inputs.length, ...arrayAnswer);
    }
};
const addInput = () => {
    inputs.push({
        name: "",
        right_answer: false,
        type: props.question.question_type,
        question_id: props.question.id,
    });
};
const collectedValues = ref([]);
// Hàm xóa input theo index
const removeInput = (index) => {
    inputs.splice(index, 1);
};
const handleSubmit = () => {
    collectedValues.value = [...inputs];
    const params = {
        question_id: query.question_id,
        answer: collectedValues.value,
        app_id: app_id,
        week_id: week_id,
        book_id: book_id,
        practice_id: practice_id,
    };
    axios
        .post(route('questions.json.storeAnswer'), params)
        .then((response) => {
            if (response.data.status) {
                toast.success('Chỉnh sửa câu trả lời thành công');
                setTimeout(function () {
                    location.href = route('questions.index', {app_id: app_id, book_id: book_id, week_id: week_id, practice_id: practice_id});
                }, 500);
            } else {
                // form error
            }
        })
        .catch((error) => {
            toast.error(error);
        });
};
const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head title="Create Answer Question" />

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                App
            </h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-4/5 sm:px-6 lg:px-8">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">
                    Câu trả lời
                </h1>
                <Form @submit="handleSubmit" v-slot="{ errors }">
                    <div v-if="props.question.question_type === 'choice'">
                        <div class="relative mt-6 w-4/5">
                            <label
                                for="appNameInput"
                                class="block text-base font-semibold text-black"
                            >
                                Câu trả lời
                            </label>
                            <div class="mt-2 italic">
                                Tích vào ô vuông để chọn câu trả lời đúng
                            </div>
                        </div>
                        <div class="mt-6 w-4/5">
                            <div class="gap-4">
                                <div class="mt-4">
                                    <div
                                        class="mt-2 flex gap-4 items-center"
                                        v-for="(input, index) in inputs"
                                        :key="index"
                                    >
                                        <a-checkbox class="custom-checkbox" v-model:checked="inputs[index].right_answer"></a-checkbox>
                                        <a-input
                                            :placeholder="`Câu trả lời ${index + 1}`"
                                            :allow-clear="true"
                                            size="large"
                                            v-model:value="inputs[index].name"
                                        >
                                        </a-input>
                                        <a-button type="primary" danger @click="removeInput(index)">
                                            Xóa
                                        </a-button>
                                    </div>
                                </div>
                            </div>
                            <div class="float-right mt-4">
                                <img
                                    @click="addInput"
                                    class="cursor-pointer"
                                    src="/images/icon-plus.png"
                                    alt=""
                                />
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <div class="relative mt-6 w-4/5">
                            <label
                                for="appNameInput"
                                class="block text-base font-semibold text-black"
                            >
                                Câu trả lời
                            </label>
                        </div>
                        <div class="mt-6 w-4/5">
                            <div class="gap-4">
                                <div class="mt-4">
                                    <div
                                        class="mt-2 flex gap-4 items-center"
                                        v-for="(input, index) in inputs"
                                        :key="index"
                                    >
                                        <a-input
                                            :placeholder="`Câu trả lời ${index + 1}`"
                                            :allow-clear="true"
                                            size="large"
                                            v-model:value="inputs[index].name"
                                        >
                                        </a-input>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-16 flex w-4/5 justify-end gap-4">
                        <img
                            @click="goBack"
                            class="h-[34px] cursor-pointer"
                            src="/images/icon-button-back.png"
                            alt=""
                        />
                        <a-button
                            class="text-white"
                            size="middle"
                            type="primary"
                            html-type="submit"
                        >
                            Lưu
                        </a-button>
                    </div>
                </Form>
            </div>
        </div>
    </SchoolLayout>
</template>
<style lang="scss">
.custom-checkbox .ant-checkbox-inner {
    width: 30px;
    height: 30px; /* Tăng kích thước hình vuông checkbox */
}
.custom-checkbox .ant-checkbox-inner::after {
    width: 10px; /* Độ dài của dấu tích */
    height: 16px; /* Chiều cao của dấu tích */
    border-width: 3px; /* Độ dày nét của dấu tích */
    top: 50%; /* Đảm bảo căn giữa */
    left: 50%;
    transform: translate(-50%, -50%) rotate(45deg); /* Căn chỉnh */
}
</style>
