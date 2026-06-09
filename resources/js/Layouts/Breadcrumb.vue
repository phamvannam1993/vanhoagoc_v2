<script setup>
    import { ref, defineProps } from 'vue';
    const props = defineProps({
        breadcrumbs: {},
    });
</script>
<template>
    <div v-if="props.breadcrumbs" class="content-page mx-auto w-full md:w-10/12 py-2 px-1 md:mt-2">
        <template v-for="(item, index) in props.breadcrumbs">
            <span v-if="item.title">
                <a class="underline" :href="item.url??'/'" >
                    {{ item.title }}
                </a>
                <span v-if="index < props.breadcrumbs.length-1"> / </span>
            </span>
        </template>
    </div>
</template>
