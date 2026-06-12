<script setup>
import { defineProps } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToast } from "vue-toastification";
import Breadcrumb from '@/Layouts/Breadcrumb.vue';
import MenuNavbar from './MenuNavbar.vue'

const toast = useToast();
const page = usePage();
const user = page.props.auth.user;
const props = defineProps({
    breadcrumbs: {},
});
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
</style>
