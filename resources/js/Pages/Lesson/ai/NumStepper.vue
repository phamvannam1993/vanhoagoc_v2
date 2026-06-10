<script setup>
const props = defineProps({
  value: { type: Number, default: 0 },
  min: { type: Number, default: 0 },
  max: { type: Number, default: 20 },
});
const emit = defineEmits(['update:value']);
const set = (v) => emit('update:value', Math.min(props.max, Math.max(props.min, v)));
const onInput = (e) => set(parseInt(e.target.value.replace(/\D/g, '')) || 0);
</script>

<template>
  <div class="stepper-num">
    <button type="button" @click="set(value - 1)">−</button>
    <input :value="value" @input="onInput" />
    <button type="button" @click="set(value + 1)">+</button>
  </div>
</template>
