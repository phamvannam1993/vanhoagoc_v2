<script setup>
import { Head, useForm, usePage } from "@inertiajs/vue3";
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { ErrorMessage, Field, Form } from "vee-validate";
import * as yup from "yup";
import InputError from "@/Components/InputError.vue";
import { ref } from "vue";
import { Link } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";

const page = usePage();
const query = page.props.query;
const app_id = query.appId;
const toast = useToast();
const { appList } = defineProps({
    appList: {
        type: Array
    }
});
const options = appList.map((v) => {
    return { value: v.id, label: v.name };
});

const bookOptions = [
    {
        value: "canhdieu",
        label: "Cánh diều"
    },
    {
        value: "kntt",
        label: "Kết nối tri thức"
    }
];

const classOptions = [
    {
        value: 1,
        label: "lớp 1"
    },
    {
        value: 2,
        label: "lớp 2"
    },
    {
        value: 3,
        label: "lớp 3"
    },
    {
        value: 4,
        label: "lớp 4"
    },
    {
        value: 5,
        label: "lớp 5"
    }
];

const form = useForm({
    title: "",
    name: "",
    image: null,
    image_show: null,
    image_db: null,
    app_id: parseInt(app_id),
    bo_sach: "",
    lop: ""
});
const rules = {
    name: yup.string().required("Tên app không được để trống"),
    idBook: yup.string().required("ID Sách không được để trống")
};
const isUploading = ref(false);
const handleSubmit = () => {
    const formData = new FormData();
    formData.append("img", form.image_db);
    formData.append("name", form.name);
    formData.append("title", form.title);
    formData.append("app_id", form.app_id);
    formData.append("bo_sach", form.bo_sach);
    formData.append("lop", form.lop);
    axios
        .post(route("books.json.store"), formData)
        .then((response) => {
            if (response.data.status) {
                toast.success("Tạo mới sách/ khóa học thành công");
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

const handleChangeApp = (value) => {
    form.idApp = value;
};
const handleChangeBook = (value) => {
    form.idBooks = value;
};
const handleChangeClass = (value) => {
    form.idClass = value;
};
const filterOption = (input, option) => {
    return option.value.toLowerCase().indexOf(input.toLowerCase()) >= 0;
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
    window.location = route("books.index", { appId: app_id });
};
</script>

<template>
    <Head title="Create Course" />

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">App</h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-4/5 sm:px-6 lg:px-8">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">
                    Thêm mới sách/ khóa học
                </h1>
                <Form @submit="handleSubmit">
                    <div class="relative mt-6 w-4/5">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Tên khóa học/sách<span class="text-red-600" aria-hidden="true"
                        >*</span
                        >
                        </label>
                        <div class="mt-2 w-4/5">
                            <Field name="name" :rules="rules.name" v-model="form.title">
                                <a-input
                                    placeholder="Bạn điền tên"
                                    :allow-clear="true"
                                    v-model:value="form.title"
                                >
                                </a-input>
                            </Field>
                            <ErrorMessage class="text-sm text-red-600" name="name" />
                            <InputError class="mt-2" :message="form.errors.title" />
                        </div>
                    </div>
                    <div class="relative mt-6 w-4/5">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            ID Sách<span class="text-red-600" aria-hidden="true">*</span>
                        </label>
                        <div class="mt-2 w-4/5">
                            <Field name="idBook" :rules="rules.idBook" v-model="form.name">
                                <a-input
                                    placeholder="Bạn điền tên"
                                    :allow-clear="true"
                                    v-model:value="form.name"
                                >
                                </a-input>
                            </Field>
                            <ErrorMessage class="text-sm text-red-600" name="idBook" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                    </div>
                    <div class="relative mt-6 w-4/5">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            App
                        </label>
                        <div class="mt-2 w-4/5">
                            <a-select
                                class="input-search w-full"
                                v-model:value="form.app_id"
                                show-search
                                placeholder="Tất cả app"
                                size="large"
                                :options="options"
                                :filter-option="filterOption"
                                @change="handleChangeApp"
                            ></a-select>
                        </div>
                    </div>
                    <div class="relative mt-6 w-4/5">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Bộ sách
                        </label>
                        <div class="mt-2 w-4/5">
                            <a-select
                                class="input-search w-full"
                                v-model:value="form.bo_sach"
                                show-search
                                placeholder="Tất cả bộ sách"
                                size="large"
                                :options="bookOptions"
                                :filter-option="filterOption"
                                @change="handleChangeBook"
                            ></a-select>
                        </div>
                    </div>
                    <div class="relative mt-6 w-4/5">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Lớp
                        </label>
                        <div class="mt-2 w-4/5">
                            <a-select
                                class="input-search w-full"
                                v-model:value="form.lop"
                                show-search
                                placeholder="Tất cả lớp"
                                size="large"
                                :options="classOptions"
                                :filter-option="filterOption"
                                @change="handleChangeClass"
                            ></a-select>
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
                                class="cursor-pointer"
                            />
                            <div
                                v-if="!form.image_show"
                                class="absolute inset-0 top-3/4 ml-[5rem] cursor-pointer lg:ml-[10rem]"
                            >
                                Chọn tệp
                            </div>
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
    </SchoolLayout>
</template>
