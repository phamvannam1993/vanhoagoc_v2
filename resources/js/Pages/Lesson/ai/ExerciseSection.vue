<script setup>
import { ref } from 'vue';
import Icon from '@/Components/AiLessonIcon.vue';
import LvlBadge from './LvlBadge.vue';
import { buildBaiQuestions, mixStr, KIND_LABEL } from './data.js';

const props = defineProps({
  bais: { type: Array, default: () => [] },
  color: { type: String, default: '#16a34a' },
  totalBai: { type: Number, default: 0 },
  totalCau: { type: Number, default: 0 },
});
const emit = defineEmits(['assign']);

const open = ref(0);
const toggle = (i) => { open.value = open.value === i ? -1 : i; };
const letter = (i) => String.fromCharCode(65 + i);
</script>

<template>
  <div>
    <div class="map-note">
      <Icon name="info" :size="15" />
      <span>Giao tự động theo mức độ của bài:</span>
      <span class="map-pair"><LvlBadge level="Dễ" /><Icon name="chevRight" :size="13" /><b>Nhóm yếu</b></span>
      <span class="map-pair"><LvlBadge level="Trung bình" /><Icon name="chevRight" :size="13" /><b>Nhóm khá</b></span>
      <span class="map-pair"><LvlBadge level="Khó" /><Icon name="chevRight" :size="13" /><b>Nhóm giỏi</b></span>
    </div>

    <div class="bai-list">
      <div v-for="(b, i) in bais" :key="i" class="bai-item" :class="{ open: open === i }">
        <button class="bai-head" @click="toggle(i)">
          <span class="bai-idx" :style="{ background: color }">{{ i + 1 }}</span>
          <div class="bai-head-main">
            <span class="bai-name">Bài tập {{ i + 1 }}</span>
            <span class="bai-sub">10 câu · {{ mixStr(b.mix) }}</span>
          </div>
          <LvlBadge :level="b.level" />
          <span class="acc-caret"><Icon name="chevDown" :size="17" /></span>
        </button>
        <div v-if="open === i" class="bai-body">
          <div class="bai-mixline">
            <span class="muted">10 câu hỏi:</span>
            <span class="mix-chip" :style="{ background: '#e8f7ee', color: '#15803d' }">{{ b.mix['Dễ'] }} Dễ</span>
            <span class="mix-chip" :style="{ background: '#fdf3e0', color: '#b45309' }">{{ b.mix['Trung bình'] }} TB</span>
            <span class="mix-chip" :style="{ background: '#fde8e6', color: '#c2410c' }">{{ b.mix['Khó'] }} Khó</span>
          </div>
          <div class="q-list" style="margin-top: 12px">
            <div v-for="(q, n) in buildBaiQuestions(b.mix)" :key="q.uid" class="q-card inc">
              <div class="q-head">
                <span class="q-num">{{ n + 1 }}</span>
                <span class="q-kind">{{ KIND_LABEL[q.kind] }}</span>
                <div class="q-text">{{ q.text }}</div>
                <LvlBadge :level="q.level" />
              </div>
              <div v-if="q.kind === 'chon'" class="q-opts">
                <div v-for="(o, oi) in q.options" :key="oi" class="q-opt" :class="{ correct: o.correct }">
                  <span class="oi">{{ letter(oi) }}</span>{{ o.t }}
                  <span v-if="o.correct" class="ans-mark"><Icon name="check" :size="13" :stroke="3" />Đáp án</span>
                </div>
              </div>
              <div v-else-if="q.kind === 'sapxep'" class="q-seq">
                <div v-for="(s, si) in q.seq" :key="si" class="si"><span class="ord">{{ si + 1 }}</span>{{ s }}</div>
              </div>
              <div v-else-if="q.kind === 'noi'" class="q-match">
                <template v-for="(p, pi) in q.pairs" :key="pi">
                  <div class="mcell">{{ p[0] }}</div>
                  <div class="mline"><Icon name="link2" :size="16" /></div>
                  <div class="mcell">{{ p[1] }}</div>
                </template>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="acc-actions">
      <span class="foot-info"><b>{{ totalBai }}</b> bài tập · <b>{{ totalCau }}</b> câu hỏi</span>
      <button class="btn btn-assign" @click="emit('assign')"><Icon name="send" :size="15" />Giao bài cho học sinh</button>
    </div>
  </div>
</template>
