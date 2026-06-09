<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import MasterLayout from '@/Layouts/MasterLayout.vue';
import { ErrorMessage, Field, Form } from "vee-validate";
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import InputError from "@/Components/InputError.vue";
import * as yup from "yup";

const toast = useToast();
const page = usePage();
const query = page.props.query;
const app_id = query.app_id;
const book_id = query.book_id;
const week_id = query.week_id;
const practice_id = query.practice_id;
const form = useForm({
    file: null,
});
const rules = {
    file: yup.mixed().required('Vui lòng chọn một tệp!'),
};
const importResult = ref(null);
const loading = ref(false);
const handleSubmit = () => {
    const formData = new FormData();
    formData.append('file', form.file);
    formData.append('app_id', app_id);
    formData.append('book_id', book_id);
    formData.append('week_id', week_id);
    formData.append('practice_id', practice_id);
    loading.value = true;
    importResult.value = null;
    axios
        .post(route('questionEditors.json.storeExercise'), formData)
        .then((response) => {
            loading.value = false;
            if (response.data.status) {
                const { count, errors, redirectUrl } = response.data.data;
                importResult.value = { count, errors };
                if (errors.length === 0) {
                    toast.success(`Import thành công ${count} câu hỏi`);
                    setTimeout(() => { location.href = redirectUrl; }, 1500);
                } else {
                    toast.warning(`Import ${count} câu hỏi, có ${errors.length} lỗi`);
                }
            }
        })
        .catch((error) => {
            loading.value = false;
            toast.error(error?.response?.data?.message || 'Có lỗi xảy ra');
        });
};
const fileName = ref('');
const fileInput = ref(null);
const triggerFileInput = () => {
    fileInput.value.click(); // Kích hoạt sự kiện click trên input
};
const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    fileName.value = file.name; // Ghi lại tên file
    form.file = file;
};
const remove = () => {
    form.file = null;
    fileName.value = '';
};
const goBack = () => {
    window.history.back();
};
</script>
<template>
    <Head title="Import Exercise" />

    <MasterLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Import Exercise
            </h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-4/5 sm:px-6 lg:px-8">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">
                    Nhập nội dung
                </h1>
                <Form @submit="handleSubmit" v-slot="{ errors }">
                    <div
                        class="custom-height relative mt-6 flex w-full rounded-2xl border border-solid border-blue-400 bg-white max-md:max-w-full"
                    >
                        <div class="ml-4 flex gap-4 pt-8">
                            <div>
                                <div class="flex gap-4">
                                    <img
                                        class="h-[38px] cursor-pointer"
                                        src="/images/icon-select.png"
                                        alt=""
                                        @click="triggerFileInput"
                                    />
                                    <div
                                        v-if="!fileName"
                                        class="flex h-[38px] items-center"
                                    >
                                        Không có tệp nào được chọn
                                    </div>
                                    <div
                                        v-else
                                        class="flex h-[38px] items-center"
                                    >
                                        {{ fileName }}
                                    </div>
                                    <input
                                        type="file"
                                        accept=".xls,.xlsx"
                                        class="hidden"
                                        ref="fileInput"
                                        @change="handleFileChange"
                                    />
                                </div>
                                <div>
                                    <Field
                                        name="file"
                                        :rules="rules.file"
                                        v-model="form.file"
                                    >
                                        <a-input
                                            class="hidden"
                                            placeholder="Bạn điền tên"
                                            :allow-clear="true"
                                            v-model:value="form.file"
                                        >
                                        </a-input>
                                    </Field>
                                    <ErrorMessage
                                        class="text-sm text-red-600"
                                        name="file"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.file"
                                    />
                                </div>
                            </div>
                            <div class="custom-right absolute">
                                <a-button
                                    class="custom-bg text-black"
                                    size="large"
                                    html-type="submit"
                                    :loading="loading"
                                >
                                    Tạo câu hỏi
                                </a-button>
                            </div>
                        </div>
                    </div>
                    <!-- Import result -->
                    <div v-if="importResult" class="mt-4 rounded-xl border p-4" :class="importResult.errors.length ? 'border-yellow-400 bg-yellow-50' : 'border-green-400 bg-green-50'">
                        <p class="font-semibold">Đã import <span class="text-blue-600">{{ importResult.count }}</span> câu hỏi thành công.</p>
                        <ul v-if="importResult.errors.length" class="mt-2 list-disc pl-5 text-sm text-red-600">
                            <li v-for="(err, i) in importResult.errors" :key="i">{{ err }}</li>
                        </ul>
                    </div>

                    <div class="float-right mt-4 flex gap-4">
                        <a-button @click="goBack" class="custom-bg text-black" size="large">
                            Quay lại
                        </a-button>
                        <a-button
                            @click="remove"
                            class="text-white"
                            size="large"
                            type="primary"
                            danger
                        >
                            Xóa
                        </a-button>
                    </div>
                </Form>
            </div>
        </div>
    </MasterLayout>
</template>
<style scoped lang="scss">
.custom-height {
    min-height: 153px;
}
.custom-right {
    right: 3rem;
}
</style>
