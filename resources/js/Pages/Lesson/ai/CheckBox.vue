<script setup>
import Icon from '@/Components/AiLessonIcon.vue';
defineProps({ on: Boolean, indet: Boolean, size: { type: Number, default: 18 } });
</script>

<template>
  <span class="checkbox" :class="{ checked: on, indet }">
    <Icon v-if="on" name="check" :size="size - 5" :stroke="3" />
    <span v-else-if="indet" :style="{ width: '9px', height: '2px', background: '#fff', borderRadius: '2px' }"></span>
  </span>
</template>
