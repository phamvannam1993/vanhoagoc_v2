<script setup>
import { Head } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { ref, computed } from 'vue';
import { useToast } from 'vue-toastification';

const props = defineProps({
  query: {
    type: Object,
    default: () => ({})
  }
});

const toast = useToast();
const app_id = props.query.app_id;
const book_id = props.query.book_id;
const week_id = props.query.week_id;
const practice_id = props.query.practice_id;

const savedQuestions = ref([]);
const selectedQuestions = ref(new Set());
const loading = ref(false);
const generatingQuiz = ref(false);
const editingQuestion = ref(null); // { index }
const editingData = ref(null);

// Form state for question generation
const contentText = ref('');
const selectedFile = ref(null);
const mucDo = ref('Dễ');
const lop = ref('Lớp 1');
const mon = ref('Toán');
const counts = ref({ chon: 2, sx: 2, noi: 1 });

const totalCau = computed(() => {
  return (counts.value.chon || 0) + (counts.value.sx || 0) + (counts.value.noi || 0);
});

const canGenerate = computed(() => {
  return (contentText.value.trim().length > 0 || selectedFile.value) && totalCau >= 5 && totalCau <= 50;
});

const handleFileSelect = (event) => {
  selectedFile.value = event.target.files[0] || null;
};

const generateQuestions = async () => {
  if (!canGenerate.value) {
    toast.warning('Vui lòng kiểm tra nội dung và số lượng câu hỏi');
    return;
  }

  try {
    generatingQuiz.value = true;

    const formData = new FormData();
    formData.append('content', contentText.value);
    formData.append('muc_do', mucDo.value);
    formData.append('lop', lop.value);
    formData.append('mon', mon.value);
    formData.append('counts[chon]', counts.value.chon);
    formData.append('counts[sx]', counts.value.sx);
    formData.append('counts[noi]', counts.value.noi);

    if (selectedFile.value) {
      formData.append('file', selectedFile.value);
      const ext = selectedFile.value.name.split('.').pop().toLowerCase();
      formData.append('input_type', ['pdf', 'doc', 'docx', 'xls', 'xlsx'].includes(ext) ? ext : 'document');
    }

    const response = await axios.post(route('lessons.generateQuestions'), formData);

    if (response.data.data) {
      const { questions } = response.data.data;

      // Add questions to savedQuestions
      if (questions.chon) {
        questions.chon.forEach(q => {
          savedQuestions.value.push({
            type: 'chon',
            question: q,
            saved: false
          });
        });
      }
      if (questions.sx) {
        questions.sx.forEach(q => {
          savedQuestions.value.push({
            type: 'sx',
            question: q,
            saved: false
          });
        });
      }
      if (questions.noi) {
        questions.noi.forEach(q => {
          savedQuestions.value.push({
            type: 'noi',
            question: q,
            saved: false
          });
        });
      }

      toast.success('✓ Tạo câu hỏi thành công!');
      contentText.value = '';
      selectedFile.value = null;
    }
  } catch (error) {
    console.error('Generate error:', error);
    toast.error(error.response?.data?.message || error.message || 'Lỗi tạo câu hỏi');
  } finally {
    generatingQuiz.value = false;
  }
};

const selectAll = computed({
  get: () => selectedQuestions.value.size === savedQuestions.value.length && savedQuestions.value.length > 0,
  set: (value) => {
    if (value) {
      selectedQuestions.value = new Set(savedQuestions.value.map((_, i) => i));
    } else {
      selectedQuestions.value.clear();
    }
  }
});

const toggleQuestion = (index) => {
  if (selectedQuestions.value.has(index)) {
    selectedQuestions.value.delete(index);
  } else {
    selectedQuestions.value.add(index);
  }
};

const handleQuizGenerated = (data) => {
  const { type, question, practiceId } = data;
  // Normalize to ensure proper structure for backend processing
  const normalizedQuestion = normalizeQuestion(question, type);
  savedQuestions.value.push({
    type,
    question: normalizedQuestion,
    practiceId,
    saved: false
  });
  toast.success(`Đã thêm câu hỏi ${type}`);
};

const extractItemText = (item) => {
  if (typeof item === 'string') return item;
  if (typeof item === 'object') {
    return item.noi_dung || item.value || item.text || '';
  }
  return String(item);
};

const normalizeQuestion = (question, type) => {
  const normalized = { ...question };

  // Ensure trich_dan_dap_an is preserved
  if (!normalized.trich_dan_dap_an) {
    normalized.trich_dan_dap_an = '';
  }

  // Ensure options have proper structure for backend processing
  if (normalized.options && Array.isArray(normalized.options)) {
    normalized.options = normalized.options.map((opt, idx) => {
      if (typeof opt === 'string') {
        return { value: opt, noi_dung: opt, key: String.fromCharCode(65 + idx) };
      }
      // Keep object structure but ensure key fields exist
      return {
        ...opt,
        value: opt.value || opt.noi_dung || opt.text || '',
        noi_dung: opt.noi_dung || opt.value || opt.text || '',
        key: opt.key || opt.chu_cai || String.fromCharCode(65 + idx)
      };
    });
  }

  // Normalize cot_a and cot_b - preserve object structure
  if (normalized.cot_a && Array.isArray(normalized.cot_a)) {
    normalized.cot_a = normalized.cot_a.map(item => {
      if (typeof item === 'string') {
        return { value: item, noi_dung: item };
      }
      return {
        ...item,
        value: item.value || item.noi_dung || item.text || '',
        noi_dung: item.noi_dung || item.value || item.text || ''
      };
    });
  }

  if (normalized.cot_b && Array.isArray(normalized.cot_b)) {
    normalized.cot_b = normalized.cot_b.map(item => {
      if (typeof item === 'string') {
        return { value: item, noi_dung: item };
      }
      return {
        ...item,
        value: item.value || item.noi_dung || item.text || '',
        noi_dung: item.noi_dung || item.value || item.text || ''
      };
    });
  }

  return normalized;
};

const saveQuestion = async (index) => {
  const item = savedQuestions.value[index];

  try {
    loading.value = true;

    // Validate required fields
    if (!app_id) {
      toast.error('Thiếu app_id. Vui lòng tải lại trang.');
      loading.value = false;
      return;
    }

    if (!practice_id) {
      toast.error('Thiếu practice_id. Vui lòng tải lại trang.');
      loading.value = false;
      return;
    }

    const payload = {
      type: item.type,
      question: item.question,
      practice_id: item.practiceId || practice_id,
      week_id: week_id || null,
      book_id: book_id || null,
      app_id: parseInt(app_id)
    };

    console.log('Save payload:', payload);
    const response = await axios.post(route('questions.json.saveQuestion'), payload);

    if (response.data.success) {
      item.saved = true;
      toast.success('Câu hỏi đã được lưu');
    } else {
      toast.error(response.data.message || 'Lỗi lưu câu hỏi');
    }
  } catch (error) {
    console.error('Save error:', error);
    console.error('Error response:', error.response?.data);
    toast.error('Lỗi lưu câu hỏi: ' + (error.response?.data?.message || error.message));
  } finally {
    loading.value = false;
  }
};

const deleteQuestion = (index) => {
  savedQuestions.value.splice(index, 1);
  selectedQuestions.value.delete(index);
  toast.info('Đã xóa câu hỏi');
};

const saveSelectedQuestions = async () => {
  const indices = Array.from(selectedQuestions.value).sort((a, b) => b - a);
  if (indices.length === 0) {
    toast.info('Vui lòng chọn ít nhất 1 câu hỏi');
    return;
  }

  try {
    loading.value = true;
    for (const index of indices) {
      if (!savedQuestions.value[index].saved) {
        await saveQuestion(index);
        await new Promise(resolve => setTimeout(resolve, 500));
      }
    }
    toast.success(`Đã lưu ${indices.length} câu hỏi`);
    selectedQuestions.value.clear();
  } catch (error) {
    console.error('Save selected error:', error);
    toast.error('Lỗi lưu câu hỏi');
  } finally {
    loading.value = false;
  }
};

const saveAllQuestions = async () => {
  const unsaved = savedQuestions.value.filter(q => !q.saved);
  if (unsaved.length === 0) {
    toast.info('Tất cả câu hỏi đã được lưu');
    return;
  }

  try {
    loading.value = true;
    for (let i = 0; i < savedQuestions.value.length; i++) {
      if (!savedQuestions.value[i].saved) {
        await saveQuestion(i);
        // Small delay between saves to avoid overload
        await new Promise(resolve => setTimeout(resolve, 500));
      }
    }
    toast.success('Đã lưu tất cả câu hỏi');
  } catch (error) {
    console.error('Save all error:', error);
    toast.error('Lỗi lưu câu hỏi');
  } finally {
    loading.value = false;
  }
};

const isCorrectAnswer = (dapAnDung, optKey) => {
  if (!dapAnDung) return false;
  const answers = dapAnDung.split(',').map(a => a.trim());
  return answers.includes(optKey);
};

const getOptionText = (opt) => {
  if (typeof opt === 'string') return opt;
  if (typeof opt === 'object') {
    // Priority: noi_dung > value > text > answer_val
    return opt.noi_dung || opt.value || opt.text || opt.answer_val || '';
  }
  return '';
};

const goBack = () => {
  window.location = route('questionEditors.index', { app_id, book_id, week_id, practice_id });
};

const startEdit = (index) => {
  const item = savedQuestions.value[index];
  editingQuestion.value = { index };
  editingData.value = JSON.parse(JSON.stringify(item.question)); // Deep copy

  // Ensure tieu_de exists
  if (!editingData.value.tieu_de) {
    editingData.value.tieu_de = '';
  }

  // Ensure trich_dan_dap_an exists
  if (!editingData.value.trich_dan_dap_an) {
    editingData.value.trich_dan_dap_an = '';
  }
};

const cancelEdit = () => {
  editingQuestion.value = null;
  editingData.value = null;
};

const saveEdit = () => {
  if (!editingQuestion.value) return;

  const { index } = editingQuestion.value;
  savedQuestions.value[index].question = editingData.value;

  toast.success('Đã cập nhật câu hỏi');
  cancelEdit();
};

const updateEditOption = (optIndex, field, value) => {
  if (!editingData.value.options) return;
  editingData.value.options[optIndex] = { ...editingData.value.options[optIndex], [field]: value };
};

const updateEditCotA = (itemIndex, value) => {
  if (!editingData.value.cot_a) return;
  const item = editingData.value.cot_a[itemIndex];
  if (typeof item === 'string') {
    editingData.value.cot_a[itemIndex] = value;
  } else if (typeof item === 'object') {
    if (item.noi_dung !== undefined) {
      editingData.value.cot_a[itemIndex].noi_dung = value;
    } else if (item.value !== undefined) {
      editingData.value.cot_a[itemIndex].value = value;
    } else {
      editingData.value.cot_a[itemIndex] = { ...item, value };
    }
  }
};

const updateEditCotB = (itemIndex, value) => {
  if (!editingData.value.cot_b) return;
  const item = editingData.value.cot_b[itemIndex];
  if (typeof item === 'string') {
    editingData.value.cot_b[itemIndex] = value;
  } else if (typeof item === 'object') {
    if (item.noi_dung !== undefined) {
      editingData.value.cot_b[itemIndex].noi_dung = value;
    } else if (item.value !== undefined) {
      editingData.value.cot_b[itemIndex].value = value;
    } else {
      editingData.value.cot_b[itemIndex] = { ...item, value };
    }
  }
};

const updateEditOptionValue = (optIdx, value) => {
  if (!editingData.value.options || !editingData.value.options[optIdx]) return;
  editingData.value.options[optIdx].value = value;
  editingData.value.options[optIdx].noi_dung = value;
};

const updateEditCotAValue = (itemIdx, value) => {
  if (!editingData.value.cot_a || !editingData.value.cot_a[itemIdx]) return;
  editingData.value.cot_a[itemIdx].value = value;
  editingData.value.cot_a[itemIdx].noi_dung = value;
};

const updateEditCotBValue = (itemIdx, value) => {
  if (!editingData.value.cot_b || !editingData.value.cot_b[itemIdx]) return;
  editingData.value.cot_b[itemIdx].value = value;
  editingData.value.cot_b[itemIdx].noi_dung = value;
};
</script>

<template>
  <Head title="Upload Câu hỏi" />

  <SchoolLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">App</h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-4/5 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
          <h1 class="text-[30px] font-bold text-[#2C75E3]">Upload Câu hỏi</h1>
          <img
            @click="goBack"
            class="h-[34px] cursor-pointer"
            src="/images/icon-button-back.png"
            alt="Back"
          />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Generate Panel -->
          <div class="bg-white rounded-lg shadow p-6 space-y-6">
            <h3 class="text-lg font-bold text-gray-800">Tạo câu hỏi</h3>

            <!-- Content Input -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Nội dung câu hỏi</label>
              <textarea
                v-model="contentText"
                placeholder="Nhập nội dung câu hỏi..."
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 h-32"
              />
            </div>

            <!-- File Upload -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Hoặc upload file (PDF, Word, Excel)</label>
              <input
                type="file"
                @change="handleFileSelect"
                accept=".pdf,.doc,.docx,.xls,.xlsx"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
              />
              <p v-if="selectedFile" class="text-xs text-green-600 mt-1">✓ {{ selectedFile.name }}</p>
            </div>

            <!-- Settings Row -->
            <div class="grid grid-cols-4 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Mức độ</label>
                <select v-model="mucDo" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                  <option>Dễ</option>
                  <option>Trung bình</option>
                  <option>Khó</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Lớp</label>
                <input v-model="lop" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Môn</label>
                <input v-model="mon" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
              </div>
              <div></div>
            </div>

            <!-- Question Counts -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="font-semibold text-gray-800 mb-3">Số lượng câu hỏi</h4>
              <div class="grid grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Chọn</label>
                  <input v-model.number="counts.chon" type="number" min="0" max="50" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Sắp xếp</label>
                  <input v-model.number="counts.sx" type="number" min="0" max="50" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nối</label>
                  <input v-model.number="counts.noi" type="number" min="0" max="50" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
                </div>
              </div>
              <p class="text-sm text-gray-600 mt-3">
                <span v-if="totalCau < 5" class="text-red-600">⚠️ Tối thiểu 5 câu hỏi</span>
                <span v-else-if="totalCau > 50" class="text-red-600">⚠️ Tối đa 50 câu hỏi</span>
                <span v-else class="text-green-600">✓ Hợp lệ</span>
              </p>
            </div>

            <!-- Generate Button -->
            <button
              @click="generateQuestions"
              :disabled="!canGenerate || generatingQuiz"
              class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 font-semibold transition text-lg"
            >
              {{ generatingQuiz ? 'Đang tạo...' : 'Tạo câu hỏi' }}
            </button>
          </div>

          <!-- Preview Panel -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-bold text-gray-800">Câu hỏi đã chọn</h3>
              <div class="flex items-center gap-3">
                <label v-if="savedQuestions.length > 0" class="flex items-center gap-2 cursor-pointer">
                  <input type="checkbox" v-model="selectAll" class="w-4 h-4">
                  <span class="text-sm font-semibold text-gray-700">Chọn tất cả</span>
                </label>
                <span class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-sm font-semibold">
                  {{ savedQuestions.length }}
                </span>
              </div>
            </div>

            <div v-if="savedQuestions.length === 0" class="text-center py-8 text-gray-500">
              <p>Chưa có câu hỏi nào được chọn</p>
              <p class="text-sm mt-2">Chọn câu hỏi từ bên trái để thêm vào danh sách</p>
            </div>

            <div v-else class="space-y-4 max-h-[600px] overflow-y-auto">
              <div v-for="(item, index) in savedQuestions" :key="index" class="p-4 border rounded-lg" :class="selectedQuestions.has(index) ? 'bg-blue-50 border-blue-300' : ''">
                <!-- View Mode -->
                <div v-if="!editingQuestion || editingQuestion.index !== index">
                  <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3 flex-1">
                      <input
                        type="checkbox"
                        :checked="selectedQuestions.has(index)"
                        @change="toggleQuestion(index)"
                        class="w-4 h-4 cursor-pointer flex-shrink-0"
                      />
                      <div>
                        <span class="inline-block px-2 py-1 bg-blue-100 text-blue-600 text-xs font-semibold rounded mr-2">
                          {{ item.type === 'chon' ? 'Chọn' : item.type === 'sx' ? 'Sắp xếp' : 'Nối' }}
                        </span>
                        <span v-if="item.saved" class="inline-block px-2 py-1 bg-green-100 text-green-600 text-xs font-semibold rounded">
                          ✓ Đã lưu
                        </span>
                      </div>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                      <button
                        @click="startEdit(index)"
                        class="text-yellow-600 hover:text-yellow-800 font-semibold text-sm"
                      >
                        Sửa
                      </button>
                      <button
                        @click="deleteQuestion(index)"
                        class="text-red-600 hover:text-red-800 font-semibold"
                      >
                        ✕
                      </button>
                    </div>
                  </div>

                  <!-- Content Preview -->
                  <div class="text-sm text-gray-700 mb-3">
                    <p v-if="item.question.tieu_de" class="text-xs text-gray-600 mb-1">{{ item.question.tieu_de }}</p>
                    <h5 class="font-semibold mb-2">{{ item.question.cau_hoi || item.question.tieu_de }}</h5>

                    <!-- Chọn -->
                    <div v-if="item.type === 'chon'" class="ml-4 space-y-1">
                      <div
                        v-for="(opt, optIdx) in item.question.options"
                        :key="optIdx"
                        class="text-xs px-2 py-1 rounded"
                        :class="isCorrectAnswer(item.question.dap_an_dung, opt.key || String.fromCharCode(65 + optIdx)) ? 'bg-green-200 font-bold text-green-800' : ''"
                      >
                        {{ opt.key || String.fromCharCode(65 + optIdx) }}. {{ getOptionText(opt) }}
                      </div>
                    </div>

                    <!-- Sắp xếp -->
                    <div v-else-if="item.type === 'sx'" class="ml-4 space-y-1">
                      <div v-for="(opt, optIdx) in item.question.options" :key="optIdx" class="text-xs">
                        {{ optIdx + 1 }}. {{ getOptionText(opt) }}
                      </div>
                    </div>

                    <!-- Nối -->
                    <div v-else-if="item.type === 'noi'" class="grid grid-cols-2 gap-2 ml-4 text-xs">
                      <div>
                        <span class="font-semibold text-gray-600">Cột A:</span>
                        <div v-for="(it, idx) in item.question.cot_a" :key="idx">{{ idx + 1 }}. {{ getOptionText(it) }}</div>
                      </div>
                      <div>
                        <span class="font-semibold text-gray-600">Cột B:</span>
                        <div v-for="(it, idx) in item.question.cot_b" :key="idx">{{ idx + 1 }}. {{ getOptionText(it) }}</div>
                      </div>
                    </div>
                  </div>

                  <!-- Save Button -->
                  <button
                    v-if="!item.saved"
                    @click="saveQuestion(index)"
                    :disabled="loading"
                    class="w-full px-3 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white text-sm font-semibold rounded transition"
                  >
                    {{ loading ? 'Đang lưu...' : 'Lưu câu hỏi' }}
                  </button>
                </div>

                <!-- Edit Mode -->
                <div v-else class="space-y-3 bg-yellow-50 p-3 rounded border-2 border-yellow-300">
                  <!-- Tiêu đề -->
                  <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tiêu đề (nếu có)</label>
                    <input
                      v-model="editingData.tieu_de"
                      type="text"
                      placeholder="Ví dụ: Chọn đáp án đúng"
                      class="w-full px-2 py-1 text-sm border rounded"
                    />
                  </div>

                  <!-- Câu hỏi -->
                  <div v-if="item.type === 'chon' || item.type === 'sx'">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Câu hỏi</label>
                    <input
                      v-model="editingData.cau_hoi"
                      type="text"
                      class="w-full px-2 py-1 text-sm border rounded"
                    />
                  </div>

                  <!-- Trích dẫn đáp án -->
                  <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Trích dẫn đáp án (nếu có)</label>
                    <textarea
                      v-model="editingData.trich_dan_dap_an"
                      placeholder="Ví dụ: Phép cộng đơn giản: 2+3=5, 4+1=5, ..."
                      class="w-full px-2 py-1 text-sm border rounded"
                      rows="3"
                    ></textarea>
                  </div>

                  <!-- Options for Chọn -->
                  <div v-if="item.type === 'chon'">
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Các lựa chọn</label>
                    <div class="space-y-2 ml-2">
                      <div v-for="(opt, optIdx) in editingData.options" :key="optIdx" class="flex gap-2 items-start">
                        <span class="text-xs font-semibold py-1">{{ String.fromCharCode(65 + optIdx) }}.</span>
                        <input
                          :value="editingData.options[optIdx]?.value || ''"
                          @input="updateEditOptionValue(optIdx, $event.target.value)"
                          type="text"
                          class="flex-1 px-2 py-1 text-xs border rounded"
                        />
                        <label class="flex items-center gap-1 py-1">
                          <input
                            type="checkbox"
                            :checked="isCorrectAnswer(editingData.dap_an_dung, String.fromCharCode(65 + optIdx))"
                            @change="(e) => {
                              const key = String.fromCharCode(65 + optIdx);
                              const answers = editingData.dap_an_dung?.split(',').map(a => a.trim()) || [];
                              if (e.target.checked) {
                                if (!answers.includes(key)) answers.push(key);
                              } else {
                                const idx = answers.indexOf(key);
                                if (idx > -1) answers.splice(idx, 1);
                              }
                              editingData.dap_an_dung = answers.join(', ');
                            }"
                            class="w-3 h-3"
                          />
                          <span class="text-xs">Đúng</span>
                        </label>
                      </div>
                    </div>
                  </div>

                  <!-- Options for Sắp xếp -->
                  <div v-if="item.type === 'sx'">
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Các mục cần sắp xếp</label>
                    <div class="space-y-2 ml-2">
                      <div v-for="(opt, optIdx) in editingData.options" :key="optIdx" class="flex gap-2">
                        <span class="text-xs font-semibold py-1 w-6">{{ optIdx + 1 }}.</span>
                        <input
                          :value="editingData.options[optIdx]?.value || ''"
                          @input="updateEditOptionValue(optIdx, $event.target.value)"
                          type="text"
                          class="flex-1 px-2 py-1 text-xs border rounded"
                        />
                      </div>
                    </div>
                  </div>

                  <!-- Columns for Nối -->
                  <div v-if="item.type === 'noi'" class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-xs font-semibold text-gray-700 mb-2">Cột A</label>
                      <div class="space-y-2">
                        <div v-for="(it, itemIdx) in editingData.cot_a" :key="itemIdx" class="flex gap-1">
                          <span class="text-xs font-semibold py-1 w-5">{{ itemIdx + 1 }}.</span>
                          <input
                            :value="editingData.cot_a[itemIdx]?.value || ''"
                            @input="updateEditCotAValue(itemIdx, $event.target.value)"
                            type="text"
                            class="flex-1 px-2 py-1 text-xs border rounded"
                          />
                        </div>
                      </div>
                    </div>

                    <div>
                      <label class="block text-xs font-semibold text-gray-700 mb-2">Cột B</label>
                      <div class="space-y-2">
                        <div v-for="(it, itemIdx) in editingData.cot_b" :key="itemIdx" class="flex gap-1">
                          <span class="text-xs font-semibold py-1 w-5">{{ itemIdx + 1 }}.</span>
                          <input
                            :value="editingData.cot_b[itemIdx]?.value || ''"
                            @input="updateEditCotBValue(itemIdx, $event.target.value)"
                            type="text"
                            class="flex-1 px-2 py-1 text-xs border rounded"
                          />
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Save/Cancel Buttons -->
                  <div class="flex gap-2 pt-2">
                    <button @click="saveEdit" class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700">Lưu</button>
                    <button @click="cancelEdit" class="px-3 py-1 text-sm bg-gray-400 text-white rounded hover:bg-gray-500">Hủy</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Selection Actions -->
            <div v-if="selectedQuestions.size > 0" class="mt-6 pt-4 border-t space-y-2">
              <button
                @click="saveSelectedQuestions"
                :disabled="loading"
                class="w-full px-6 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold rounded-lg transition"
              >
                {{ loading ? 'Đang lưu...' : `Lưu ${selectedQuestions.size} câu được chọn` }}
              </button>
            </div>

            <!-- Save All Button -->
            <div v-if="savedQuestions.length > 0" class="mt-6 pt-4 border-t">
              <button
                @click="saveAllQuestions"
                :disabled="loading"
                class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-semibold rounded-lg transition"
              >
                {{ loading ? 'Đang lưu...' : `Lưu tất cả (${savedQuestions.filter(q => !q.saved).length})` }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </SchoolLayout>
</template>

<style scoped>
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
