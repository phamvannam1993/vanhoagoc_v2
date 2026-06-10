<script setup>
import { computed } from 'vue';
import { LEVEL_STYLE } from './data.js';
const props = defineProps({ level: { type: String, default: 'Dễ' } });
const s = computed(() => LEVEL_STYLE[props.level] || LEVEL_STYLE['Dễ']);
</script>

<template>
  <span class="lvl-badge" :style="{ background: s.bg, color: s.color }">{{ level }}</span>
</template>
