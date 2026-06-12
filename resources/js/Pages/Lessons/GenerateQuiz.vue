<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, reactive, computed, onMounted } from 'vue';
import { useToast } from 'vue-toastification';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import Icon from '@/Components/AiLessonIcon.vue';
import CheckBox from '@/Pages/Lesson/ai/CheckBox.vue';
import LessonDraftHistory from "@/Pages/Lesson/LessonDraftHistory.vue";
import ExerciseList from "@/Pages/Lessons/ExerciseList.vue";
import Editor from '@tinymce/tinymce-vue';

const toast = useToast();
const props = defineProps({
  app_id: String,
  book_id: String,
  week_id: String,
  practice_id: String,
});

const getQueryParam = (param) => {
  const searchParams = new URLSearchParams(window.location.search);
  return searchParams.get(param);
};

// Form state - matching AiCreate structure (all true by default like AiCreate)
const selected = reactive({ exercises: true, text: true, audio: true, video: true });
const content = ref('Nhập nội dung bài học hoặc chủ đề bài tập');
const cfg = reactive({ grade: 'Lớp 1', subject: 'Toán', voice: 'Nova (Nữ trẻ)', vstyle: 'Hoạt hình minh hoạ' });
const counts = reactive({ chon: 2, sx: 2, noi: 1 });
const selectedFiles = ref([]);

// Loading & Results
const loading = ref(false);
const generatingQuiz = ref(false);
const generatingText = ref(false);
const generatingAudio = ref(false);
const generationStatus = ref('');
const pollingTaskIds = ref(new Map());
const pollingIntervals = ref(new Map());
const results = ref(new Map());
const quizResult = ref(null);
const selectedByType = ref({ chon: new Set(), sx: new Set(), noi: new Set() });
const editingQuestion = ref(null);
const editingData = ref(null);
const selectedQuestionTab = ref('chon');
const historyModal = ref(null);
const phase = ref('setup');
const open = ref('setup');
const files = ref([]);

// Progress tracking for long-running tasks
const progressMap = reactive({ text: 0, audio: 0, video: 0 });
const statusMap = reactive({ text: null, audio: null, video: null }); // 'generating', 'done', 'error'

// Editor state for text content
const editingTextContent = ref(false);
const textEditorContent = ref('');

const apiKey = import.meta.env.VITE_TINY_MCE_API_KEY;
const editorConfig = {
  height: '400px',
  plugins: 'lists link image table code help wordcount',
  menubar: 'file edit view format tools table',
  statusbar: true,
  language: 'vi',
  toolbar: 'undo redo | formatselect fontselect fontsizeselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image',
  font_family_formats:
    'Arial=arial,helvetica,sans-serif;' +
    'Times New Roman=times new roman,times,serif;' +
    'Courier New=courier new,courier,monospace;' +
    'Georgia=georgia,serif;' +
    'Verdana=verdana,sans-serif;',
};

// Constants - matching AiCreate patterns (same order as RTYPES in data.js)
const CONTENT_TYPES = [
  { id: 'text', name: 'Bài đọc', icon: 'reader', color: '#2563eb' },
  { id: 'audio', name: 'Sách nói', icon: 'headphones', color: '#7c3aed' },
  { id: 'video', name: 'Video', icon: 'video', color: '#ea580c' },
  { id: 'exercises', name: 'Bài tập luyện tập', icon: 'puzzle', color: '#16a34a' },
];

const GRADES = ['Lớp 1', 'Lớp 2', 'Lớp 3', 'Lớp 4', 'Lớp 5'];
const SUBJECTS = ['Toán', 'Tiếng Việt', 'Tự nhiên & Xã hội'];
const VOICES = ['Nova (Nữ trẻ)', 'Shimmer', 'Echo', 'Fable', 'Onyx', 'Alloy'];
const VIDEO_STYLES = ['Hoạt hình minh hoạ', 'Minh hoạ động', 'Phim hoạt hình 3D'];

// Selection helpers
const sel = (id) => selected[id];
const toggleType = (id) => {
  if (phase.value === 'setup') selected[id] = !selected[id];
};
const toggleOpen = (id) => {
  open.value = open.value === id ? null : id;
};

const addFiles = (list) => {
  for (const f of list) {
    files.value.push({ name: f.name, isImg: (f.type || '').startsWith('image/') });
  }
};

const removeFile = (i) => {
  files.value.splice(i, 1);
};

// Computed
const totalCau = computed(() => {
  return (counts.chon || 0) + (counts.sx || 0) + (counts.noi || 0);
});

const orderedResults = computed(() => CONTENT_TYPES.filter((t) => sel(t.id)));

const setupSummary = computed(() => `${cfg.subject} · ${cfg.grade} · ${totalCau.value} câu hỏi`);

const canGenerate = computed(() => {
  return (content.value.trim().length > 0 || files.value.length > 0 || selectedFiles.value.length > 0) && orderedResults.value.length > 0;
});

const getOptionText = (opt) => {
  if (typeof opt === 'string') return opt;
  if (typeof opt === 'object') {
    return opt.noi_dung || opt.value || opt.text || opt.answer_val || '';
  }
  return '';
};

const handleFileSelect = (event) => {
  const fileList = Array.from(event.target.files || []);
  selectedFiles.value = fileList.filter(f => {
    const isValid = f.size <= 15 * 1024 * 1024;
    if (!isValid) {
      toast.error(`${f.name} vượt quá 15MB`);
    }
    return isValid;
  });
};

const uploadFiles = async () => {
  const urls = [];
  for (const file of selectedFiles.value) {
    const formData = new FormData();
    formData.append('file', file);
    try {
      const response = await axios.post(route('lessons.json.uploadMultiFile'), formData);
      if (response.data.success && response.data.url) {
        urls.push(response.data.url);
      }
    } catch (error) {
      toast.error(`Lỗi upload ${file.name}`);
      throw error;
    }
  }
  return urls;
};

const pollTaskStatus = (bundleId) => {
  if (!bundleId) return;

  // Set initial generating state for all selected types
  if (sel('text')) { statusMap.text = 'generating'; progressMap.text = 0; }
  if (sel('audio')) { statusMap.audio = 'generating'; progressMap.audio = 0; }
  if (sel('video')) { statusMap.video = 'generating'; progressMap.video = 0; }

  const interval = setInterval(async () => {
    try {
      const response = await axios.get(route('lessons.json.getBundleStatus'), {
        params: { bundle_id: bundleId }
      });

      const bundle = response.data;

      // Update progress and parse each child result
      if (bundle.children) {
        // Parse text/baidoc
        if (bundle.children.baidoc) {
          const baidoc = bundle.children.baidoc;
          if (baidoc.status === 'done' && statusMap.text !== 'done') {
            const textData = {
              content: baidoc.result_text || '',
              html: baidoc.result_text || '',
              title: cfg.grade + ' - ' + cfg.subject,
            };
            results.value.set('text', textData);
            statusMap.text = 'done';
            progressMap.text = 100;
            toast.success('✓ Tạo bài đọc thành công!');
          } else if (baidoc.status === 'processing') {
            progressMap.text = baidoc.progress || 50;
          } else if (baidoc.status === 'failed' && statusMap.text !== 'error') {
            statusMap.text = 'error';
            toast.error(`Lỗi tạo bài đọc: ${baidoc.error}`);
          }
        }

        // Parse audio/sachnoi
        if (bundle.children.sachnoi) {
          const sachnoi = bundle.children.sachnoi;
          if (sachnoi.status === 'done' && statusMap.audio !== 'done') {
            results.value.set('audio', { result_url: sachnoi.result_url });
            statusMap.audio = 'done';
            progressMap.audio = 100;
            toast.success('✓ Tạo sách nói thành công!');
          } else if (sachnoi.status === 'processing') {
            progressMap.audio = sachnoi.progress || 50;
          } else if (sachnoi.status === 'failed' && statusMap.audio !== 'error') {
            statusMap.audio = 'error';
            toast.error(`Lỗi tạo sách nói: ${sachnoi.error}`);
          }
        }

        // Parse video
        if (bundle.children.video) {
          const video = bundle.children.video;
          if (video.status === 'done' && statusMap.video !== 'done') {
            results.value.set('video', { result_url: video.result_url });
            statusMap.video = 'done';
            progressMap.video = 100;
            toast.success('✓ Tạo video thành công!');
          } else if (video.status === 'processing') {
            progressMap.video = video.progress || 50;
          } else if (video.status === 'failed' && statusMap.video !== 'error') {
            statusMap.video = 'error';
            toast.error(`Lỗi tạo video: ${video.error}`);
          }
        }

        // Parse exercises/baitap_giao
        if (bundle.children.baitap_giao) {
          const exercises = bundle.children.baitap_giao;
          if (exercises.status === 'done' && !quizResult.value) {
            if (exercises.result_json && exercises.result_json.giao) {
              quizResult.value = {
                chon: [],
                sx: [],
                noi: [],
              };
              exercises.result_json.giao.forEach(levelGroup => {
                levelGroup.questions?.forEach(q => {
                  if (q.kind === 'chon') quizResult.value.chon.push(q);
                  else if (q.kind === 'sx') quizResult.value.sx.push(q);
                  else if (q.kind === 'noi') quizResult.value.noi.push(q);
                });
              });
              toast.success('✓ Tạo câu hỏi thành công!');
            }
          }
        }
      }

      // Check if all tasks are complete
      const allDone = [
        !sel('text') || statusMap.text === 'done',
        !sel('audio') || statusMap.audio === 'done',
        !sel('video') || statusMap.video === 'done',
        !sel('exercises') || !!quizResult.value,
      ].every(v => v);

      const hasErrors = [
        !sel('text') || statusMap.text !== 'error',
        !sel('audio') || statusMap.audio !== 'error',
        !sel('video') || statusMap.video !== 'error',
      ].some(v => !v);

      if (allDone) {
        clearInterval(interval);
        loading.value = false;
        phase.value = 'ready';
        generationStatus.value = '✓ Hoàn thành!';
        await onGenerationComplete();
        setTimeout(() => {
          generationStatus.value = '';
        }, 2000);
      } else if (hasErrors) {
        clearInterval(interval);
        loading.value = false;
      }
    } catch (error) {
      console.error('Bundle poll error:', error);
    }
  }, 3000);

  pollingIntervals.value.set('bundle', interval);
};

const generateLesson = async () => {
  if (orderedResults.value.length === 0) {
    toast.error('Vui lòng chọn ít nhất một loại bài giảng');
    return;
  }

  if ((sel('text') || sel('audio')) && !content.value.trim() && selectedFiles.value.length === 0) {
    toast.error('Vui lòng nhập nội dung hoặc chọn file cho bài đọc/sách nói');
    return;
  }

  loading.value = true;
  results.value.clear();
  quizResult.value = null;
  generationStatus.value = '⏳ Đang tạo bài giảng...';

  // Reset progress
  progressMap.text = 0;
  progressMap.audio = 0;
  progressMap.video = 0;
  statusMap.text = null;
  statusMap.audio = null;
  statusMap.video = null;

  try {
    // Upload files if needed
    let contentUrls = [];
    if (selectedFiles.value.length > 0) {
      contentUrls = await uploadFiles();
    }

    // Map voice name to API voice code
    const voiceMap = {
      'nova (nữ trẻ)': 'hn_female_ngochuyen_full_48k-fhg',
      'shimmer': 'hn_female_shimmer',
      'echo': 'hn_male_echo',
      'fable': 'hn_male_fable',
      'onyx': 'hn_male_onyx',
      'alloy': 'hn_male_alloy',
    };
    const voiceKey = cfg.voice.toLowerCase();
    const voiceCode = voiceMap[voiceKey] || 'hn_female_ngochuyen_full_48k-fhg';

    // Build bundle payload with all types
    const types = [];
    if (sel('text')) types.push('baidoc');
    if (sel('audio')) types.push('sachnoi');
    if (sel('video')) types.push('video');
    if (sel('exercises')) {
      types.push('baitap_giao');
      types.push('baitap_chung');
    }

    // Map grade/subject to expected format
    const gradeMatch = cfg.grade.match(/\d+/);
    const gradeNum = gradeMatch ? gradeMatch[0] : '1';

    const bundlePayload = {
      content_text: content.value,
      content_urls: contentUrls.length > 0 ? contentUrls : [],
      lop: cfg.grade,
      mon: cfg.subject,
      voice: voiceCode,
      types: types,
    };

    // Add level_mix for exercises if selected
    if (sel('exercises')) {
      bundlePayload.level_mix = {
        'Dễ': counts.chon,
        'Trung bình': counts.sx,
        'Khó': counts.noi,
      };
    }

    const bundleResponse = await axios.post(route('lessons.json.generateBundle'), bundlePayload);

    if (bundleResponse.data.bundle_id) {
      bundleIdRef.value = bundleResponse.data.bundle_id;
      generationStatus.value = `⏳ Đang tạo ${types.length} loại nội dung...`;
      pollTaskStatus(bundleResponse.data.bundle_id);
    } else {
      throw new Error('Không nhận được bundle_id từ API');
    }
  } catch (error) {
    console.error('Generate lesson error:', error);
    generationStatus.value = '✗ Lỗi tạo bài giảng';
    const errorMsg = error?.response?.data?.message || error?.message || 'Vui lòng thử lại';
    toast.error('Lỗi tạo bài giảng: ' + errorMsg);
    loading.value = false;
    pollingIntervals.value.forEach(interval => clearInterval(interval));
    pollingIntervals.value.clear();
  }
};

const bundleIdRef = ref(null);

const saveGeneratedContent = async () => {
  const practice_id = getQueryParam('practice_id');
  if (!practice_id || !bundleIdRef.value) return;

  try {
    // Save all bundle results (text, audio, video) through single endpoint
    const saveResponse = await axios.post(route('lessons.json.saveBundleResult'), {
      practice_id: practice_id,
      bundle_id: bundleIdRef.value,
      app_id: getQueryParam('app_id'),
      book_id: getQueryParam('book_id'),
      week_id: getQueryParam('week_id'),
    });

    if (saveResponse.data.success) {
      toast.success('✓ Nội dung bài giảng đã lưu vào nháp');
    }
  } catch (error) {
    console.error('Save content error:', error);
    toast.error('Lỗi khi lưu nội dung');
  }
};

const onGenerationComplete = async () => {
  await saveGeneratedContent();

  // Open history modal after save
  setTimeout(() => {
    if (historyModal && historyModal.value && historyModal.value.openModal) {
      historyModal.value.openModal();
    }
  }, 800);
};

const handleSaveQuestions = async () => {
  toast.success('Lưu thành công!');
};

const startEdit = (type, idx) => {
  editingQuestion.value = { type, index: idx };
};

const cancelEdit = () => {
  editingQuestion.value = null;
};

const deleteQuestion = (type, idx) => {
  if (quizResult.value && quizResult.value[type]) {
    quizResult.value[type].splice(idx, 1);
    // Remove from selected if exists
    if (selectedByType.value[type]) {
      selectedByType.value[type].delete(`${type}-${idx}`);
    }
  }
};

const toggleSelectQuestion = (type, idx) => {
  if (!selectedByType.value[type]) {
    selectedByType.value[type] = new Set();
  }
  const key = `${type}-${idx}`;
  if (selectedByType.value[type].has(key)) {
    selectedByType.value[type].delete(key);
  } else {
    selectedByType.value[type].add(key);
  }
};

const selectAllOfType = (type) => {
  if (!quizResult.value || !quizResult.value[type]) return;

  if (!selectedByType.value[type]) {
    selectedByType.value[type] = new Set();
  }

  const questions = quizResult.value[type];
  if (selectedByType.value[type].size === questions.length) {
    selectedByType.value[type].clear();
  } else {
    selectedByType.value[type].clear();
    questions.forEach((_, idx) => {
      selectedByType.value[type].add(`${type}-${idx}`);
    });
  }
};

const isQuestionSelected = (type, idx) => {
  return selectedByType.value[type]?.has(`${type}-${idx}`) || false;
};

const startEditText = () => {
  if (results.value.get('text')) {
    textEditorContent.value = results.value.get('text')?.content || results.value.get('text')?.html || '';
    editingTextContent.value = true;
  }
};

const cancelEditText = () => {
  editingTextContent.value = false;
  textEditorContent.value = '';
};

const saveEditText = () => {
  if (results.value.get('text')) {
    results.value.get('text').content = textEditorContent.value;
    editingTextContent.value = false;
    toast.success('✓ Nội dung bài đọc đã cập nhật');
  }
};
</script>

<template>
  <Head title="Tạo bài giảng" />

  <SchoolLayout>
    <div class="ai-flow">
      <div class="page">
        <!-- Header -->
        <div class="flow-head">
          <Link :href="route('lessons.index', { app_id: props.app_id || getQueryParam('app_id'), book_id: props.book_id || getQueryParam('book_id'), week_id: props.week_id || getQueryParam('week_id') })" class="flow-back">
            <Icon name="back" :size="16" />Quay lại
          </Link>
          <div class="flow-spacer"></div>
          <button class="btn" @click="historyModal && historyModal.openModal()">
            <Icon name="history" :size="16" />Lịch sử
          </button>
        </div>

        <h1 class="page-title" style="margin-bottom: 18px">Tạo nội dung bằng AI</h1>

        <!-- Content type selection - matching AiCreate style -->
        <div class="type-row">
          <div
            v-for="t in CONTENT_TYPES"
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

        <!-- Main accordion sections -->
        <div class="acc">
          <!-- Section 1: Nguồn & thiết lập -->
          <div class="acc-item" :class="{ open: open === 'setup', done: phase === 'ready' }">
            <button class="acc-head" @click="toggleOpen('setup')">
              <span class="acc-dot">
                <Icon v-if="phase === 'ready' && open !== 'setup'" name="check" :size="16" :stroke="3" />
                <template v-else>1</template>
              </span>
              <div class="acc-head-main">
                <div class="acc-title">Nguồn &amp; thiết lập</div>
                <div v-if="open !== 'setup'" class="acc-summary">{{ setupSummary }}</div>
                <div v-else class="acc-summary alt">Nội dung, mục độ và tùy chọn tạo bài</div>
              </div>
              <span v-if="open !== 'setup' && phase === 'ready'" class="badge badge-done"><Icon name="check" :size="12" :stroke="3" />Đã tạo</span>
              <span class="acc-caret"><Icon name="chevDown" :size="18" /></span>
            </button>

            <div v-if="open === 'setup'" class="acc-body">
              <p class="sec-sub">Nhập nội dung bài học (hoặc tải file), chọn số lượng câu hỏi theo loại.</p>

              <!-- Content & Config Grid - matching AiCreate order -->
              <div class="setup-grid">
                <div class="full">
                  <label class="lbl-sm">Nội dung / chủ đề bài học</label>
                  <textarea
                    v-model="content"
                    class="ta"
                    placeholder="Nhập nội dung bài đọc, đoạn văn, hoặc chủ đề…"
                  ></textarea>
                </div>

                <div>
                  <label class="lbl-sm">Lớp</label>
                  <select v-model="cfg.grade" class="ctrl">
                    <option v-for="g in GRADES" :key="g">{{ g }}</option>
                  </select>
                </div>

                <div>
                  <label class="lbl-sm">Môn</label>
                  <select v-model="cfg.subject" class="ctrl">
                    <option v-for="s in SUBJECTS" :key="s">{{ s }}</option>
                  </select>
                </div>

                <div>
                  <label class="lbl-sm" :style="{ opacity: sel('audio') ? 1 : 0.4 }">Giọng nói</label>
                  <select v-model="cfg.voice" class="ctrl" :disabled="!sel('audio')" :style="{ opacity: sel('audio') ? 1 : 0.5 }">
                    <option v-for="v in VOICES" :key="v">{{ v }}</option>
                  </select>
                </div>

                <div>
                  <label class="lbl-sm" :style="{ opacity: sel('video') ? 1 : 0.4 }">Phong cách video</label>
                  <select v-model="cfg.vstyle" class="ctrl" :disabled="!sel('video')" :style="{ opacity: sel('video') ? 1 : 0.5 }">
                    <option v-for="s in VIDEO_STYLES" :key="s">{{ s }}</option>
                  </select>
                </div>
              </div>

              <!-- File upload -->
              <div class="upload-mini">
                <span class="or">Hoặc tải file:</span>
                <label class="btn">
                  <Icon name="upload2" :size="15" />Chọn file (Ảnh, PDF, Word, Excel)
                  <input
                    type="file"
                    multiple
                    accept="image/*,.pdf,.doc,.docx,.xls,.xlsx"
                    style="display: none"
                    @change="(e) => { addFiles(e.target.files); e.target.value = ''; }"
                  />
                </label>
                <span class="muted" style="font-size: 13px">Ảnh, PDF, Word, Excel · tối đa 15MB</span>
              </div>

              <div v-if="files.length" class="file-pills">
                <span v-for="(f, i) in files" :key="i" class="file-pill">
                  <Icon :name="f.isImg ? 'image' : 'fileSpread'" :size="15" />{{ f.name }}
                  <span class="x" @click="removeFile(i)"><Icon name="x" :size="13" /></span>
                </span>
              </div>

              <!-- Question quantities -->
              <div class="qty-box">
                <div class="opt-label">Số lượng câu hỏi theo loại</div>
                <div class="lvl-rows">
                  <div class="lvl-row" :class="{ on: counts.chon > 0 }">
                    <div class="lr-pick">
                      <span class="bai-rowname">Chọn đáp án</span>
                    </div>
                    <div class="lr-qty">
                      <span class="muted">Số câu</span>
                      <div class="qty-control">
                        <button @click="counts.chon = Math.max(0, counts.chon - 1)" class="qty-btn">−</button>
                        <input v-model.number="counts.chon" type="number" min="0" max="50" class="qty-input" />
                        <button @click="counts.chon++" class="qty-btn">+</button>
                      </div>
                    </div>
                  </div>
                  <div class="lvl-row" :class="{ on: counts.sx > 0 }">
                    <div class="lr-pick">
                      <span class="bai-rowname">Sắp xếp</span>
                    </div>
                    <div class="lr-qty">
                      <span class="muted">Số câu</span>
                      <div class="qty-control">
                        <button @click="counts.sx = Math.max(0, counts.sx - 1)" class="qty-btn">−</button>
                        <input v-model.number="counts.sx" type="number" min="0" max="50" class="qty-input" />
                        <button @click="counts.sx++" class="qty-btn">+</button>
                      </div>
                    </div>
                  </div>
                  <div class="lvl-row" :class="{ on: counts.noi > 0 }">
                    <div class="lr-pick">
                      <span class="bai-rowname">Nối</span>
                    </div>
                    <div class="lr-qty">
                      <span class="muted">Số câu</span>
                      <div class="qty-control">
                        <button @click="counts.noi = Math.max(0, counts.noi - 1)" class="qty-btn">−</button>
                        <input v-model.number="counts.noi" type="number" min="0" max="50" class="qty-input" />
                        <button @click="counts.noi++" class="qty-btn">+</button>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="lvl-total">Tổng: <b>{{ totalCau }}</b> câu hỏi</div>
              </div>

              <!-- Action -->
              <div class="acc-actions">
                <span class="foot-info">Sẽ tạo <b>{{ totalCau }}</b> câu hỏi{{ sel('text') ? ' + Bài đọc' : '' }}{{ sel('audio') ? ' + Sách nói' : '' }}{{ sel('video') ? ' + Video' : '' }}</span>
                <button
                  class="btn btn-ai btn-lg"
                  :disabled="!canGenerate"
                  @click="generateLesson"
                >
                  <Icon name="sparkles" :size="16" :fill="true" />
                  Tạo nội dung
                </button>
              </div>

              <!-- Status message -->
              <div v-if="generationStatus" class="status-msg" :class="{ 'status-success': generationStatus.includes('✓'), 'status-error': generationStatus.includes('✗') }">
                {{ generationStatus }}
              </div>
            </div>
          </div>

          <!-- Section 2+: Results - matching AiCreate loop -->
          <template v-if="phase === 'ready'">
            <div
              v-for="(t, i) in orderedResults"
              :key="t.id"
              class="acc-item"
              :class="{ open: open === t.id, done: true }"
            >
              <button class="acc-head" @click="toggleOpen(t.id)">
                <span class="acc-dot">
                  <Icon name="check" :size="16" :stroke="3" />
                </span>
                <div class="acc-head-main">
                  <div class="acc-title">{{ t.name }}</div>
                  <div v-if="open !== t.id" class="acc-summary">
                    <template v-if="t.id === 'exercises' && quizResult">
                      {{ (quizResult.chon?.length || 0) + (quizResult.sx?.length || 0) + (quizResult.noi?.length || 0) }} câu hỏi
                    </template>
                    <template v-else-if="t.id === 'text'">1 bài đọc chung</template>
                    <template v-else-if="t.id === 'audio'">1 sách nói · giọng {{ cfg.voice.split(' (')[0] }}</template>
                  </div>
                </div>
                <span class="badge badge-done"><Icon name="check" :size="12" :stroke="3" />Đã tạo</span>
                <span class="acc-caret"><Icon name="chevDown" :size="18" /></span>
              </button>

              <div v-if="open === t.id" class="acc-body">
                <!-- Exercises Section -->
                <template v-if="t.id === 'exercises' && quizResult">
                  <!-- Tabs -->
                  <div class="result-tabs">
                    <button
                      v-for="tab in ['chon', 'sx', 'noi']"
                      :key="tab"
                      @click="selectedQuestionTab = tab"
                      :class="{ active: selectedQuestionTab === tab }"
                      class="result-tab"
                    >
                      {{ tab === 'chon' ? 'Chọn' : tab === 'sx' ? 'Sắp xếp' : 'Nối' }} ({{ quizResult[tab]?.length || 0 }})
                    </button>
                  </div>

                  <!-- Select All Bar -->
                  <div class="select-bar">
                    <input type="checkbox" :checked="selectedByType[selectedQuestionTab]?.size === quizResult[selectedQuestionTab]?.length" @change="selectAllOfType(selectedQuestionTab)" />
                    <span>Chọn tất cả ({{ selectedByType[selectedQuestionTab]?.size || 0 }}/{{ quizResult[selectedQuestionTab]?.length || 0 }})</span>
                  </div>

                  <!-- Questions List -->
                  <div class="questions-list">
                    <template v-if="selectedQuestionTab === 'chon'">
                      <div v-for="(q, idx) in quizResult.chon" :key="`chon-${idx}`" class="question-item" :class="{ selected: isQuestionSelected('chon', idx) }">
                        <input type="checkbox" :checked="isQuestionSelected('chon', idx)" @change="toggleSelectQuestion('chon', idx)" />
                        <div class="q-num">{{ idx + 1 }}</div>
                        <div class="q-content">
                          <p class="q-text">{{ q.cau_hoi }}</p>
                          <div class="q-options">
                            <div v-for="(opt, optIdx) in q.options" :key="optIdx" class="option" :class="{ correct: q.dap_an_dung === (opt.key || String.fromCharCode(65 + optIdx)) }">
                              {{ opt.key || String.fromCharCode(65 + optIdx) }}. {{ getOptionText(opt) }}
                            </div>
                          </div>
                          <div class="q-actions">
                            <button @click="startEdit('chon', idx)" class="action-btn edit-btn">✏️ Sửa</button>
                            <button @click="deleteQuestion('chon', idx)" class="action-btn delete-btn">🗑️ Xóa</button>
                          </div>
                        </div>
                      </div>
                    </template>

                    <template v-else-if="selectedQuestionTab === 'sx'">
                      <div v-for="(q, idx) in quizResult.sx" :key="`sx-${idx}`" class="question-item" :class="{ selected: isQuestionSelected('sx', idx) }">
                        <input type="checkbox" :checked="isQuestionSelected('sx', idx)" @change="toggleSelectQuestion('sx', idx)" />
                        <div class="q-num">{{ idx + 1 }}</div>
                        <div class="q-content">
                          <p class="q-text">{{ q.cau_hoi }}</p>
                          <div class="q-items">
                            <div v-for="(item, i) in (q.items || q.cot_a || q.options || [])" :key="i" class="item">
                              {{ i + 1 }}. {{ typeof item === 'string' ? item : item.noi_dung || item.value }}
                            </div>
                          </div>
                          <div class="q-actions">
                            <button @click="startEdit('sx', idx)" class="action-btn edit-btn">✏️ Sửa</button>
                            <button @click="deleteQuestion('sx', idx)" class="action-btn delete-btn">🗑️ Xóa</button>
                          </div>
                        </div>
                      </div>
                    </template>

                    <template v-else-if="selectedQuestionTab === 'noi'">
                      <div v-for="(q, idx) in quizResult.noi" :key="`noi-${idx}`" class="question-item" :class="{ selected: isQuestionSelected('noi', idx) }">
                        <input type="checkbox" :checked="isQuestionSelected('noi', idx)" @change="toggleSelectQuestion('noi', idx)" />
                        <div class="q-num">{{ idx + 1 }}</div>
                        <div class="q-content">
                          <p class="q-text">{{ q.cau_hoi }}</p>
                          <div class="q-columns">
                            <div class="column">
                              <div class="col-label">Cột A</div>
                              <div v-for="(item, i) in q.cot_a" :key="`a-${i}`" class="item">
                                {{ i + 1 }}. {{ typeof item === 'string' ? item : item.noi_dung || item.value }}
                              </div>
                            </div>
                            <div class="column">
                              <div class="col-label">Cột B</div>
                              <div v-for="(item, i) in q.cot_b" :key="`b-${i}`" class="item">
                                {{ i + 1 }}. {{ typeof item === 'string' ? item : item.noi_dung || item.value }}
                              </div>
                            </div>
                          </div>
                          <div class="q-actions">
                            <button @click="startEdit('noi', idx)" class="action-btn edit-btn">✏️ Sửa</button>
                            <button @click="deleteQuestion('noi', idx)" class="action-btn delete-btn">🗑️ Xóa</button>
                          </div>
                        </div>
                      </div>
                    </template>
                  </div>

                  <!-- Save button -->
                  <div class="acc-actions">
                    <button class="btn btn-primary btn-lg" @click="handleSaveQuestions" :disabled="loading">
                      <Icon name="save" :size="16" />
                      {{ loading ? 'Đang lưu...' : 'Lưu bài giảng' }}
                    </button>
                  </div>
                </template>

                <!-- Text Section -->
                <template v-else-if="t.id === 'text'">
                  <template v-if="statusMap.text === 'generating'">
                    <p class="sec-sub">Bài đọc đang được tạo...</p>
                    <div class="gen-item">
                      <div class="gi-ic" style="background: var(--blue-600); color: #fff">
                        <span class="spin"></span>
                      </div>
                      <div class="gi-name">Bài đọc</div>
                      <div class="gi-status" style="color: var(--blue-600)">{{ progressMap.text }}%</div>
                    </div>
                    <div class="bar"><i :style="{ width: progressMap.text + '%' }"></i></div>
                  </template>
                  <template v-else-if="statusMap.text === 'done' && results.value && results.value.get('text')">
                    <p class="sec-sub">Một bài đọc chung do AI tạo</p>

                    <!-- Reading content with editor -->
                    <div style="background: #fff; border: 1px solid var(--line); border-radius: var(--r); overflow: hidden; margin-bottom: 1rem">
                      <!-- Header -->
                      <div style="padding: 1.5rem; background: #f7f9fc; border-bottom: 1px solid var(--line)">
                        <p style="font-size: 12.5px; color: var(--muted); margin: 0 0 0.5rem">📄 Phiên bản 1 (mới nhất) · {{ new Date().toLocaleString('vi-VN') }}</p>
                        <h3 style="font-size: 20px; font-weight: 700; margin: 0.5rem 0; color: var(--ink)">{{ results.value && results.value.get('text')?.title || 'Bài đọc chung' }}</h3>
                      </div>

                      <!-- Content area -->
                      <div style="padding: 1.5rem">
                        <!-- Editor view -->
                        <template v-if="editingTextContent">
                          <Editor
                            v-model="textEditorContent"
                            :api-key="apiKey"
                            :init="editorConfig"
                          />
                        </template>

                        <!-- Read-only view -->
                        <template v-else>
                          <div style="color: var(--ink-soft); line-height: 1.8; font-size: 15px; word-break: break-word">
                            <div
                              v-if="results.value && (results.value.get('text')?.content || results.value.get('text')?.html)"
                              v-html="results.value && (results.value.get('text')?.content || results.value.get('text')?.html)"
                              class="prose-content"
                            ></div>
                            <div v-else style="text-align: center; padding: 2rem; color: var(--muted)">
                              <p>Nội dung bài đọc</p>
                              <p style="font-size: 13px">Đang tải dữ liệu...</p>
                            </div>
                          </div>
                        </template>
                      </div>
                    </div>

                    <!-- Action buttons -->
                    <div style="display: flex; gap: 10px; margin-bottom: 1rem; flex-wrap: wrap">
                      <template v-if="editingTextContent">
                        <button class="btn" style="background: #f3f4f6; color: var(--ink-soft)" @click="cancelEditText">
                          <Icon name="x" :size="15" />Hủy
                        </button>
                        <button class="btn btn-primary btn-lg" @click="saveEditText">
                          <Icon name="save" :size="16" />Lưu thay đổi
                        </button>
                      </template>
                      <template v-else>
                        <button class="btn" style="background: #f3f4f6; color: var(--ink-soft)" @click="startEditText">
                          <Icon name="edit" :size="15" />Sửa nội dung
                        </button>
                        <button class="btn" style="background: #f3f4f6; color: var(--ink-soft)">
                          <Icon name="refresh" :size="15" />Tạo lại
                        </button>
                        <button class="btn btn-primary btn-lg" style="margin-left: auto">
                          <Icon name="save" :size="16" />Lưu bài đọc
                        </button>
                      </template>
                    </div>
                  </template>
                  <template v-else>
                    <p class="sec-sub">Bài đọc chưa được tạo.</p>
                  </template>
                </template>

                <!-- Audio Section -->
                <template v-else-if="t.id === 'audio'">
                  <template v-if="statusMap.audio === 'generating'">
                    <p class="sec-sub">Sách nói đang được tạo...</p>
                    <div class="gen-item">
                      <div class="gi-ic" style="background: #7c3aed; color: #fff">
                        <span class="spin"></span>
                      </div>
                      <div class="gi-name">Sách nói</div>
                      <div class="gi-status" style="color: #7c3aed">{{ progressMap.audio }}%</div>
                    </div>
                    <div class="bar"><i :style="{ width: progressMap.audio + '%' }"></i></div>
                  </template>
                  <template v-else-if="statusMap.audio === 'done' && results.value && results.value.get('audio')">
                    <p class="sec-sub">Một sách nói chung do AI tạo</p>

                    <!-- Audio player display -->
                    <div style="background: #fff; border: 1px solid var(--line); border-radius: var(--r); padding: 1.5rem; margin-bottom: 1rem">
                      <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--line)">
                        <p style="font-size: 12.5px; color: var(--muted); margin: 0 0 0.5rem">🔊 Phiên bản 1 (mới nhất) · {{ new Date().toLocaleString('vi-VN') }}</p>
                        <h3 style="font-size: 18px; font-weight: 700; margin: 0.5rem 0; color: var(--ink)">Sách nói · Giọng {{ cfg.voice.split(' (')[0] }}</h3>
                      </div>
                      <div style="display: flex; flex-direction: column; gap: 1rem">
                        <p style="font-size: 14px; color: var(--muted)">Âm thanh sách nói đã được tạo thành công với giọng {{ cfg.voice.split(' (')[0] }}.</p>
                        <div v-if="results.value && results.value.get('audio')?.result_url" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: #f7f9fc; border-radius: var(--r)">
                          <button style="width: 40px; height: 40px; border-radius: 50%; background: #7c3aed; color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center">
                            ▶
                          </button>
                          <div style="flex: 1">
                            <p style="font-size: 14px; font-weight: 600; color: var(--ink); margin: 0">Phát âm thanh</p>
                            <p style="font-size: 12px; color: var(--muted); margin: 0.25rem 0 0">Sách nói chung</p>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Action buttons -->
                    <div style="display: flex; gap: 10px; margin-bottom: 1rem; flex-wrap: wrap">
                      <button class="btn" style="background: #f3f4f6; color: var(--ink-soft)">
                        <Icon name="download" :size="15" />Tải xuống
                      </button>
                      <button class="btn" style="background: #f3f4f6; color: var(--ink-soft)">
                        <Icon name="refresh" :size="15" />Tạo lại
                      </button>
                      <button class="btn btn-primary btn-lg" style="margin-left: auto">
                        <Icon name="save" :size="16" />Lưu sách nói
                      </button>
                    </div>
                  </template>
                  <template v-else>
                    <p class="sec-sub">Sách nói chưa được tạo.</p>
                  </template>
                </template>

                <!-- Video Section -->
                <template v-else-if="t.id === 'video'">
                  <template v-if="statusMap.video === 'generating'">
                    <p class="sec-sub">Video đang được tạo...</p>
                    <div class="gen-item">
                      <div class="gi-ic" style="background: #ea580c; color: #fff">
                        <span class="spin"></span>
                      </div>
                      <div class="gi-name">Video</div>
                      <div class="gi-status" style="color: #ea580c">{{ progressMap.video }}%</div>
                    </div>
                    <div class="bar"><i :style="{ width: progressMap.video + '%' }"></i></div>
                  </template>
                  <template v-else-if="statusMap.video === 'done' && results.value && results.value.get('video')">
                    <p class="sec-sub">Một video chung do AI tạo</p>

                    <!-- Video display -->
                    <div style="background: #fff; border: 1px solid var(--line); border-radius: var(--r); padding: 1.5rem; margin-bottom: 1rem">
                      <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--line)">
                        <p style="font-size: 12.5px; color: var(--muted); margin: 0 0 0.5rem">🎬 Phiên bản 1 (mới nhất) · {{ new Date().toLocaleString('vi-VN') }}</p>
                        <h3 style="font-size: 18px; font-weight: 700; margin: 0.5rem 0; color: var(--ink)">Video · {{ cfg.vstyle }}</h3>
                      </div>
                      <div style="display: flex; flex-direction: column; gap: 1rem">
                        <p style="font-size: 14px; color: var(--muted)">Video minh hoạ đã được tạo thành công với phong cách {{ cfg.vstyle }}.</p>
                        <div v-if="results.value && results.value.get('video')?.result_url" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: #f7f9fc; border-radius: var(--r)">
                          <button style="width: 40px; height: 40px; border-radius: 50%; background: #ea580c; color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 18px">
                            ▶
                          </button>
                          <div style="flex: 1">
                            <p style="font-size: 14px; font-weight: 600; color: var(--ink); margin: 0">Xem video</p>
                            <p style="font-size: 12px; color: var(--muted); margin: 0.25rem 0 0">Video chung</p>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Action buttons -->
                    <div style="display: flex; gap: 10px; margin-bottom: 1rem; flex-wrap: wrap">
                      <button class="btn" style="background: #f3f4f6; color: var(--ink-soft)">
                        <Icon name="download" :size="15" />Tải xuống
                      </button>
                      <button class="btn" style="background: #f3f4f6; color: var(--ink-soft)">
                        <Icon name="refresh" :size="15" />Tạo lại
                      </button>
                      <button class="btn btn-primary btn-lg" style="margin-left: auto">
                        <Icon name="save" :size="16" />Lưu video
                      </button>
                    </div>
                  </template>
                  <template v-else>
                    <p class="sec-sub">Video chưa được tạo.</p>
                  </template>
                </template>
              </div>
            </div>
          </template>
        </div>
      </div>

      <LessonDraftHistory
        ref="historyModal"
        :practice-id="parseInt(props.practice_id || getQueryParam('practice_id'))"
      />
    </div>
  </SchoolLayout>
</template>

<style lang="scss" scoped>
@use '../../../css/ai-lesson' as *;

.prose-content {
  h1, h2, h3, h4, h5, h6 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
    font-weight: 700;
    color: var(--ink);
  }

  h1 { font-size: 1.5rem; }
  h2 { font-size: 1.25rem; }
  h3 { font-size: 1.1rem; }

  p {
    margin-bottom: 0.75rem;
  }

  ul, ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
  }

  li {
    margin-bottom: 0.5rem;
  }

  strong {
    font-weight: 700;
    color: var(--ink);
  }

  em {
    font-style: italic;
  }

  a {
    color: var(--blue-600);
    text-decoration: none;

    &:hover {
      text-decoration: underline;
    }
  }

  blockquote {
    border-left: 4px solid var(--blue-600);
    padding-left: 1rem;
    margin: 1rem 0;
    color: var(--muted);
  }

  code {
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
  }

  pre {
    background: #f3f4f6;
    padding: 1rem;
    border-radius: 8px;
    overflow-x: auto;
    margin: 1rem 0;

    code {
      background: none;
      padding: 0;
    }
  }
}

.result-tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid var(--line);

  .result-tab {
    padding: 0.75rem 1rem;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    font-weight: 600;
    color: var(--muted);
    transition: all 0.2s;

    &:hover {
      color: var(--ink);
    }

    &.active {
      border-bottom-color: var(--blue-600);
      color: var(--blue-600);
    }
  }
}

.select-bar {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  background-color: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: var(--r);
  margin-bottom: 1rem;
  font-weight: 500;

  input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
  }
}

.questions-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.question-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  border: 1px solid var(--line);
  border-radius: var(--r);
  background-color: var(--bg);
  align-items: flex-start;
  transition: all 0.2s;

  input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin-top: 0.5rem;
    cursor: pointer;
    flex-shrink: 0;
  }

  &.selected {
    background-color: #eff6ff;
    border-color: #3b82f6;
  }

  .q-num {
    flex-shrink: 0;
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--blue-100);
    color: var(--blue-600);
    border-radius: var(--r);
    font-weight: 700;
  }

  .q-content {
    flex: 1;
    min-width: 0;

    .q-text {
      margin: 0;
      margin-bottom: 0.75rem;
      font-weight: 600;
      color: var(--ink);
    }

    .q-options,
    .q-items,
    .q-columns {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .option {
      padding: 0.5rem;
      background-color: #f3f4f6;
      border-radius: 0.25rem;
      font-size: 0.875rem;
      color: var(--ink-soft);

      &.correct {
        background-color: var(--ok-50);
        color: var(--ok);
        font-weight: 600;
      }
    }

    .item {
      padding: 0.5rem;
      background-color: #f3f4f6;
      border-radius: 0.25rem;
      font-size: 0.875rem;
      color: var(--ink-soft);
    }

    .q-columns {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;

      .column {
        .col-label {
          font-weight: 600;
          font-size: 0.875rem;
          color: var(--muted);
          margin-bottom: 0.5rem;
        }
      }
    }
  }

  .q-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    justify-content: flex-end;

    .action-btn {
      padding: 0.5rem 1rem;
      border: none;
      border-radius: var(--r);
      font-size: 0.875rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      white-space: nowrap;

      &.edit-btn {
        background-color: #fef3c7;
        color: var(--amber);

        &:hover {
          background-color: #fce7a6;
        }
      }

      &.delete-btn {
        background-color: #fee2e2;
        color: #991b1b;

        &:hover {
          background-color: #fecaca;
        }
      }
    }
  }
}

.qty-control {
  display: flex;
  gap: 0.5rem;
  align-items: center;

  .qty-btn {
    flex-shrink: 0;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--line-2);
    background-color: var(--surface);
    border-radius: var(--r);
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;

    &:hover {
      background-color: var(--bg);
      border-color: var(--muted-2);
    }
  }

  .qty-input {
    flex: 1;
    padding: 0.5rem;
    border: 1px solid var(--line-2);
    border-radius: var(--r);
    text-align: center;
    font-weight: 600;

    &:focus {
      outline: none;
      border-color: var(--blue-600);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
  }
}
</style>
