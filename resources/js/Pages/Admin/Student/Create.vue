<script setup>
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { ErrorMessage, Field, Form } from "vee-validate";
import { defineProps, ref } from "vue";
import InputError from "@/Components/InputError.vue";
import * as yup from "yup";
import { useToast } from "vue-toastification";
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import dayjs from "dayjs";

const props = defineProps({
    listApp: {
        type: Array,
    },
});
const options = props.listApp.map((v) => {
    return { value: v.id, label: v.name };
});
const optionsClass = ref([]);
const handleChangeApp = async (value) => {
    form.class_id = null;
    const params = {
      app_id: value
    };
    const res = await axios.get(route('admins.students.json.getClassByApp', params));
    if (res.data.status) {
        optionsClass.value = res.data.data.map((v) => {
          return { value: v.id, label: v.name }
      });
    }
};
const page = usePage();
const query = page.props.query;
const type_id = parseInt(query.type_id);
const class_id = query.class_id;
const toast = useToast();
const form = useForm({
    email: '',
    username: '',
    birthday: null,
    address: '',
    name: '',
    password: '',
    password_confirm: '',
    tel: '',
    app_id: null,
    class_id: null
});

const rules = {
    password: value => !!value || 'Password không được để trống',
    password_confirm: value => {
        if (!form.password) return true;
        if (!value) return 'Vui lòng nhập lại mật khẩu';
        return value === form.password || 'Mật khẩu xác nhận không khớp';
    },
    email: yup.string().required('Email không được để trống').email('Email không hợp lệ'),
    username: yup.string().required('Tên tài khoản không được để trống'),
    app_id: yup.string().required('App không được để trống'),
};
const handleSubmit = () => {
    const payload = {
        ...form,
        birthday: form.birthday ? dayjs(form.birthday).format('YYYY-MM-DD') : null
    }
    axios
        .post(route('admins.students.json.store'), payload)
        .then((response) => {
            if (response.data.status) {
                toast.success('Tạo tài khoản Nhân viên/Học sinh thành công');
                setTimeout(function () {
                    if (class_id) {
                        location.href = route('admins.students.index', { class_id: class_id });
                    } else {
                        location.href = route('admins.students.index');
                    }
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
</script>

<template>
    <Head title="New Student" />
    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                New Student
            </h2>
        </template>

        <div class="app-page mx-auto flex w-4/5 gap-4 py-4">
            <div class="content-page w-full">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Thêm Nhân viên/Học sinh</h1>
                <Form @submit="handleSubmit" v-slot="{ errors }" class="w-full">
                    <div class="flex w-full gap-8">
                        <div class="w-full">
                            <div class="flex gap-8 mt-6">
                                <div class="w-1/2 relative">
                                    <label
                                        for="appNameInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                        Tên tài khoản (*)
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <Field
                                                    name="username"
                                                    :rules="rules.username"
                                                    v-model="form.username"
                                                >
                                                    <a-input
                                                        placeholder="Bạn điền tên tài khoản"
                                                        :allow-clear="true"
                                                        v-model:value="form.username"
                                                        size="large"
                                                    >
                                                    </a-input>
                                                </Field>
                                                <ErrorMessage
                                                    class="text-sm text-red-600"
                                                    name="username"
                                                />
                                                <InputError
                                                    class="mt-2"
                                                    :message="form.errors.username"
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
                                        Ngày sinh
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <a-date-picker size="large" style="width: 100%" v-model:value="form.birthday" placeholder="Chọn ngày" format="DD/MM/YYYY"/>
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
                                        Địa chỉ
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <a-input
                                                    placeholder="Bạn điền địa chỉ"
                                                    :allow-clear="true"
                                                    v-model:value="form.address"
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
                            </div>
                            <div class="flex gap-8 mt-6">
                                <div class="w-1/2 relative">
                                    <label
                                        for="appNameInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                        Họ và tên
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
                                <div class="w-1/2 relative">
                                    <label
                                        for="appNameInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                        ID App
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <Field
                                                    name="app_id"
                                                    :rules="rules.app_id"
                                                    v-model="form.app_id"
                                                >
                                                    <a-select
                                                        class="input-search w-full"
                                                        v-model:value="form.app_id"
                                                        show-search
                                                        placeholder="Đơn vị"
                                                        size="large"
                                                        :options="options"
                                                        :filter-option="filterOption"
                                                        @change="handleChangeApp"
                                                    ></a-select>
                                                </Field>
                                                <ErrorMessage
                                                    class="text-sm text-red-600"
                                                    name="app_id"
                                                />
                                                <InputError
                                                    class="mt-2"
                                                    :message="form.errors.app_id"
                                                />
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
                                        Số điện thoại
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <a-input
                                                    placeholder="Bạn nhập số điện thoại"
                                                    :allow-clear="true"
                                                    v-model:value="form.tel"
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
                                        ID Lớp/Phòng ban trong App
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <a-select
                                                    class="input-search w-full"
                                                    v-model:value="form.class_id"
                                                    show-search
                                                    placeholder="Lớp/Phòng ban"
                                                    size="large"
                                                    :options="optionsClass"
                                                    :filter-option="filterOption"
                                                ></a-select>
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
                                        Tạo
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
