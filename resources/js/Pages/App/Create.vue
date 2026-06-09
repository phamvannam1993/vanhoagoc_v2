<script setup>
import { Head, useForm, usePage } from "@inertiajs/vue3";
import MasterLayout from "@/Layouts/MasterLayout.vue";
import { ErrorMessage, Field, Form } from "vee-validate";
import * as yup from "yup";
import InputError from "@/Components/InputError.vue";
import { Link } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import { ref } from "vue";

const toast = useToast();
const form = useForm({
    name: "",
    image: null,
    image_show: null,
    image_db: null
});
const page = usePage();
const query = page.props.query;
const app_id = query.appId;
const rules = {
    name: yup.string().required("Tên app không được để trống")
};
const isUploading = ref(false);
const handleSubmit = () => {
    const params = {
        name: form.name,
        app_id: app_id
    };
    const formData = new FormData();
    formData.append("img", form.image_db);
    formData.append("name", form.name);
    formData.append("app_id", app_id);
    axios
        .post(route("apps.json.store"), formData)
        .then((response) => {
            if (response.data.status) {
                toast.success("Tạo app thành công");
                setTimeout(function() {
                    location.href = response.data.data.redirectUrl;
                }, 500);
            } else {
                // form error
            }
        })
        .catch((error) => {
            toast.error(error);
        });
};
const fileInput = ref(null);
const triggerFileInput = () => {
    fileInput.value.value = "";
    fileInput.value.click(); // Kích hoạt sự kiện click trên input
};
const handleFileChange = async (event) => {
    const file = event.target.files[0];

    if (!file) return;
    const isImage = file.type.startsWith("image/");

    if (!isImage) {
        return;
    }
    try {
        const formData = new FormData();
        formData.append("file", file);
        isUploading.value = true;
        const res = await axios.post("/api/upload-file", formData);
        form.image_show = res.data.s3_file_url;
        form.image_db = res.data.file_url;
    } catch (error) {
        console.error("Lỗi upload:", error);
    } finally {
        isUploading.value = false;
    }
};
const goBack = () => {
    window.location = route("apps.dashboard");
};
</script>

<template>
    <Head title="Create App" />

    <MasterLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">App</h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-4/5 sm:px-6 lg:px-8">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Thêm mới App</h1>
                <Form @submit="handleSubmit" v-slot="{ errors }">
                    <div class="relative mt-6 w-4/5">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Tên App<span class="text-red-600" aria-hidden="true">*</span>
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
                    <div class="relative mt-6 w-4/5">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Ảnh đại diện
                        </label>
                        <div class="relative mt-2 w-4/5">
                            <img
                                v-if="form.image_show"
                                :src="form.image_show"
                                @click="triggerFileInput"
                                alt="Preview"
                                class="h-[174px] w-[376px] cursor-pointer object-contain"
                            />
                            <img
                                v-else
                                @click="triggerFileInput"
                                src="/images/icon-select-image.png"
                                alt="image-select"
                                class="cursor-pointer h-[64px] w-[110px]"
                            />
                            <input
                                id="file-input"
                                type="file"
                                accept="image/*"
                                class="hidden"
                                ref="fileInput"
                                @change="handleFileChange"
                            />
                        </div>
                    </div>
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
                            Đi tiếp
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
    </MasterLayout>
</template>
