<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ErrorMessage, Field, Form } from "vee-validate";
import { defineProps, onMounted, ref } from "vue";
import InputError from "@/Components/InputError.vue";
import * as yup from "yup";
import { useToast } from 'vue-toastification';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import dayjs from 'dayjs'

const props = defineProps({
    listApp: {
        type: Array,
    },
    user: {
        type: Object
    }
});
const options = props.listApp.map((v) => {
    return { value: v.id, label: v.name };
});
const dataClass = ref(props.user.teacher_app.map((v) => {
    return { value: v.id, label: v.name }
}));
onMounted(async () => {
    await loadByApp();
});
const page = usePage();
const query = page.props.query;
const type_id = parseInt(query.type_id);
const toast = useToast();
const result = ref(props.user.teacher_app.map(item => item.id));
const form = useForm({
    id: props.user.id,
    email: props.user.email,
    username: props.user.username,
    birthday: props.user.birthday ? dayjs(props.user.birthday) : null,
    address: props.user.address,
    name: props.user.name,
    password: '',
    password_confirm: '',
    tel: props.user.tel,
    app_id: props.user.teacher_app[0]?.app.id,
    class_id: ref(result.value),
    course_id: ref(props.user.teacher_courses?.map(item => item.id) ?? []),
});
const optionsClass = ref([]);
const optionsCourse = ref([]);
const loadByApp = async () => {
    const appId = props.user.teacher_app[0]?.app.id;
    if (!appId) return;
    const params = { app_id: appId };
    const [resClass, resCourse] = await Promise.all([
        axios.get(route('admins.teachers.json.getClassByApp', params)),
        axios.get(route('admins.teachers.json.getCourseByApp', params)),
    ]);
    if (resClass.data.status) {
        optionsClass.value = resClass.data.data.map((v) => ({ value: v.id, label: v.name }));
    }
    if (resCourse.data.status) {
        optionsCourse.value = resCourse.data.data.map((v) => ({ value: v.id, label: v.name }));
    }
};
const handleChangeApp = async (value) => {
    form.class_id = ref([]);
    form.course_id = ref([]);
    const params = { app_id: value };
    const [resClass, resCourse] = await Promise.all([
        axios.get(route('admins.teachers.json.getClassByApp', params)),
        axios.get(route('admins.teachers.json.getCourseByApp', params)),
    ]);
    if (resClass.data.status) {
        optionsClass.value = resClass.data.data.map((v) => ({ value: v.id, label: v.name }));
    }
    if (resCourse.data.status) {
        optionsCourse.value = resCourse.data.data.map((v) => ({ value: v.id, label: v.name }));
    }
};
const rules = {
    password: yup.string(),
    password_confirm: value => {
        if (!form.password) return true;
        if (!value) return 'Vui lòng nhập lại mật khẩu';
        return value === form.password || 'Mật khẩu xác nhận không khớp';
    },
    email: yup.string().required('Email không được để trống').email('Email không hợp lệ'),
    username: yup.string().required('Tên tài khoản không được để trống'),
    app_id: yup.string().required('App không được để trống'),
    class_id: value => {
        return Array.isArray(value) && value.length > 0 || 'Vui lòng chọn ít nhất 1 Phòng ban/Lớp';
    },
};
const handleSubmit = () => {
    const payload = {
        id: form.id,
        email: form.email,
        username: form.username,
        name: form.name || '',
        tel: form.tel || '',
        address: form.address || '',
        password: form.password,
        password_confirm: form.password_confirm,
        app_id: form.app_id,
        class_id: form.class_id,
        course_id: form.course_id,
        birthday: form.birthday ? dayjs(form.birthday).format('YYYY-MM-DD') : null,
    }
    axios
        .post(route('admins.teachers.json.store'), payload)
        .then((response) => {
            if (response.data.status) {
                toast.success('Cập nhật tài khoản Quản lý/Giáo viên thành công');
                setTimeout(function () {
                    location.href = route('admins.teachers.index')
                }, 500);
            } else {
                toast.error(response.data?.messages?.email ?? 'Có lỗi xảy ra, vui lòng thử lại.');
            }
        })
        .catch((error) => {
            toast.error('Có lỗi xảy ra, vui lòng thử lại.');
            console.error(error);
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
    <Head title="Edit teacher" />
    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit teacher
            </h2>
        </template>

        <div class="app-page mx-auto flex w-4/5 gap-4 py-4">
            <div class="content-page w-full">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Sửa Quản lý/Giáo viên</h1>
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
                                        Phân quyền quản lý Lớp/Phòng ban trong App
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <Field
                                                    name="class_id"
                                                    :rules="rules.class_id"
                                                    v-model="form.class_id"
                                                >
                                                    <a-select
                                                        class="input-search w-full"
                                                        v-model:value="form.class_id"
                                                        mode="multiple"
                                                        show-search
                                                        placeholder="Lớp/Phòng ban"
                                                        size="large"
                                                        :options="optionsClass"
                                                        :filter-option="filterOption"
                                                    ></a-select>
                                                </Field>
                                                <ErrorMessage
                                                    class="text-sm text-red-600"
                                                    name="class_id"
                                                />
                                                <InputError
                                                    class="mt-2"
                                                    :message="form.errors.class_id"
                                                />
                                            </a-col>
                                        </a-row>
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-8 mt-6">
                                <div class="w-1/2 relative">
                                    <label
                                        for="courseInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                        Phân quyền Môn học/Khóa học trong App
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <a-select
                                                    class="input-search w-full"
                                                    v-model:value="form.course_id"
                                                    mode="multiple"
                                                    show-search
                                                    placeholder="Chọn môn học/khóa học"
                                                    size="large"
                                                    :options="optionsCourse"
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
