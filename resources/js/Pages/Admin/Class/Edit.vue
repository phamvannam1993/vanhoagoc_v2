<script setup>
import { Head, useForm, usePage } from "@inertiajs/vue3";
import MasterLayout from "@/Layouts/MasterLayout.vue";
import { ErrorMessage, Field, Form } from "vee-validate";
import * as yup from "yup";
import InputError from "@/Components/InputError.vue";
import { Link } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import { ref } from "vue";
import SchoolLayout from "@/Layouts/SchoolLayout.vue";

const props = defineProps({
    record: {
        type: Object,
    },
});
const toast = useToast();
const form = useForm({
    id: props.record.id,
    name: props.record.name,
    year: props.record.year
});
const page = usePage();
const query = page.props.query;
const app_id = query.app_id;
const rules = {
    name: yup.string().required("Tên Phòng ban/Lớp không được để trống"),
    year: yup.string().required("Năm không được để trống"),
};
const isUploading = ref(false);
const handleSubmit = () => {
    const formData = new FormData();
    formData.append("id", form.id);
    formData.append("name", form.name);
    formData.append("app_id", app_id);
    formData.append("year", form.year);
    axios
        .post(route("admins.class.json.store"), formData)
        .then((response) => {
            if (response.data.status) {
                toast.success("Cập nhật Đơn vị/Trường học thành công");
                setTimeout(function() {
                    location.href = route("admins.class.index", { app_id: app_id });
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
    window.location = route("admins.class.index", { app_id: app_id });
};
</script>

<template>
    <Head title="Edit Class" />

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Class</h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-4/5 sm:px-6 lg:px-8">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Cập nhật Phòng ban/Lớp</h1>
                <Form @submit="handleSubmit" v-slot="{ errors }">
                    <div class="flex mt-6 w-4/5">
                        <div class="w-full">
                            <label
                                for="appNameInput"
                                class="block text-base font-semibold text-black"
                            >
                                Tên<span class="text-red-600" aria-hidden="true">*</span>
                            </label>
                            <div class="mt-2 w-4/5">
                                <Field name="name" :rules="rules.name" v-model="form.name">
                                    <a-input
                                        placeholder="Bạn điền tên"
                                        :allow-clear="true"
                                        v-model:value="form.name"
                                    >
                                    </a-input>
                                </Field>
                                <ErrorMessage class="text-sm text-red-600" name="name" />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>
                        </div>
                        <div class="w-full">
                            <label
                                for="appNameInput"
                                class="block text-base font-semibold text-black"
                            >
                                Năm<span class="text-red-600" aria-hidden="true">*</span>
                            </label>
                            <div class="mt-2 w-4/5">
                                <Field name="year" :rules="rules.year" v-model="form.year">
                                    <a-input
                                        placeholder="Bạn điền năm"
                                        :allow-clear="true"
                                        v-model:value="form.year"
                                    >
                                    </a-input>
                                </Field>
                                <ErrorMessage class="text-sm text-red-600" name="year" />
                                <InputError class="mt-2" :message="form.errors.year" />
                            </div>
                        </div>

                    </div>
<!--                    <div class="relative mt-6 w-4/5">-->
<!--                        <label-->
<!--                            for="appNameInput"-->
<!--                            class="block text-base font-semibold text-black"-->
<!--                        >-->
<!--                            Người quản lý/GV chủ nhiệm-->
<!--                        </label>-->
<!--                        <div class="mt-2 w-4/5">-->
<!--                            <a-select-->
<!--                                class="input-search w-full"-->
<!--                                v-model:value="form.user_id"-->
<!--                                show-search-->
<!--                                placeholder="Tất cả app"-->
<!--                                size="large"-->
<!--                                :options="options"-->
<!--                                :filter-option="filterOption"-->
<!--                            ></a-select>-->
<!--                        </div>-->
<!--                    </div>-->
                    <div class="mt-4 flex gap-4">
                        <a-button @click="goBack" class="custom-bg text-black" size="large">
                            Quay lại
                        </a-button>
                        <a-button
                            class="text-white"
                            size="large"
                            type="primary"
                            html-type="submit"
                        >
                            Lưu
                        </a-button>
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
    </SchoolLayout>
</template>
