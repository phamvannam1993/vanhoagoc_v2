<script setup>
import { usePage, Link } from '@inertiajs/vue3';
import { useToast } from "vue-toastification";
import MenuNavbar from './MenuNavbar.vue'
import Breadcrumb from '@/Layouts/Breadcrumb.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const props = defineProps({
    breadcrumbs: {},
});

const toast = useToast();
const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <div class="min-h-screen flex flex-col">
        <!-- Top Navbar -->
        <nav class="bg-[#041C38] border-b border-gray-200 sticky top-0 z-50 w-full">
            <div class="flex h-16 justify-between items-center px-4 sm:px-6 lg:px-8">
                    <!-- Left: Tool Editor -->
                    <Link :href="route('apps.dashboard')" class="flex items-center gap-3 shrink-0">
                        <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/>
                            <path d="M20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                        </svg>
                        <span class="text-white font-bold text-sm">Tool Editor</span>
                    </Link>

                    <!-- Right: User Dropdown -->
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button class="flex items-center gap-2 text-white hover:opacity-80 transition">
                                        <img
                                            :src="user.avatar ?? '/images/icon-avatar-default.png'"
                                            alt="avatar"
                                            class="h-8 w-8 rounded-full border border-white object-cover"
                                        />
                                        <span class="text-sm font-medium">{{ user.name }}</span>
                                    </button>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">
                                        Hồ sơ
                                    </DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">
                                        Đăng xuất
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
            </div>
        </nav>

        <!-- Main Content with Sidebar -->
        <div class="flex flex-1 bg-gray-50">
            <MenuNavbar/>
            <div class="flex-1 min-w-0 flex flex-col">
                <Breadcrumb :breadcrumbs="props.breadcrumbs" />
                <!-- Page Content -->
                <main class="flex-1 bg-white">
                    <slot />
                </main>
            </div>
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
