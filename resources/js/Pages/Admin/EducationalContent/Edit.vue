<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';
import MasterLayout from '@/Layouts/MasterLayout.vue';

const toast = useToast();
const page = usePage();
const item = page.props.item;
const form = ref({ id: item.id, name: item.name, code: item.code ?? '' });
const errors = ref({});
const loading = ref(false);

async function submit() {
    loading.value = true;
    errors.value = {};
    try {
        const res = await axios.post(route('admins.educational-contents.json.store'), form.value);
        if (res.data.status) {
            toast.success('Cập nhật thành công');
            router.visit(route('admins.educational-contents.index'));
        } else {
            errors.value = res.data.messages ?? {};
        }
    } catch (e) {
        toast.error('Có lỗi xảy ra');
    }
    loading.value = false;
}
</script>

<template>
    <Head title="Sửa NDGD" />
    <MasterLayout>
        <div class="app-page py-4">
            <div class="content-page mx-auto w-full md:w-6/12">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Sửa Nội dung Giáo dục</h1>
                <div class="mt-6 bg-white rounded-lg p-6 shadow">
                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Tên NDGD <span class="text-red-500">*</span></label>
                        <a-input v-model:value="form.name" size="large" placeholder="Nhập tên nội dung giáo dục" />
                        <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
                    </div>
                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Mã (ID)</label>
                        <a-input v-model:value="form.code" size="large" placeholder="Nhập mã" />
                        <p v-if="errors.code" class="text-red-500 text-sm mt-1">{{ errors.code }}</p>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <Link :href="route('admins.educational-contents.index')">
                            <a-button size="large">Quay lại</a-button>
                        </Link>
                        <a-button type="primary" size="large" :loading="loading" @click="submit">Lưu</a-button>
                    </div>
                </div>
            </div>
        </div>
    </MasterLayout>
</template>
