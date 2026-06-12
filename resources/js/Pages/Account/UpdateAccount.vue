<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { ErrorMessage, Field, Form } from "vee-validate";
import { defineProps, ref } from "vue";
import InputError from "@/Components/InputError.vue";
import * as yup from "yup";
import { useToast } from 'vue-toastification';

const props = defineProps({
    user: {
        type: Object,
    },
});
console.log(123, props.user)
const page = usePage();
const query = page.props.query;
const type_id = parseInt(query.type_id);
const toast = useToast();
const rules = {
    password: yup.string(),
    password_confirm: value => {
        if (!form.password) return true;
        if (!value) return 'Vui lòng nhập lại mật khẩu';
        return value === form.password || 'Mật khẩu xác nhận không khớp';
    },
    email: yup.string().required('Email không được để trống').email('Email không hợp lệ'),
};
const form = useForm({
    id: props.user.id,
    type_id: props.user.user_type_id,
    email: props.user.email,
    name: props.user.name,
    password: '',
    password_confirm: '',
    phone_number: props.user.tel,
    image_show: props.user.img,
    image_db: props.user.img_db
});
const handleSubmit = () => {
    const formData = new FormData();
    formData.append('img', form.image_db);
    formData.append('name', form.name);
    formData.append('id', form.id);
    formData.append('password', form.password);
    formData.append('email', form.email);
    formData.append('phone_number', form.phone_number);
    axios
        .post(route('users.json.updateMember'), formData)
        .then((response) => {
            if (response.data.status) {
                toast.success('Cập nhật tài khoản thành công');
                setTimeout(function () {
                    location.href = route('apps.dashboard')
                }, 500);
            } else {
                toast.error(response.data.messages.email);
            }
        })
        .catch((error) => {
            toast.error(error);
        });
};
const filterOption = (input, option) => {
    return option.value.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};
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
    const formData = new FormData();
    formData.append('file', file);
    const res = await axios.post('/api/upload-file', formData);
    form.image_name = file.name;
    form.image_show = res.data.s3_file_url;
    form.image_db = res.data.file_url;
};
const removeImage = () => {
    form.image_show = '';
    form.image_db = '';
};
</script>

<template>
    <Head title="Update account" />
    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Update account
            </h2>
        </template>

        <div class="app-page mx-auto flex w-4/5 gap-4 py-4">
            <div class="content-page w-full">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Cài đặt tài khoản</h1>
                <Form @submit="handleSubmit" v-slot="{ errors }" class="w-full">
                    <div class="flex w-full gap-8">
                        <div class="w-full">
                            <div class="flex gap-8 mt-6">
                                <div class="w-1/2 relative">
                                    <label
                                        for="appNameInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                        Email của bạn (*)
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <Field
                                                    name="name"
                                                    :rules="rules.email"
                                                    v-model="form.email"
                                                >
                                                    <a-input
                                                        placeholder="Bạn điền email"
                                                        :allow-clear="true"
                                                        v-model:value="form.email"
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
                                                    :message="form.errors.email"
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
                                        Tên
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <a-input
                                                    placeholder="Bạn điền tên"
                                                    :allow-clear="true"
                                                    v-model:value="form.name"
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
                                        Mật khẩu (*)
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <Field
                                                    name="password"
                                                    :rules="rules.password"
                                                    v-model="form.password"
                                                >
                                                    <a-input-password v-model:value="form.password" placeholder="******"  size="large"/>
                                                </Field>
                                                <ErrorMessage
                                                    class="text-sm text-red-600"
                                                    name="password"
                                                />
                                                <InputError
                                                    class="mt-2"
                                                    :message="form.errors.password"
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
                                        Số điện thoại
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <a-input
                                                    placeholder="Bạn điền số điện thoại"
                                                    :allow-clear="true"
                                                    v-model:value="form.phone_number"
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
                                        Nhập lại mật khẩu (*)
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <Field
                                                    name="password_confirm"
                                                    :rules="rules.password_confirm"
                                                    v-model="form.password_confirm"
                                                >
                                                    <a-input-password v-model:value="form.password_confirm" placeholder="******"  size="large"/>
                                                </Field>
                                                <ErrorMessage
                                                    class="text-sm text-red-600"
                                                    name="password_confirm"
                                                />
                                                <InputError
                                                    class="mt-2"
                                                    :message="form.errors.password_confirm"
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
                                        Ảnh đại diện
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <div
                                                    v-if="form.image_show"
                                                    class="image-container items-center flex w-4/5 gap-4"
                                                >
                                                    <img
                                                        :src="form.image_show"
                                                        @click="triggerFileInput"
                                                        alt="Preview"
                                                        class="image h-[52px] w-[62px] cursor-pointer object-contain"
                                                    />
                                                    <img
                                                        @click="removeImage"
                                                        class="delete-icon w-[30px] h-[30px]"
                                                        src="/images/icon-game-choose-correct/icon-delete.png"
                                                        alt=""
                                                    />
                                                </div>
                                                <div v-else class="relative flex items-center gap-4 mt-2 w-4/5">
                                                    <img
                                                        @click="triggerFileInput"
                                                        :class="`h-[52px] w-[62px] cursor-pointer`"
                                                        src="/images/image_app.png"
                                                        alt=""
                                                    />
                                                    <span class="font-bold cursor-pointer">Chọn tệp</span>
                                                </div>
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
                                        Lưu
                                    </a-button>
                                </a-col>
                            </a-row>
                        </div>
                    </div>
                </Form>
            </div>

        </div>
    </SchoolLayout>
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
</style>
