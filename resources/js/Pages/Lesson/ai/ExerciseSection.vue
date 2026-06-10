<script setup>
import { ref, computed } from 'vue';
import Icon from '@/Components/AiLessonIcon.vue';
import LvlBadge from './LvlBadge.vue';
import ExerciseBaiBody from './ExerciseBaiBody.vue';
import { mixStr, LEVEL_STYLE, SUG_NHOM } from './data.js';

const props = defineProps({
  bais: { type: Array, default: () => [] },
  color: { type: String, default: '#0284c7' },
  counts: { type: Object, default: () => ({}) },
  totalBai: { type: Number, default: 0 },
});
const emit = defineEmits(['assign']);

const giaoMap = computed(() => [
  { grp: 'Nhóm yếu', level: 'Dễ', baiName: 'Bài dễ', n: props.counts['Dễ'] || 0 },
  { grp: 'Nhóm khá', level: 'Trung bình', baiName: 'Bài trung bình', n: props.counts['Trung bình'] || 0 },
  { grp: 'Nhóm giỏi', level: 'Khó', baiName: 'Bài khó', n: props.counts['Khó'] || 0 },
]);
const lv = (level) => LEVEL_STYLE[level] || LEVEL_STYLE['Dễ'];

const open = ref(0);
const toggle = (i) => { open.value = open.value === i ? -1 : i; };
</script>

<template>
  <div>
    <p class="sec-sub">Các bài tập tạo theo mức độ, giao cho từng nhóm học sinh phù hợp. Mỗi bài 10 câu, có thể sửa / tạo lại bằng AI trước khi giao.</p>

    <div class="map-note">
      <Icon name="info" :size="15" />
      <span>Tự động giao theo mức độ của bài:</span>
      <span class="map-pair"><LvlBadge level="Dễ" /><Icon name="chevRight" :size="13" /><b>Nhóm yếu</b></span>
      <span class="map-pair"><LvlBadge level="Trung bình" /><Icon name="chevRight" :size="13" /><b>Nhóm khá</b></span>
      <span class="map-pair"><LvlBadge level="Khó" /><Icon name="chevRight" :size="13" /><b>Nhóm giỏi</b></span>
    </div>

    <div class="giao-grid">
      <div v-for="g in giaoMap" :key="g.grp" class="giao-card" :style="{ borderColor: lv(g.level).bg }">
        <div class="gc-grp"><span class="gc-grp-ic" :style="{ background: lv(g.level).bg, color: lv(g.level).color }"><Icon name="users" :size="16" /></span>{{ g.grp }}</div>
        <div class="gc-arrow"><Icon name="chevDown" :size="16" /></div>
        <div class="gc-bai" :style="{ background: lv(g.level).bg, color: lv(g.level).color }"><LvlBadge :level="g.level" />{{ g.baiName }}</div>
        <div class="gc-stat"><b>{{ g.n }}</b> bài · <b>{{ g.n * 10 }}</b> câu</div>
      </div>
    </div>

    <div class="bai-list">
      <div v-for="(b, i) in bais" :key="i" class="bai-item" :class="{ open: open === i }">
        <button class="bai-head" @click="toggle(i)">
          <span class="bai-idx" :style="{ background: color }">{{ i + 1 }}</span>
          <div class="bai-head-main">
            <span class="bai-name">Bài {{ i + 1 }}</span>
            <span class="bai-sub">10 câu · {{ mixStr(b.mix) }} · giao {{ SUG_NHOM[b.level] }}</span>
          </div>
          <LvlBadge :level="b.level" />
          <span class="acc-caret"><Icon name="chevDown" :size="17" /></span>
        </button>
        <div v-if="open === i" class="bai-body">
          <ExerciseBaiBody :mix="b.mix" />
        </div>
      </div>
    </div>

    <div class="acc-actions">
      <span class="foot-info"><b>{{ totalBai }}</b> bài giao cho <b>3</b> nhóm học sinh</span>
      <button class="btn btn-assign" @click="emit('assign')"><Icon name="send" :size="15" />Giao bài cho học sinh</button>
    </div>
  </div>
</template>
