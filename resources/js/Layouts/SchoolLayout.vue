<script setup>
import { usePage } from '@inertiajs/vue3';
import { useToast } from "vue-toastification";
import MenuNavbar from './MenuNavbar.vue'
import Breadcrumb from '@/Layouts/Breadcrumb.vue';

const props = defineProps({
    breadcrumbs: {},
});

const toast = useToast();
const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <div class="min-h-screen md:flex bg-gray-50">
        <MenuNavbar/>
        <div class="flex-1 min-w-0 flex flex-col">
            <Breadcrumb :breadcrumbs="props.breadcrumbs" />
            <!-- Page Content -->
            <main class="flex-1 bg-white">
                <slot />
            </main>
        </div>
    </div>
</template>
<style lang="scss">
.custom-bg {
    background-color: #cccccc;
}
.hide-class {
    opacity: 30%;
}
.ant-input, .ant-input-affix-wrapper, .ant-input-textarea, .ant-select-selector {
    border-width: 1px !important; /* Đảm bảo ghi đè CSS của Ant Design */
    border-color: black !important;
}
.ant-input-textarea-show-count {
    border: none !important;
    padding: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
}
/* Tùy biến toàn bộ khung date picker */
.ant-picker {
    border-width: 1px !important;
    border-color: black !important;
    border-radius: 4px !important;
}

/* Tùy biến input bên trong date picker */
.ant-picker-input > input {
    color: black !important;
}

/* Tùy biến hover hoặc focus nếu cần */
.ant-picker:hover,
.ant-picker-focused {
    border-color: #1890ff !important; /* hoặc màu bạn muốn */
    box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2) !important;
}
</style>
