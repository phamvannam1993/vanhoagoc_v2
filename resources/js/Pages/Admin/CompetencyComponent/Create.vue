<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";

const toast = useToast();
const page = usePage();
const competency = page.props.competency;
const form = ref({ competency_id: competency.id, name: '', code: '' });
const errors = ref({});
const loading = ref(false);

async function submit() {
    loading.value = true;
    errors.value = {};
    try {
        const res = await axios.post(route('admins.competency-components.json.store'), form.value);
        if (res.data.status) {
            toast.success('Thêm mới thành công');
            router.visit(route('admins.competency-components.index', { competency_id: competency.id }));
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
    <Head title="Thêm thành phần NL" />
    <SchoolLayout>
        <div class="app-page py-4">
            <div class="content-page mx-auto w-full md:w-6/12">
                <div class="flex items-center gap-3 mb-2">
                    <Link :href="route('admins.competencies.index')" class="text-[#2C75E3] hover:underline">PCNL</Link>
                    <span>›</span>
                    <Link :href="route('admins.competency-components.index', { competency_id: competency.id })" class="text-[#2C75E3] hover:underline">{{ competency.name }}</Link>
                </div>
                <h1 class="text-[28px] font-bold text-[#2C75E3]">Thêm Thành phần Năng lực</h1>
                <div class="mt-6 bg-white rounded-lg p-6 shadow">
                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Tên thành phần <span class="text-red-500">*</span></label>
                        <a-input v-model:value="form.name" size="large" placeholder="Nhập tên thành phần năng lực" />
                        <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
                    </div>
                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Mã (ID)</label>
                        <a-input v-model:value="form.code" size="large" placeholder="Nhập mã" />
                        <p v-if="errors.code" class="text-red-500 text-sm mt-1">{{ errors.code }}</p>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <Link :href="route('admins.competency-components.index', { competency_id: competency.id })">
                            <a-button size="large">Quay lại</a-button>
                        </Link>
                        <a-button type="primary" size="large" :loading="loading" @click="submit">Lưu</a-button>
                    </div>
                </div>
            </div>
        </div>
    </SchoolLayout>
</template>
