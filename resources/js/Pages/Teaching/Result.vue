<script setup>
import { Head } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import ClassPicker from "@/Components/ClassPicker.vue";

defineProps({ appId: { type: [String, Number], default: '' } });

const breadcrumbs = [
    { title: 'Kết quả', url: '' },
];
</script>

<template>
    <Head title="Kết quả" />
    <SchoolLayout :breadcrumbs="breadcrumbs">
        <ClassPicker mode="result" :app-id="appId" />
    </SchoolLayout>
</template>
