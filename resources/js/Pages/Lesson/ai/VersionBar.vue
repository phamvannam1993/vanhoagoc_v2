<script setup>
import { ref, computed } from 'vue';
import Icon from '@/Components/AiLessonIcon.vue';

const props = defineProps({
  versions: { type: Array, default: () => [] },
  activeIdx: { type: Number, default: 0 },
  preview: { type: Function, default: () => '' },
  kind: { type: String, default: 'restore' },
});
const emit = defineEmits(['pick']);
const open = ref(false);
const active = computed(() => props.versions[props.activeIdx] || {});
</script>

<template>
  <div class="ver-wrap">
    <div class="ver-bar">
      <span class="ver-cur">
        <Icon name="layers" :size="14" />Phiên bản {{ active.n }}{{ activeIdx === 0 ? ' (mới nhất)' : '' }}
        <span class="ver-time">· {{ active.time }}</span>
      </span>
      <button v-if="versions.length > 1" class="ver-toggle" :class="{ on: open }" @click="open = !open">
        <Icon name="history" :size="14" />Lịch sử tạo ({{ versions.length }})<Icon name="chevDown" :size="14" />
      </button>
    </div>
    <div v-if="open && versions.length > 1" class="ver-list">
      <div
        v-for="(v, i) in versions"
        :key="v.n"
        class="ver-item"
        :class="{ active: i === activeIdx }"
        @click="emit('pick', i)"
      >
        <span class="vi-dot"><Icon v-if="i === activeIdx" name="check" :size="13" :stroke="3" /><template v-else>{{ v.n }}</template></span>
        <div class="vi-main">
          <div class="vi-title">Phiên bản {{ v.n }}{{ i === 0 ? ' · Mới nhất' : '' }}<span class="vi-time">{{ v.time }}</span></div>
          <div class="vi-prev">{{ preview(v) }}</div>
        </div>
        <span v-if="i === activeIdx" class="vi-using">Đang dùng</span>
        <button v-else class="vi-use" @click.stop="emit('pick', i)">{{ kind === 'restore' ? 'Khôi phục' : 'Dùng bản này' }}</button>
      </div>
    </div>
  </div>
</template>
