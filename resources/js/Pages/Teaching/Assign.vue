<script setup>
import { Head } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import ClassPicker from "@/Components/ClassPicker.vue";

defineProps({ appId: { type: [String, Number], default: '' } });

const breadcrumbs = [
    { title: 'Giao bài', url: '' },
];
</script>

<template>
    <Head title="Giao bài" />
    <SchoolLayout :breadcrumbs="breadcrumbs">
        <ClassPicker mode="assign" :app-id="appId" />
    </SchoolLayout>
</template>
