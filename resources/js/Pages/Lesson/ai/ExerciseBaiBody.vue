<script setup>
import { ref } from 'vue';
import Icon from '@/Components/AiLessonIcon.vue';
import QCard from './QCard.vue';
import { buildBaiQuestions } from './data.js';

const props = defineProps({
  mix: { type: Object, required: true },
});

const salt = ref(0);
const qs = ref(buildBaiQuestions(props.mix));
const regenAll = ref(false);

const doRegenAll = () => {
  regenAll.value = true;
  setTimeout(() => {
    salt.value++;
    qs.value = buildBaiQuestions(props.mix);
    regenAll.value = false;
  }, 1300);
};
const delQ = (uid) => { qs.value = qs.value.filter((q) => q.uid !== uid); };
</script>

<template>
  <div>
    <div class="bai-toolbar">
      <div class="bai-mixline">
        <span class="muted">{{ qs.length }} câu hỏi:</span>
        <span class="mix-chip" :style="{ background: '#e8f7ee', color: '#15803d' }">{{ mix['Dễ'] }} Dễ</span>
        <span class="mix-chip" :style="{ background: '#fdf3e0', color: '#b45309' }">{{ mix['Trung bình'] }} TB</span>
        <span class="mix-chip" :style="{ background: '#fde8e6', color: '#c2410c' }">{{ mix['Khó'] }} Khó</span>
      </div>
      <button class="btn btn-regenall" :disabled="regenAll" @click="doRegenAll">
        <template v-if="regenAll"><span class="spin"></span>Đang tạo lại…</template>
        <template v-else><Icon name="sparkles" :size="14" fill />Tạo lại cả bài</template>
      </button>
    </div>
    <p class="regen-hint"><Icon name="info" :size="14" />Chưa ưng câu nào? Bấm <b>Tạo lại</b> để AI sinh lại riêng câu đó, hoặc <b>Sửa</b> để chỉnh tay.</p>
    <div class="q-list" :class="{ dimmed: regenAll }" style="margin-top: 6px">
      <QCard v-for="(q, i) in qs" :key="q.uid + '-' + salt" :q="q" :n="i + 1" @delete="delQ(q.uid)" />
    </div>
  </div>
</template>
