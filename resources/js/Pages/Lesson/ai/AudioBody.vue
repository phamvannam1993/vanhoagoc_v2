<script setup>
import { ref, computed } from 'vue';
import Icon from '@/Components/AiLessonIcon.vue';
import VersionBar from './VersionBar.vue';
import { LESSON, VOICE_LIST, SPEEDS, DURATIONS, nowStr } from './data.js';

const emit = defineEmits(['toast']);

const versions = ref([{ n: 1, time: nowStr(), voice: LESSON.voice, speed: 'Vừa', duration: LESSON.duration }]);
const activeIdx = ref(0);
const playing = ref(false);
const pickerOpen = ref(false);
const selVoice = ref(LESSON.voice);
const selSpeed = ref('Vừa');
const bars = ref(Array.from({ length: 60 }, () => 6 + Math.round(Math.random() * 26)));
const prog = 0.32;
const cur = computed(() => versions.value[activeIdx.value]);

const pick = (i) => { activeIdx.value = i; playing.value = false; };
const openPicker = () => { selVoice.value = cur.value.voice; selSpeed.value = cur.value.speed || 'Vừa'; pickerOpen.value = true; };
const regen = () => {
  const duration = DURATIONS[versions.value.length % DURATIONS.length];
  versions.value = [{ n: versions.value[0].n + 1, time: nowStr(), voice: selVoice.value, speed: selSpeed.value, duration }, ...versions.value];
  activeIdx.value = 0;
  playing.value = false;
  pickerOpen.value = false;
  emit('toast', '🔊 Đã tạo lại sách nói');
};
const previewFn = (v) => `Giọng ${v.voice} · ${v.speed || 'Vừa'} · ${v.duration}`;
</script>

<template>
  <div>
    <p class="sec-sub">Bản thu âm thanh đọc bài, giọng <b>{{ cur.voice }}</b><template v-if="cur.speed"> · tốc độ {{ cur.speed }}</template>. Mỗi lần tạo lại được lưu thành phiên bản riêng để so sánh và khôi phục.</p>
    <VersionBar :versions="versions" :active-idx="activeIdx" :preview="previewFn" kind="restore" @pick="pick" />
    <div class="player">
      <button class="player-btn" @click="playing = !playing"><Icon :name="playing ? 'pause' : 'play'" :size="20" fill /></button>
      <div class="player-mid">
        <div class="wave">
          <i v-for="(h, i) in bars" :key="i" :class="{ on: i / bars.length < prog }" :style="{ height: h + 'px' }"></i>
        </div>
        <div class="player-time"><span>0:42</span><span>{{ cur.duration }}</span></div>
      </div>
      <span class="voice-chip"><Icon name="volume" :size="14" />{{ cur.voice }}</span>
    </div>
    <div class="transcript">
      <div class="tl">Lời thoại</div>
      <p v-for="(p, i) in LESSON.reading" :key="i" style="margin: 0 0 8px">{{ p }}</p>
    </div>
    <div class="acc-actions">
      <div style="display: flex; gap: 10px; position: relative">
        <button class="btn" :class="{ 'btn-active': pickerOpen }" @click="pickerOpen ? (pickerOpen = false) : openPicker()">
          <Icon name="volume" :size="15" />Đổi giọng / Tạo lại<Icon name="chevDown" :size="14" />
        </button>
        <button class="btn" @click="emit('toast', 'Đang tải MP3…')"><Icon name="download" :size="15" />Tải MP3</button>
        <div v-if="pickerOpen" class="voice-pop">
          <div class="vp-head">Chọn giọng đọc</div>
          <div class="vp-list">
            <button v-for="v in VOICE_LIST" :key="v.name" class="vp-item" :class="{ on: selVoice === v.name }" @click="selVoice = v.name">
              <span class="vp-ic"><Icon name="mic" :size="15" /></span>
              <span class="vp-main">
                <span class="vp-name">{{ v.name }}<span class="vp-region">{{ v.region }}</span></span>
                <span class="vp-desc">{{ v.desc }}</span>
              </span>
              <Icon v-if="selVoice === v.name" name="check" :size="16" :stroke="3" />
            </button>
          </div>
          <div class="vp-speed">
            <span class="vp-speed-lbl">Tốc độ đọc</span>
            <div class="seg"><button v-for="s in SPEEDS" :key="s" :class="{ on: selSpeed === s }" @click="selSpeed = s">{{ s }}</button></div>
          </div>
          <div class="vp-foot">
            <button class="btn btn-ghost" @click="pickerOpen = false">Huỷ</button>
            <button class="btn btn-primary" @click="regen"><Icon name="refresh" :size="15" />Tạo lại với giọng này</button>
          </div>
        </div>
      </div>
      <button class="btn btn-primary" @click="emit('toast', 'Đã lưu sách nói')"><Icon name="save" :size="15" />Lưu sách nói</button>
    </div>
  </div>
</template>
