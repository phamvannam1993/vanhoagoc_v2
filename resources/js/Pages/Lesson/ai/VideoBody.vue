<script setup>
import { ref, computed } from 'vue';
import Icon from '@/Components/AiLessonIcon.vue';
import VersionBar from './VersionBar.vue';
import { LESSON, VSTYLES, VDURATIONS, nowStr } from './data.js';

const props = defineProps({ vstyle: { type: String, default: 'Hoạt hình minh hoạ' } });
const emit = defineEmits(['toast']);

const versions = ref([{ n: 1, time: nowStr(), vstyle: props.vstyle || 'Hoạt hình minh hoạ', duration: VDURATIONS[0] }]);
const activeIdx = ref(0);
const playing = ref(false);
const cur = computed(() => versions.value[activeIdx.value]);

const pick = (i) => { activeIdx.value = i; playing.value = false; };
const regen = () => {
  const vstyle2 = VSTYLES[versions.value.length % VSTYLES.length];
  const duration = VDURATIONS[versions.value.length % VDURATIONS.length];
  versions.value = [{ n: versions.value[0].n + 1, time: nowStr(), vstyle: vstyle2, duration }, ...versions.value];
  activeIdx.value = 0;
  playing.value = false;
  emit('toast', '🎬 Đã tạo lại video');
};
const previewFn = (v) => `${v.vstyle} · ${v.duration}`;
</script>

<template>
  <div>
    <p class="sec-sub">Video bài giảng do AI dựng tự động từ nội dung bài, phong cách <b>{{ cur.vstyle }}</b>. Mỗi lần tạo lại được lưu thành phiên bản riêng.</p>
    <VersionBar :versions="versions" :active-idx="activeIdx" :preview="previewFn" kind="restore" @pick="pick" />
    <div class="video-frame">
      <div class="vf-stage">
        <div class="vf-scene">
          <span class="vf-badge"><Icon name="sparkles" :size="12" fill />AI · {{ cur.vstyle }}</span>
          <div class="vf-art">
            <span class="vf-sun"></span>
            <span class="vf-hill"></span><span class="vf-hill h2"></span>
            <span class="vf-char"></span>
            <span class="vf-cap">“{{ LESSON.title }}”</span>
          </div>
        </div>
        <button class="vf-play" @click="playing = !playing"><Icon :name="playing ? 'pause' : 'play'" :size="26" fill /></button>
      </div>
      <div class="vf-bar">
        <button class="vf-mini" @click="playing = !playing"><Icon :name="playing ? 'pause' : 'play'" :size="15" fill /></button>
        <div class="vf-track"><i :style="{ width: playing ? '38%' : '0%' }"></i></div>
        <span class="vf-time">{{ playing ? '0:41' : '0:00' }} / {{ cur.duration }}</span>
        <button class="vf-mini"><Icon name="volume" :size="15" /></button>
      </div>
    </div>
    <div class="acc-actions">
      <div style="display: flex; gap: 10px">
        <button class="btn" @click="regen"><Icon name="refresh" :size="15" />Đổi phong cách / Tạo lại</button>
        <button class="btn" @click="emit('toast', 'Đang tải MP4…')"><Icon name="download" :size="15" />Tải MP4</button>
      </div>
      <button class="btn btn-primary" @click="emit('toast', 'Đã lưu video')"><Icon name="save" :size="15" />Lưu video</button>
    </div>
  </div>
</template>
