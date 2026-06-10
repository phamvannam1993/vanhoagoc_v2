<script setup>
import { ref, reactive, computed, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MasterLayout from '@/Layouts/MasterLayout.vue';
import Icon from '@/Components/AiLessonIcon.vue';
import NumStepper from './ai/NumStepper.vue';
import CheckBox from './ai/CheckBox.vue';
import LvlBadge from './ai/LvlBadge.vue';
import ReadingBody from './ai/ReadingBody.vue';
import AudioBody from './ai/AudioBody.vue';
import VideoBody from './ai/VideoBody.vue';
import ExerciseSection from './ai/ExerciseSection.vue';
import ExerciseBaiBody from './ai/ExerciseBaiBody.vue';
import AssignModal from './ai/AssignModal.vue';
import HistoryModal from './ai/HistoryModal.vue';
import { RTYPES, BAI_DEFS, mixStr, summaryDesc, CHUNG_MIX } from './ai/data.js';

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

/* ---------- state (port flow2-v3 App) ---------- */
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

const GRADES = ['Lớp 1', 'Lớp 2', 'Lớp 3', 'Lớp 4', 'Lớp 5'];
const SUBJECTS = ['Tiếng Việt', 'Toán', 'Tự nhiên & Xã hội'];
const VOICES = ['Nova (Nữ trẻ)', 'Mai (Nữ miền Bắc)', 'Minh (Nam miền Bắc)'];

const sel = (id) => selected[id];
const orderedResults = computed(() => RTYPES.filter((t) => sel(t.id)));
const toggleType = (id) => { if (phase.value === 'setup') selected[id] = !selected[id]; };
const toggleOpen = (id) => { open.value = open.value === id ? null : id; };
const setCount = (lv, v) => { counts[lv] = v; };

const addFiles = (list) => {
  for (const f of list) files.value.push({ name: f.name, isImg: (f.type || '').startsWith('image/') });
};
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
  BAI_DEFS.forEach((d) => {
    for (let i = 0; i < (counts[d.level] || 0); i++) arr.push({ level: d.level, mix: d.mix });
  });
  return arr;
});
const assignGroupBai = computed(() => ({ yeu: counts['Dễ'] || 0, kha: counts['Trung bình'] || 0, gioi: counts['Khó'] || 0 }));

const setupSummary = computed(() => `${cfg.subject} · ${cfg.grade} · ${totalBai.value} bài`);
const audioBusy = computed(() => status.sachnoi === 'generating');
const videoBusy = computed(() => status.video === 'generating');

const showToast = (msg) => {
  toast.value = msg;
  setTimeout(() => (toast.value = null), 2600);
};
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

/* mô phỏng tiến trình (không backend) */
const runProgress = (key, pctRef, timerRef, doneMsg, speed) => {
  status[key] = 'generating';
  pctRef.value = 0;
  clearInterval(timerRef.value);
  timerRef.value = setInterval(() => {
    pctRef.value = Math.min(100, pctRef.value + Math.round(3 + Math.random() * 7));
    if (pctRef.value >= 100) {
      clearInterval(timerRef.value);
      status[key] = 'done';
      showToast(doneMsg);
    }
  }, speed);
};
const runAudio = () => runProgress('sachnoi', audioPct, audioTimer, '🔊 Đã tạo xong sách nói', 520);
const runVideo = () => runProgress('video', videoPct, videoTimer, '🎬 Đã tạo xong video', 560);

const generate = () => {
  orderedResults.value.forEach((t) => (status[t.id] = 'generating'));
  phase.value = 'ready';
  open.value = orderedResults.value[0]?.id || 'setup';
  if (sel('baidoc')) setTimeout(() => (status.baidoc = 'done'), 1500);
  if (sel('baitap')) setTimeout(() => (status.baitap = 'done'), 2300);
  if (sel('baigiao')) setTimeout(() => (status.baigiao = 'done'), 2600);
  if (sel('sachnoi')) runAudio();
  if (sel('video')) runVideo();
};
const cancel = (key, timerRef) => {
  clearInterval(timerRef.value);
  status[key] = 'cancelled';
};
const retry = (id) => {
  if (id === 'sachnoi') runAudio();
  else if (id === 'video') runVideo();
  else status[id] = 'done';
};
const saveAll = () => showToast('Đã lưu tất cả bài giảng');

/* accent cho khối đang tạo */
const sachnoiAccent = { c: '#7c3aed', bd: '#e2d5f5', bg: 'linear-gradient(180deg,#fcfaff,#f6f0fe)', ring: '#e9ddf8' };
const videoAccent = { c: '#ea580c', bd: '#fad9bf', bg: 'linear-gradient(180deg,#fffaf5,#fff1e6)', ring: '#fbd9bd' };
const eta = (pct) => Math.max(1, Math.ceil(((100 - pct) / 100) * 16));

onUnmounted(() => {
  clearInterval(audioTimer.value);
  clearInterval(videoTimer.value);
});
</script>

<template>
  <Head title="Tạo bài học bằng AI" />
  <MasterLayout :breadcrumbs="breadcrumbs">
    <div class="ai-flow">
      <div class="page">
        <div class="flow-head">
          <Link :href="backUrl" class="flow-back"><Icon name="back" :size="16" />Quay lại</Link>
          <div class="flow-spacer"></div>
          <button class="btn" @click="showHist = true"><Icon name="history" :size="16" />Lịch sử</button>
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
                      <div class="ag-actions"><button class="btn" @click="cancel(t.id, t.id === 'sachnoi' ? audioTimer : videoTimer)"><Icon name="x" :size="14" />Huỷ tạo</button></div>
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
                  <div v-else-if="t.id === 'baitap'">
                    <p class="sec-sub">Một bộ <b>10 câu hỏi</b> (3 Dễ · 4 TB · 3 Khó) dùng chung cho <b>cả lớp</b>. Bạn có thể sửa hoặc tạo lại từng câu / cả bài bằng AI.</p>
                    <ExerciseBaiBody :mix="CHUNG_MIX" />
                  </div>
                  <ExerciseSection
                    v-else-if="t.id === 'baigiao'"
                    :bais="bais"
                    :color="t.color"
                    :counts="counts"
                    :total-bai="totalBai"
                    @assign="showAssign = true"
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
            <button class="btn" @click="showHist = true"><Icon name="history" :size="15" />Lịch sử</button>
            <button class="btn btn-primary btn-lg" @click="saveAll">
              <Icon name="save" :size="16" />{{ audioBusy || videoBusy ? 'Lưu phần đã xong' : 'Lưu tất cả' }}
            </button>
          </div>
        </div>
      </div>

      <AssignModal v-if="showAssign" :group-bai="assignGroupBai" :total-assigned="totalCau" @close="showAssign = false" @done="showAssign = false" />
      <HistoryModal v-if="showHist" @close="showHist = false" />
      <div v-if="toast" class="toast"><Icon name="check" :size="16" :stroke="3" />{{ toast }}</div>
    </div>
  </MasterLayout>
</template>

<style lang="scss">
@import '../../../css/ai-lesson.scss';
</style>
