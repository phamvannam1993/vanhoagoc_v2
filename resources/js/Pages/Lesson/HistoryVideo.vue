<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";

const page = usePage();
const query = page.props.query;
const app_id = query.app_id;
const book_id = query.book_id;
const week_id = query.week_id;
const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head title="History Video" />

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                App
            </h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-4/5 sm:px-6 lg:px-8">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">
                    Video đã tạo
                </h1>
                <div class="grid grid-cols-3 gap-4">
                    <!-- Cột 1 -->
                    <div class="p-6">
                        <iframe
                            src="https://www.youtube.com/embed/o5Y3GYu4ldk"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            height="232px"
                        >
                        </iframe>
                    </div>
                    <div class="p-6">
                        <iframe
                            src="https://www.youtube.com/embed/o5Y3GYu4ldk"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            height="232px"
                        >
                        </iframe>
                    </div>
                    <div class="p-6">
                        <iframe
                            src="https://www.youtube.com/embed/o5Y3GYu4ldk"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            height="232px"
                        >
                        </iframe>
                    </div>
                    <div class="p-6">
                        <iframe
                            src="https://www.youtube.com/embed/o5Y3GYu4ldk"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            height="232px"
                        >
                        </iframe>
                    </div>
                    <div class="p-6">
                        <iframe
                            src="https://www.youtube.com/embed/o5Y3GYu4ldk"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            height="232px"
                        >
                        </iframe>
                    </div>
                    <div class="p-6">
                        <iframe
                            src="https://www.youtube.com/embed/o5Y3GYu4ldk"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            height="232px"
                        >
                        </iframe>
                    </div>
                    <div class="p-6">
                        <iframe
                            src="https://www.youtube.com/embed/o5Y3GYu4ldk"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            height="232px"
                        >
                        </iframe>
                    </div>
                </div>
                <div class="mt-4">
                    <img
                        @click="goBack"
                        class="h-[34px] cursor-pointer"
                        src="/images/icon-button-back.png"
                        alt=""
                    />
                </div>
            </div>
        </div>
    </SchoolLayout>
</template>
