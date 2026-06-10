<script setup>
import { ref, computed } from 'vue';
import Icon from '@/Components/AiLessonIcon.vue';
import VersionBar from './VersionBar.vue';
import { LESSON, READING_VARIANTS, nowStr } from './data.js';

const emit = defineEmits(['toast']);

const versions = ref([{ n: 1, time: nowStr(), text: LESSON.reading.join('\n\n') }]);
const activeIdx = ref(0);
const editing = ref(false);
const draft = ref(versions.value[0].text);
const vi = ref(0);

const text = computed(() => versions.value[activeIdx.value].text);
const pick = (i) => { activeIdx.value = i; editing.value = false; };
const regen = () => {
  const variant = READING_VARIANTS[vi.value % READING_VARIANTS.length].join('\n\n');
  vi.value++;
  versions.value = [{ n: versions.value[0].n + 1, time: nowStr(), text: variant }, ...versions.value];
  activeIdx.value = 0;
  editing.value = false;
  emit('toast', '✨ Đã tạo lại bài đọc');
};
const startEdit = () => { draft.value = text.value; editing.value = true; };
const saveEdit = () => {
  versions.value = versions.value.map((v, i) => (i === activeIdx.value ? { ...v, text: draft.value } : v));
  editing.value = false;
};
const previewFn = (v) => v.text.replace(/\n+/g, ' ').slice(0, 90) + '…';
const paragraphs = computed(() => text.value.split('\n\n'));
const wordCount = computed(() => text.value.split(/\s+/).length);
</script>

<template>
  <div>
    <p class="sec-sub">Bài đọc do AI tạo từ nội dung bài học. Mỗi lần tạo lại sẽ lưu thành một phiên bản — bạn xem lại hoặc khôi phục bất cứ lúc nào.</p>
    <VersionBar :versions="versions" :active-idx="activeIdx" :preview="previewFn" kind="restore" @pick="pick" />
    <div v-if="!editing" class="doc">
      <div class="doc-title">{{ LESSON.title }}</div>
      <div class="doc-meta"><span>{{ LESSON.subject }} · {{ LESSON.grade }}</span><span>~{{ wordCount }} từ</span><span>Thời lượng đọc ~1 phút</span></div>
      <div class="doc-body"><p v-for="(p, i) in paragraphs" :key="i">{{ p }}</p></div>
    </div>
    <textarea v-else class="doc-edit" v-model="draft"></textarea>
    <div class="acc-actions">
      <div style="display: flex; gap: 10px">
        <button v-if="!editing" class="btn" @click="startEdit"><Icon name="edit" :size="15" />Sửa nội dung</button>
        <button v-else class="btn btn-primary" @click="saveEdit"><Icon name="check" :size="15" :stroke="3" />Xong</button>
        <button class="btn" @click="regen"><Icon name="refresh" :size="15" />Tạo lại</button>
      </div>
      <button class="btn btn-primary" @click="emit('toast', 'Đã lưu bài đọc')"><Icon name="save" :size="15" />Lưu bài đọc</button>
    </div>
  </div>
</template>
