<script setup>
import { ref, reactive, computed, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import Icon from '@/Components/AiLessonIcon.vue';
import NumStepper from './ai/NumStepper.vue';
import CheckBox from './ai/CheckBox.vue';
import LvlBadge from './ai/LvlBadge.vue';
import ReadingBody from './ai/ReadingBody.vue';
import AudioBody from './ai/AudioBody.vue';
import VideoBody from './ai/VideoBody.vue';
import ExerciseSection from './ai/ExerciseSection.vue';
import AssignModal from './ai/AssignModal.vue';
import HistoryModal from './ai/HistoryModal.vue';
import { RTYPES, BAI_DEFS, mixStr, summaryDesc, CHUNG_MIX } from './ai/data.js';
import LessonDraftHistory from '@/Pages/Lesson/LessonDraftHistory.vue';

const props = defineProps({
  app_id: [String, Number],
  book_id: [String, Number],
  week_id: [String, Number],
  practice_id: [String, Number],
  week: { type: Object, default: () => ({}) },
  practice: { type: Object, default: () => null },
});

/* breadcrumb (giống Lesson/Index) */
const breadcrumbs = computed(() => {
  const w = props.week || {};
  const book = w.book || {};
  const app = book.app || {};
  const c = [{ title: 'App', url: route('apps.dashboard') }];
  if (app.id) c.push({ title: app.name, url: route('books.index', { appId: app.id }) });
  if (book.id) c.push({ title: book.title, url: route('weeks.index', { app_id: app.id, book_id: book.id }) });
  if (w.id) c.push({ title: w.name, url: route('lessons.index', { app_id: app.id, book_id: book.id, week_id: w.id }) });
  c.push({ title: 'Tạo bài học bằng AI', url: '' });
  return c;
});
const backUrl = computed(() => route('lessons.index', { app_id: props.app_id, book_id: props.book_id, week_id: props.week_id }));
const lessonName = computed(() => props.practice?.name || 'Tôi là học sinh lớp 2');

/* ---------- state ---------- */
const selected = reactive({ baidoc: true, sachnoi: true, video: true, baitap: true, baigiao: true });
const content = ref('Bài đọc "Tôi là học sinh lớp 2" — kể về cảm xúc của bạn nhỏ trong ngày tựu trường đầu tiên của lớp 2.');
const cfg = reactive({ grade: 'Lớp 2', subject: 'Tiếng Việt', voice: 'Nova (Nữ trẻ)', vstyle: 'Hoạt hình minh hoạ' });
const counts = reactive({ Dễ: 2, 'Trung bình': 1, Khó: 1 });
const phase = ref('setup');
const open = ref('setup');
const status = reactive({});
const audioPct = ref(0);
const videoPct = ref(0);
const showHist = ref(false);
const showAssign = ref(false);
const toast = ref(null);
const files = ref([]);
const audioTimer = ref(null);
const videoTimer = ref(null);
const bundleIdRef = ref(null);
const pollingIntervals = ref(new Map());
const quizResult = ref(null); // bài tập giao học sinh (theo nhóm)
const quizChung = ref(null); // bài luyện tập chung (1 bộ cho cả lớp)
const hasAssigned = ref(false); // đã giao bài chưa
const historyModal = ref(null);
const assignModalRef = ref(null);

const GRADES = ['Lớp 1', 'Lớp 2', 'Lớp 3', 'Lớp 4', 'Lớp 5'];
const SUBJECTS = ['Tiếng Việt', 'Toán', 'Tự nhiên & Xã hội'];
const VOICES = ['Nova (Nữ trẻ)', 'Mai (Nữ miền Bắc)', 'Minh (Nam miền Bắc)'];

const getQueryParam = (param) => {
  const searchParams = new URLSearchParams(window.location.search);
  return searchParams.get(param);
};

const uploadFiles = async () => {
  const urls = [];
  for (const file of files.value) {
    const formData = new FormData();
    formData.append('file', file);
    try {
      const response = await axios.post(route('lessons.json.uploadMultiFile'), formData);
      if (response.data.success && response.data.url) urls.push(response.data.url);
    } catch (error) {
      showToast('❌ Lỗi upload file');
      throw error;
    }
  }
  return urls;
};

const sel = (id) => selected[id];
const orderedResults = computed(() => RTYPES.filter((t) => sel(t.id)));
const toggleType = (id) => { if (phase.value === 'setup') selected[id] = !selected[id]; };
const toggleOpen = (id) => { open.value = open.value === id ? null : id; };
const setCount = (lv, v) => { counts[lv] = v; };

const addFiles = (list) => { for (const f of list) files.value.push({ name: f.name, isImg: (f.type || '').startsWith('image/') }); };
const removeFile = (i) => files.value.splice(i, 1);

const totalBai = computed(() => BAI_DEFS.reduce((a, d) => a + (counts[d.level] || 0), 0));
const totalCau = computed(() => totalBai.value * 10);
const totalMix = computed(() => {
  const m = { Dễ: 0, 'Trung bình': 0, Khó: 0 };
  BAI_DEFS.forEach((d) => {
    const n = counts[d.level] || 0;
    ['Dễ', 'Trung bình', 'Khó'].forEach((l) => (m[l] += n * d.mix[l]));
  });
  return m;
});
const bais = computed(() => {
  const arr = [];
  BAI_DEFS.forEach((d) => { for (let i = 0; i < (counts[d.level] || 0); i++) arr.push({ level: d.level, mix: d.mix }); });
  return arr;
});
const assignGroupBai = computed(() => ({ yeu: counts['Dễ'] || 0, kha: counts['Trung bình'] || 0, gioi: counts['Khó'] || 0 }));

const setupSummary = computed(() => `${cfg.subject} · ${cfg.grade} · ${totalBai.value} bài`);
const audioBusy = computed(() => status.sachnoi === 'generating');
const videoBusy = computed(() => status.video === 'generating');

const showToast = (msg) => { toast.value = msg; setTimeout(() => (toast.value = null), 2600); };
const resSummary = (id) => {
  const st = status[id];
  if (st === 'done') {
    if (id === 'baidoc') return '1 bài đọc chung';
    if (id === 'sachnoi') return `1 sách nói · giọng ${cfg.voice.split(' (')[0]}`;
    if (id === 'video') return `1 video · ${cfg.vstyle}`;
    if (id === 'baitap') return '10 câu chung cho cả lớp · 3 Dễ · 4 TB · 3 Khó';
    if (id === 'baigiao') return `${totalBai.value} bài giao theo 3 nhóm học sinh`;
  }
  if (st === 'cancelled') return 'Đã huỷ — chưa tạo';
  const pct = id === 'sachnoi' ? audioPct.value : id === 'video' ? videoPct.value : null;
  return pct != null ? `Đang tạo · ${pct}%` : 'Đang tạo…';
};

const countMix = (qList) => {
  const mix = { Dễ: 0, 'Trung bình': 0, Khó: 0 };
  (qList || []).forEach((q) => { if (mix[q.muc_do] != null) mix[q.muc_do]++; });
  return mix;
};

const pollTaskStatus = (bundleId) => {
  if (!bundleId) return;

  const checkAndSave = async () => {
    try {
      const response = await axios.get(route('lessons.json.getBundleStatus'), { params: { bundle_id: bundleId } });
      const bundle = response.data;

      if (bundle.children) {
        // Bài đọc
        if (bundle.children.baidoc) {
          const baidoc = bundle.children.baidoc;
          if (baidoc.status === 'done' && status.baidoc !== 'done') { status.baidoc = 'done'; showToast('✓ Bài đọc tạo xong'); }
          else if (baidoc.status === 'failed') { status.baidoc = 'cancelled'; showToast('❌ Lỗi tạo bài đọc'); }
        }
        // Sách nói
        if (bundle.children.sachnoi) {
          const sachnoi = bundle.children.sachnoi;
          if (sachnoi.status === 'done' && status.sachnoi !== 'done') { status.sachnoi = 'done'; audioPct.value = 100; showToast('🔊 Sách nói tạo xong'); }
          else if (sachnoi.status === 'processing') { audioPct.value = sachnoi.progress || 50; }
          else if (sachnoi.status === 'failed') { status.sachnoi = 'cancelled'; showToast('❌ Lỗi tạo sách nói'); }
        }
        // Video
        if (bundle.children.video) {
          const video = bundle.children.video;
          if (video.status === 'done' && status.video !== 'done') { status.video = 'done'; videoPct.value = 100; showToast('🎬 Video tạo xong'); }
          else if (video.status === 'processing') { videoPct.value = video.progress || 50; }
          else if (video.status === 'failed') { status.video = 'cancelled'; showToast('❌ Lỗi tạo video'); }
        }
        // Bài luyện tập chung (1 bộ cho cả lớp)
        if (bundle.children.baitap_chung) {
          const chung = bundle.children.baitap_chung;
          if (chung.status === 'done' && status.baitap !== 'done') {
            status.baitap = 'done';
            showToast('✓ Bài luyện tập chung tạo xong');
            const rj = chung.result_json;
            let qList = null;
            if (rj) qList = Array.isArray(rj.chung) ? rj.chung : Array.isArray(rj.questions) ? rj.questions : null;
            if (qList && !quizChung.value) {
              quizChung.value = [{ level: 'Chung', mix: countMix(qList), realQuestions: qList }];
            }
          } else if (chung.status === 'failed') { status.baitap = 'cancelled'; showToast('❌ Lỗi tạo bài luyện tập chung'); }
        }
        // Bài tập giao học sinh (theo nhóm mức độ)
        if (bundle.children.baitap_giao) {
          const baitap = bundle.children.baitap_giao;
          if (baitap.status === 'done' && status.baigiao !== 'done') {
            status.baigiao = 'done';
            showToast('✓ Bài tập giao học sinh tạo xong');
            if (baitap.result_json && baitap.result_json.giao && !quizResult.value) {
              quizResult.value = [];
              baitap.result_json.giao.forEach((baiGroup) => {
                quizResult.value.push({
                  level: baiGroup.level,
                  mix: countMix(baiGroup.questions),
                  realQuestions: baiGroup.questions || [],
                });
              });
            }
          } else if (baitap.status === 'failed') { status.baigiao = 'cancelled'; showToast('❌ Lỗi tạo bài tập giao'); }
        }

        const allDone = [
          !sel('baidoc') || status.baidoc === 'done',
          !sel('sachnoi') || status.sachnoi === 'done',
          !sel('video') || status.video === 'done',
          !sel('baitap') || status.baitap === 'done',
          !sel('baigiao') || status.baigiao === 'done',
        ].every((v) => v);

        if (allDone) {
          clearInterval(interval);
          pollingIntervals.value.delete('bundle');
          showToast('✓ Tất cả nội dung đã tạo xong!');
          return true;
        }
      }
      return false;
    } catch (error) {
      console.error('Bundle poll error:', error);
      return false;
    }
  };

  const interval = setInterval(() => { checkAndSave(); }, 3000);
  pollingIntervals.value.set('bundle', interval);
};

const generate = async () => {
  if (orderedResults.value.length === 0) { showToast('❌ Vui lòng chọn ít nhất một loại nội dung'); return; }
  if (!content.value.trim() && files.value.length === 0) { showToast('❌ Vui lòng nhập nội dung hoặc tải file'); return; }

  orderedResults.value.forEach((t) => (status[t.id] = 'generating'));
  phase.value = 'ready';
  open.value = orderedResults.value[0]?.id || 'setup';

  try {
    let contentUrls = [];
    if (files.value.length > 0) contentUrls = await uploadFiles();

    const voiceMap = {
      'nova (nữ trẻ)': 'hn_female_ngochuyen_full_48k-fhg',
      'mai (nữ miền bắc)': 'hn_female_mai',
      'minh (nam miền bắc)': 'hn_male_minh',
    };
    const voiceCode = voiceMap[cfg.voice.toLowerCase()] || 'hn_female_ngochuyen_full_48k-fhg';

    const types = [];
    if (sel('baidoc')) types.push('baidoc');
    if (sel('sachnoi')) types.push('sachnoi');
    if (sel('video')) types.push('video');
    if (sel('baitap')) types.push('baitap_chung');
    if (sel('baigiao')) types.push('baitap_giao');

    const levelMix = {};
    BAI_DEFS.forEach((d) => { if (counts[d.level]) levelMix[d.level] = counts[d.level]; });

    const bundlePayload = {
      content_text: content.value,
      content_urls: contentUrls,
      lop: cfg.grade,
      mon: cfg.subject,
      voice: voiceCode,
      types,
      level_mix: levelMix,
    };

    const bundleResponse = await axios.post(route('lessons.json.generateBundle'), bundlePayload);
    if (bundleResponse.data.bundle_id) {
      bundleIdRef.value = bundleResponse.data.bundle_id;
      showToast(`⏳ Đang tạo ${types.length} loại nội dung...`);
      pollTaskStatus(bundleResponse.data.bundle_id);
    } else {
      throw new Error('Không nhận được bundle_id');
    }
  } catch (error) {
    console.error('Generate error:', error);
    showToast('❌ Lỗi tạo bài giảng');
    orderedResults.value.forEach((t) => (status[t.id] = 'cancelled'));
  }
};

const cancel = (key) => { status[key] = 'cancelled'; showToast(`Đã hủy tạo ${key}`); };
const retry = (id) => {
  status[id] = 'generating';
  if (bundleIdRef.value) pollTaskStatus(bundleIdRef.value);
  else status[id] = 'cancelled';
};

// cập nhật / xoá câu hỏi cho 1 danh sách bài (giao hoặc chung)
const applyQuestionUpdate = (listRef, { baiIdx, qIdx, data }) => {
  const list = listRef.value;
  if (list && list[baiIdx]?.realQuestions) {
    list[baiIdx].realQuestions[qIdx] = data;
    list[baiIdx].mix = countMix(list[baiIdx].realQuestions);
  }
  showToast('✓ Câu hỏi đã cập nhật');
};
const applyQuestionDelete = (listRef, { baiIdx, qIdx }) => {
  const list = listRef.value;
  if (list && list[baiIdx]?.realQuestions) {
    list[baiIdx].realQuestions.splice(qIdx, 1);
    list[baiIdx].mix = countMix(list[baiIdx].realQuestions);
  }
  showToast('✓ Câu hỏi đã xóa');
};
const onQuestionUpdate = (e) => applyQuestionUpdate(quizResult, e);
const onQuestionDelete = (e) => applyQuestionDelete(quizResult, e);
const onChungUpdate = (e) => applyQuestionUpdate(quizChung, e);
const onChungDelete = (e) => applyQuestionDelete(quizChung, e);

const onAssignExercises = async (data) => {
  try {
    const response = await axios.post(route('lessons.json.assignExercises'), {
      student_ids: data.studentIds,
      class_code: data.className,
      due_date: data.dueDate,
      due_time: data.dueTime,
      note: data.note,
      exercise_items: data.exerciseItems,
    });
    if (response.data.success) {
      showToast('✓ Giao bài thành công!');
      hasAssigned.value = true;
      assignModalRef.value?.closeModal();
    } else {
      showToast('❌ ' + response.data.message);
    }
  } catch (error) {
    showToast('❌ Lỗi giao bài');
    console.error('Assign error:', error);
  }
};

const saveAll = async (e) => {
  const mode = e?.mode;
  console.log('=== saveAll called ===', { mode, quizResult: quizResult.value?.length, quizChung: quizChung.value?.length });
  const practice_id = props.practice_id || getQueryParam('practice_id');
  if (!practice_id || !bundleIdRef.value) { console.log('❌ Missing practice_id or bundleId'); showToast('❌ Không thể lưu - thiếu thông tin'); return; }
  try {
    if (mode === 'chung' && quizChung.value?.length) {
      console.log('Saving common practice questions...');
      // Save common practice mode - save to question_editor only
      const allQuestions = quizChung.value.flatMap(b => b.realQuestions || []);
      const response = await axios.post(route('lessons.json.saveLessonPracticeQuestions'), {
        practice_id,
        questions: allQuestions,
        app_id: props.app_id || getQueryParam('app_id'),
        book_id: props.book_id || getQueryParam('book_id'),
        week_id: props.week_id || getQueryParam('week_id'),
      });
      if (response.data.success) {
        showToast('✓ Bài luyện tập đã lưu');
      }
    } else if (mode === 'giao' && quizResult.value?.length) {
      console.log('Saving assigned exercises...');
      // Save assign mode - save current exercise items with all edits/deletes
      const response = await axios.post(route('lessons.json.saveAssignExercises'), {
        practice_id,
        bundle_id: bundleIdRef.value,
        exercise_items: quizResult.value,
        app_id: props.app_id || getQueryParam('app_id'),
        book_id: props.book_id || getQueryParam('book_id'),
        week_id: props.week_id || getQueryParam('week_id'),
      });
      if (response.data.success) {
        showToast('✓ Bài tập đã lưu');
      }
    } else {
      // Normal save - save to lesson_drafts
      const response = await axios.post(route('lessons.json.saveBundleResult'), {
        practice_id,
        bundle_id: bundleIdRef.value,
        app_id: props.app_id || getQueryParam('app_id'),
        book_id: props.book_id || getQueryParam('book_id'),
        week_id: props.week_id || getQueryParam('week_id'),
      });
      if (response.data.success) {
        showToast('✓ Bài giảng đã lưu vào nháp');
        setTimeout(() => { if (historyModal.value) historyModal.value.openModal(); }, 500);
      }
    }
  } catch (error) {
    console.error('Save error:', error);
    showToast('❌ Lỗi khi lưu');
  }
};

const onSaveSingleExercise = async (e) => {
  const { baiIdx, mode } = e;
  console.log('=== onSaveSingleExercise called ===', { baiIdx, mode });
  const practice_id = props.practice_id || getQueryParam('practice_id');
  if (!practice_id || !bundleIdRef.value) { console.log('❌ Missing practice_id or bundleId'); showToast('❌ Không thể lưu - thiếu thông tin'); return; }
  try {
    if (mode === 'chung' && quizChung.value?.length) {
      console.log('Saving single common practice exercise...');
      // Save single exercise from common practice
      const exercise = quizChung.value[baiIdx];
      const questions = exercise.realQuestions || [];
      const response = await axios.post(route('lessons.json.saveLessonPracticeQuestions'), {
        practice_id,
        questions,
        app_id: props.app_id || getQueryParam('app_id'),
        book_id: props.book_id || getQueryParam('book_id'),
        week_id: props.week_id || getQueryParam('week_id'),
      });
      if (response.data.success) {
        showToast(`✓ Bài ${baiIdx + 1} đã lưu`);
      } else {
        showToast('❌ ' + response.data.message);
      }
    } else if (mode === 'giao' && quizResult.value?.length) {
      console.log('Saving single assigned exercise...');
      // Save single exercise from assigned
      const response = await axios.post(route('lessons.json.saveAssignExercises'), {
        practice_id,
        bundle_id: bundleIdRef.value,
        exercise_items: [quizResult.value[baiIdx]], // Save only this exercise
        app_id: props.app_id || getQueryParam('app_id'),
        book_id: props.book_id || getQueryParam('book_id'),
        week_id: props.week_id || getQueryParam('week_id'),
      });
      if (response.data.success) {
        showToast(`✓ Bài ${baiIdx + 1} đã lưu`);
      } else {
        showToast('❌ ' + response.data.message);
      }
    }
  } catch (error) {
    showToast('❌ Lỗi lưu bài');
    console.error('Save error:', error);
  }
};

/* accent cho khối đang tạo */
const sachnoiAccent = { c: '#7c3aed', bd: '#e2d5f5', bg: 'linear-gradient(180deg,#fcfaff,#f6f0fe)', ring: '#e9ddf8' };
const videoAccent = { c: '#ea580c', bd: '#fad9bf', bg: 'linear-gradient(180deg,#fffaf5,#fff1e6)', ring: '#fbd9bd' };
const eta = (pct) => Math.max(1, Math.ceil(((100 - pct) / 100) * 16));

onUnmounted(() => {
  pollingIntervals.value.forEach((interval) => clearInterval(interval));
  pollingIntervals.value.clear();
});
</script>

<template>
  <Head title="Tạo bài học bằng AI" />
  <SchoolLayout :breadcrumbs="breadcrumbs">
    <div class="ai-flow">
      <div class="page">
        <div class="flow-head">
          <Link :href="backUrl" class="flow-back"><Icon name="back" :size="16" />Quay lại</Link>
          <div class="flow-spacer"></div>
          <button class="btn" @click="historyModal && historyModal.openModal()"><Icon name="history" :size="16" />Lịch sử</button>
        </div>
        <h1 class="page-title" style="margin-bottom: 18px">Tạo nội dung bằng AI <span class="v2-tag">V3</span></h1>

        <!-- chọn loại nội dung -->
        <div class="type-row">
          <div
            v-for="t in RTYPES"
            :key="t.id"
            class="type-toggle"
            :class="{ on: sel(t.id) }"
            :style="phase !== 'setup' ? { cursor: 'default', opacity: sel(t.id) ? 1 : 0.45 } : {}"
            @click="toggleType(t.id)"
          >
            <CheckBox :on="sel(t.id)" />
            <span class="tt-ic" :style="{ background: t.color }"><Icon :name="t.icon" :size="17" /></span>
            <span class="tt-name">{{ t.name }}</span>
          </div>
        </div>

        <div class="acc">
          <!-- ===== Sec: Nguồn & thiết lập ===== -->
          <div class="acc-item" :class="{ open: open === 'setup', done: phase === 'ready' }">
            <button class="acc-head" @click="toggleOpen('setup')">
              <span class="acc-dot">
                <Icon v-if="phase === 'ready' && open !== 'setup'" name="check" :size="16" :stroke="3" />
                <template v-else>1</template>
              </span>
              <div class="acc-head-main">
                <div class="acc-title">Nguồn &amp; thiết lập</div>
                <div v-if="open !== 'setup'" class="acc-summary">{{ setupSummary }}</div>
                <div v-else class="acc-summary alt">{{ summaryDesc.setup }}</div>
              </div>
              <span v-if="open !== 'setup' && phase === 'ready'" class="badge badge-done"><Icon name="check" :size="12" :stroke="3" />Đã tạo</span>
              <span class="acc-caret"><Icon name="chevDown" :size="18" /></span>
            </button>
            <div v-if="open === 'setup'" class="acc-body">
              <p class="sec-sub">Nhập nội dung bài học (hoặc tải file), chọn số lượng bài tập theo mức độ. Bài đọc, Sách nói và Video dùng <b>một bản chung</b> cho cả bài học; mỗi bài tập gồm 10 câu.</p>
              <div class="setup-grid">
                <div class="full">
                  <label class="lbl-sm">Nội dung / chủ đề bài học</label>
                  <textarea class="ta" v-model="content" placeholder="Nhập nội dung bài đọc, đoạn văn, hoặc chủ đề…"></textarea>
                </div>
                <div>
                  <label class="lbl-sm">Lớp</label>
                  <select class="ctrl" v-model="cfg.grade"><option v-for="g in GRADES" :key="g">{{ g }}</option></select>
                </div>
                <div>
                  <label class="lbl-sm">Môn</label>
                  <select class="ctrl" v-model="cfg.subject"><option v-for="s in SUBJECTS" :key="s">{{ s }}</option></select>
                </div>
                <div>
                  <label class="lbl-sm" :style="{ opacity: sel('sachnoi') ? 1 : 0.4 }">Giọng nói</label>
                  <select class="ctrl" :disabled="!sel('sachnoi')" v-model="cfg.voice" :style="{ opacity: sel('sachnoi') ? 1 : 0.5 }">
                    <option v-for="v in VOICES" :key="v">{{ v }}</option>
                  </select>
                </div>
              </div>

              <div class="upload-mini">
                <span class="or">Hoặc tải file:</span>
                <label class="btn">
                  <Icon name="upload2" :size="15" />Chọn file (Ảnh, PDF, Word, Excel)
                  <input type="file" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" style="display: none" @change="(e) => { addFiles(e.target.files); e.target.value = ''; }" />
                </label>
                <span class="muted" style="font-size: 13px">Ảnh, PDF, Word, Excel · tối đa 15MB</span>
              </div>
              <div v-if="files.length" class="file-pills">
                <span v-for="(f, i) in files" :key="i" class="file-pill">
                  <Icon :name="f.isImg ? 'image' : 'fileSpread'" :size="15" />{{ f.name }}
                  <span class="x" @click="removeFile(i)"><Icon name="x" :size="13" /></span>
                </span>
              </div>

              <div class="qty-box">
                <div class="opt-label">Mức độ &amp; số lượng bài</div>
                <p class="lvl-help">Chọn số <b>bài tập</b> muốn tạo ở mỗi mức độ. Mỗi bài tập gồm <b>10 câu hỏi</b> với tỉ lệ độ khó cố định. Bài đọc / Sách nói / Video chỉ tạo một bản chung.</p>
                <div class="lvl-rows">
                  <div v-for="d in BAI_DEFS" :key="d.level" class="lvl-row" :class="{ on: (counts[d.level] || 0) > 0 }">
                    <div class="lr-pick bai" style="cursor: default">
                      <LvlBadge :level="d.level" />
                      <span class="bai-rowname">{{ d.name }}</span>
                      <span class="bai-mixtag">{{ mixStr(d.mix) }}</span>
                    </div>
                    <div class="lr-qty">
                      <span class="muted">Số bài</span>
                      <NumStepper :value="counts[d.level] || 0" :min="0" @update:value="(v) => setCount(d.level, v)" />
                    </div>
                  </div>
                </div>
                <div class="lvl-total">Tổng: <b>{{ totalBai }}</b> bài · <b>{{ totalCau }}</b> câu hỏi
                  <span class="bai-breakdown"> ({{ totalMix['Dễ'] }} Dễ · {{ totalMix['Trung bình'] }} TB · {{ totalMix['Khó'] }} Khó)</span>
                </div>
              </div>

              <div class="acc-actions">
                <span class="foot-info">Sẽ tạo <b>{{ totalBai }}</b> bài tập + {{ [sel('baidoc') && 'Bài đọc', sel('sachnoi') && 'Sách nói', sel('video') && 'Video'].filter(Boolean).join(' · ') || 'nội dung' }} chung</span>
                <button class="btn btn-ai btn-lg" :disabled="orderedResults.length === 0 || totalBai === 0" @click="generate">
                  <Icon :name="phase === 'ready' ? 'refresh' : 'sparkles'" :size="16" :fill="phase !== 'ready'" />
                  {{ phase === 'ready' ? 'Tạo lại nội dung' : `Tạo ${totalBai} bài` }}
                </button>
              </div>
            </div>
          </div>

          <!-- ===== Sec: kết quả ===== -->
          <template v-if="phase === 'ready'">
            <div
              v-for="(t, i) in orderedResults"
              :key="t.id"
              class="acc-item"
              :class="{ open: open === t.id, done: status[t.id] === 'done', gen: status[t.id] === 'generating' }"
            >
              <button class="acc-head" @click="toggleOpen(t.id)">
                <span class="acc-dot">
                  <span v-if="status[t.id] === 'generating'" class="spin"></span>
                  <Icon v-else-if="status[t.id] === 'done' && open !== t.id" name="check" :size="16" :stroke="3" />
                  <template v-else>{{ i + 2 }}</template>
                </span>
                <div class="acc-head-main">
                  <div class="acc-title">{{ t.name }}</div>
                  <div v-if="open !== t.id" class="acc-summary">{{ resSummary(t.id) }}</div>
                  <div v-else class="acc-summary alt">{{ summaryDesc[t.id] }}</div>
                </div>
                <span v-if="open !== t.id && status[t.id] === 'done'" class="badge badge-done"><Icon name="check" :size="12" :stroke="3" />Đã tạo</span>
                <span v-else-if="open !== t.id && status[t.id] === 'generating'" class="badge badge-gen"><span class="spin"></span>Đang tạo…</span>
                <span v-else-if="open !== t.id && status[t.id] === 'cancelled'" class="badge badge-draft">Đã huỷ</span>
                <span class="acc-caret"><Icon name="chevDown" :size="18" /></span>
              </button>

              <div v-if="open === t.id" class="acc-body">
                <!-- đang tạo -->
                <template v-if="status[t.id] === 'generating'">
                  <template v-if="t.id === 'sachnoi' || t.id === 'video'">
                    <p class="sec-sub">{{ t.id === 'sachnoi' ? 'Sách nói' : 'Video' }} cần thời gian tạo nên thường lâu hơn. Bạn cứ xem và chỉnh những phần đã xong — khối này tự cập nhật khi hoàn tất.</p>
                    <div class="audio-gen" :style="{ borderColor: (t.id === 'sachnoi' ? sachnoiAccent : videoAccent).bd, background: (t.id === 'sachnoi' ? sachnoiAccent : videoAccent).bg }">
                      <div class="ag-top">
                        <div class="ag-ring"><div class="ag-spin" :style="{ borderTopColor: (t.id === 'sachnoi' ? sachnoiAccent : videoAccent).c, borderColor: (t.id === 'sachnoi' ? sachnoiAccent : videoAccent).ring }"></div></div>
                        <div class="ag-meta">
                          <div class="ag-title" :style="{ color: (t.id === 'sachnoi' ? sachnoiAccent : videoAccent).c }">Đang tạo {{ t.id === 'sachnoi' ? 'sách nói' : 'video' }}…</div>
                          <div class="ag-sub">{{ t.id === 'sachnoi' ? `1 bản chung · giọng ${cfg.voice}` : `1 video chung · ${cfg.vstyle}` }} · còn khoảng {{ eta(t.id === 'sachnoi' ? audioPct : videoPct) }} giây</div>
                        </div>
                        <div class="ag-pct" :style="{ color: (t.id === 'sachnoi' ? sachnoiAccent : videoAccent).c }">{{ t.id === 'sachnoi' ? audioPct : videoPct }}%</div>
                      </div>
                      <div class="ag-bar"><i :style="{ width: (t.id === 'sachnoi' ? audioPct : videoPct) + '%', background: (t.id === 'sachnoi' ? sachnoiAccent : videoAccent).c }"></i></div>
                      <div class="ag-actions"><button class="btn" @click="cancel(t.id)"><Icon name="x" :size="14" />Huỷ tạo</button></div>
                    </div>
                  </template>
                  <div v-else class="skel-note"><span class="spin"></span>Đang tạo {{ t.unit }}…</div>
                </template>

                <!-- đã huỷ -->
                <template v-else-if="status[t.id] === 'cancelled'">
                  <p class="sec-sub">Đã huỷ tạo {{ t.unit }}. Các phần khác không bị ảnh hưởng.</p>
                  <button class="btn btn-primary" @click="retry(t.id)"><Icon name="refresh" :size="15" />Tạo lại {{ t.unit }}</button>
                </template>

                <!-- hoàn tất -->
                <template v-else>
                  <ReadingBody v-if="t.id === 'baidoc'" @toast="showToast" />
                  <AudioBody v-else-if="t.id === 'sachnoi'" @toast="showToast" />
                  <VideoBody v-else-if="t.id === 'video'" :vstyle="cfg.vstyle" @toast="showToast" />
                  <!-- Bài luyện tập chung -->
                  <ExerciseSection
                    v-else-if="t.id === 'baitap'"
                    mode="chung"
                    :bais="quizChung && quizChung.length ? quizChung : [{ level: 'Chung', mix: CHUNG_MIX }]"
                    :color="t.color"
                    @save="saveAll"
                    @save-single="onSaveSingleExercise"
                    @question-update="onChungUpdate"
                    @question-delete="onChungDelete"
                  />
                  <!-- Bài tập giao học sinh -->
                  <ExerciseSection
                    v-else-if="t.id === 'baigiao'"
                    mode="giao"
                    :bais="quizResult && quizResult.length ? quizResult : bais"
                    :color="t.color"
                    :counts="counts"
                    :total-bai="totalBai"
                    :total-cau="totalCau"
                    @assign="showAssign = true"
                    @save="saveAll"
                    @save-single="onSaveSingleExercise"
                    @question-update="onQuestionUpdate"
                    @question-delete="onQuestionDelete"
                  />
                </template>
              </div>
            </div>
          </template>
        </div>

        <!-- thanh lưu -->
        <div v-if="phase === 'ready'" class="save-bar">
          <span class="save-info">
            <template v-if="audioBusy || videoBusy">Đang tạo {{ audioBusy && videoBusy ? 'sách nói & video' : audioBusy ? 'sách nói' : 'video' }}… — các phần khác đã sẵn sàng để lưu</template>
            <template v-else>Đã tạo nội dung cho <b>“{{ lessonName }}”</b> · <b>{{ totalBai }}</b> bài tập</template>
          </span>
          <div style="display: flex; gap: 10px">
            <button class="btn" @click="historyModal && historyModal.openModal()"><Icon name="history" :size="15" />Lịch sử</button>
            <button class="btn btn-primary btn-lg" @click="saveAll">
              <Icon name="save" :size="16" />{{ audioBusy || videoBusy ? 'Lưu phần đã xong' : 'Lưu tất cả' }}
            </button>
          </div>
        </div>
      </div>

      <AssignModal
        ref="assignModalRef"
        v-if="showAssign"
        :group-bai="assignGroupBai"
        :exercise-items="quizResult || []"
        :total-assigned="totalCau"
        @close="showAssign = false"
        @done="showAssign = false"
        @assign="onAssignExercises"
      />
      <LessonDraftHistory ref="historyModal" :practice-id="parseInt(props.practice_id) || parseInt(getQueryParam('practice_id'))" />
      <div v-if="toast" class="toast"><Icon name="check" :size="16" :stroke="3" />{{ toast }}</div>
    </div>
  </SchoolLayout>
</template>

<style lang="scss">
@use '../../../css/ai-lesson' as *;
</style>
