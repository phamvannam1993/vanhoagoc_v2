<script setup>
import Icon from '@/Components/AiLessonIcon.vue';
const emit = defineEmits(['close']);

const items = [
  { t: 'Sách nói', date: '08/06/2026 14:44', icon: 'headphones', color: '#7c3aed' },
  { t: 'Bài đọc', date: '08/06/2026 14:42', icon: 'reader', color: '#2563eb' },
];
const onOverlay = (e) => { if (e.target === e.currentTarget) emit('close'); };
</script>

<template>
  <div class="overlay" @mousedown="onOverlay">
    <div class="hist-modal">
      <div class="hist-head">
        <h3>Lịch sử bài giảng</h3>
        <button class="wz-close" @click="emit('close')"><Icon name="x" :size="18" /></button>
      </div>
      <div class="hist-body">
        <div class="hint-line" style="margin: 0 0 4px; color: #1d63c9; background: #eef5ff; border-color: #d3e3fb">
          <Icon name="info" :size="16" />Các bản vừa tạo giờ hiển thị ngay trên trang. Mục này chỉ lưu các bản nháp cũ.
        </div>
        <div v-for="(it, i) in items" :key="i" class="hist-item">
          <span class="hi-ic" :style="{ background: it.color }"><Icon :name="it.icon" :size="18" /></span>
          <div class="hi-main">
            <div class="hi-title">{{ it.t }}<span class="badge badge-draft">Nháp</span></div>
            <div class="hi-date">{{ it.date }}</div>
          </div>
          <a href="#" class="hist-view" @click.prevent>Xem →</a>
        </div>
      </div>
    </div>
  </div>
</template>
