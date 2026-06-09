<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import MasterLayout from '@/Layouts/MasterLayout.vue';
import { ErrorMessage, Field, Form } from "vee-validate";
import { defineProps, ref } from "vue";
import InputError from "@/Components/InputError.vue";
import * as yup from "yup";
import { useToast } from 'vue-toastification';

const props = defineProps({
    types: {
        type: Object,
    },
});
const page = usePage();
const query = page.props.query;
const type_id = parseInt(query.type_id);
const toast = useToast();
const form = useForm({
    quantity: 0,
    price:0
});
const handleSubmit = () => {
    axios
        .post(route('admins.active-codes.save'), form)
        .then((response) => {
            if (response.data.success) {
                toast.success('Tạo mã kích hoạt thành công');
                setTimeout(function () {
                    location.href = route('admins.active-codes.index')
                }, 500);
            } else {
                toast.error('Có lỗi xảy ra');
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
    <Head title="New member" />
    <MasterLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                New member
            </h2>
        </template>

        <div class="app-page mx-auto flex w-4/5 gap-4 py-4">
            <div class="content-page w-full">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Thêm mã kích hoạt</h1>
                <Form @submit="handleSubmit" v-slot="{ errors }" class="w-full">
                    <div class="flex w-full gap-8">
                        <div class="w-full">
                            <div class="flex gap-8 mt-6">
    
                                <div class="w-1/2 relative">
                                    <label
                                        for="appNameInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                      Giá
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <a-input
                                                    type="number"
                                                    placeholder="Nhập số lượng"
                                                    :allow-clear="true"
                                                    v-model:value="form.price"
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
                                        Số lượng
                                    </label>
                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <a-input
                                                    type="number"
                                                    placeholder="Nhập số lượng"
                                                    :allow-clear="true"
                                                    v-model:value="form.quantity"
                                                    size="large"
                                                >
                                                </a-input>
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
