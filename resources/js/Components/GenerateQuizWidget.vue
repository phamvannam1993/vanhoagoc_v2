<script setup>
import { ref, computed } from 'vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
  practiceId: [String, Number],
  weekId: [String, Number],
  bookId: [String, Number],
  appId: [String, Number],
  counts: {
    type: Object,
    default: () => ({ chon: 3, sx: 2, noi: 2 })
  }
});

const emit = defineEmits(['quiz-generated']);

const contentText = ref('');
const selectedFile = ref(null);
const fileError = ref('');
const selectedFiles = ref([]);
const multiFileError = ref('');
const mucDo = ref('Dễ');
const lop = ref('Lớp 1');
const mon = ref('Toán');
const voice = ref('nova');
const voices = [
  { value: 'alloy', label: 'Alloy' },
  { value: 'echo', label: 'Echo' },
  { value: 'fable', label: 'Fable' },
  { value: 'onyx', label: 'Onyx' },
  { value: 'nova', label: 'Nova' },
  { value: 'shimmer', label: 'Shimmer' }
];

const MAX_FILE_SIZE = 15 * 1024 * 1024; // 15MB in bytes
const MAX_TOTAL_SIZE = 50 * 1024 * 1024; // 50MB total
const MAX_FILES = 20;

const loading = ref(false);
const showResults = ref(false);
const quizResult = ref(null);
const activeTab = ref('chon');
const selectedByType = ref({ chon: new Set(), sx: new Set(), noi: new Set() });
const addedQuestions = ref({ chon: [], sx: [], noi: [] }); // Track added questions
const selectedAddedQuestions = ref({ chon: new Set(), sx: new Set(), noi: new Set() }); // Track selected questions in right column
const editingQuestion = ref(null); // { type, index }
const editingData = ref(null);
const editingAddedQuestion = ref(null); // { type, index } for right column editing
const editingAddedData = ref(null);

const totalCau = computed(() => {
  return (props.counts.chon || 0) + (props.counts.sx || 0) + (props.counts.noi || 0);
});

const totalQuestionsCount = computed(() => {
  if (!quizResult.value) return 0;
  return (quizResult.value.chon?.length || 0) +
         (quizResult.value.sx?.length || 0) +
         (quizResult.value.noi?.length || 0);
});

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  fileError.value = '';

  if (!file) {
    selectedFile.value = null;
    return;
  }

  // Validate file size
  if (file.size > MAX_FILE_SIZE) {
    const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
    fileError.value = `File quá lớn (${sizeMB}MB). Tối đa 15MB.`;
    selectedFile.value = null;
    toast.error(fileError.value);
    return;
  }

  selectedFile.value = file;
};

const handleMultiFileUpload = (event) => {
  const files = Array.from(event.target.files || []);
  multiFileError.value = '';

  if (files.length === 0) {
    selectedFiles.value = [];
    return;
  }

  // Validate number of files
  if (files.length > MAX_FILES) {
    multiFileError.value = `Tối đa ${MAX_FILES} file. Bạn chọn ${files.length} file.`;
    selectedFiles.value = [];
    toast.error(multiFileError.value);
    return;
  }

  // Validate individual file sizes and calculate total
  let totalSize = 0;
  const validFiles = [];
  const errors = [];

  for (const file of files) {
    // Check individual file size
    if (file.size > MAX_FILE_SIZE) {
      const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
      errors.push(`${file.name}: ${sizeMB}MB (tối đa 15MB)`);
      continue;
    }
    totalSize += file.size;
    validFiles.push(file);
  }

  // Validate total size
  if (totalSize > MAX_TOTAL_SIZE) {
    const totalMB = (totalSize / (1024 * 1024)).toFixed(2);
    multiFileError.value = `Tổng dung lượng quá lớn (${totalMB}MB). Tối đa 50MB.`;
    toast.error(multiFileError.value);
    selectedFiles.value = [];
    return;
  }

  if (errors.length > 0) {
    multiFileError.value = errors.join(' | ');
    toast.error(multiFileError.value);
    selectedFiles.value = validFiles;
    return;
  }

  selectedFiles.value = validFiles;
  toast.success(`Đã chọn ${validFiles.length} file (${(totalSize / (1024 * 1024)).toFixed(2)}MB)`);
};

const validateInput = () => {
  if (totalCau.value < 5 || totalCau.value > 50) {
    toast.error('Tổng số câu phải từ 5 đến 50.');
    return false;
  }

  // Require either text content OR file upload (single or multiple files)
  if (!contentText.value?.trim() && !selectedFile.value && selectedFiles.value.length === 0) {
    toast.error('Vui lòng nhập nội dung hoặc chọn file.');
    return false;
  }

  return true;
};

const generateQuiz = async () => {
  if (!validateInput()) {
    throw new Error('Vui lòng nhập nội dung hoặc chọn file và nhập số lượng câu hỏi');
  }

  loading.value = true;

  try {
    // Auto-detect input type based on what's provided
    let inputType = 'text';
    const formData = new FormData();

    if (selectedFiles.value.length > 0) {
      // Multiple files upload
      inputType = 'image';

      // Upload each file to S3 and collect URLs
      const contentUrls = [];
      for (const file of selectedFiles.value) {
        const uploadFormData = new FormData();
        uploadFormData.append('file', file);

        try {
          const uploadResponse = await axios.post(route('lessons.json.uploadMultiFile'), uploadFormData, {
            headers: { 'Content-Type': 'multipart/form-data' },
          });

          if (uploadResponse.data.url) {
            contentUrls.push(uploadResponse.data.url);
          }
        } catch (e) {
          const errorMsg = `Lỗi upload file ${file.name}: ${e.response?.data?.message || e.message}`;
          toast.error(errorMsg);
          throw new Error(errorMsg);
        }
      }

      // Add content URLs as JSON array
      formData.append('content_urls', JSON.stringify(contentUrls));
    } else if (selectedFile.value) {
      // Single file upload
      inputType = 'file';
      formData.append('file', selectedFile.value);
    } else {
      // Text input
      inputType = 'text';
      formData.append('content_text', contentText.value);
    }

    formData.append('input_type', inputType);
    formData.append('content_text', contentText.value);
    formData.append('muc_do', mucDo.value);
    formData.append('lop', lop.value);
    formData.append('mon', mon.value);
    formData.append('counts[chon]', props.counts.chon);
    formData.append('counts[sx]', props.counts.sx);
    formData.append('counts[noi]', props.counts.noi);

    console.log('Calling generateQuiz API...');
    const response = await axios.post(route('lessons.json.generateQuiz'), formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    console.log('Quiz API response:', response.data);

    if (response.data.success) {
      quizResult.value = response.data.data;
      showResults.value = true;
      activeTab.value = 'chon';
      toast.success('Tạo câu hỏi thành công!');
      loading.value = false;
      return true;
    } else {
      throw new Error(response.data.message || 'Lỗi tạo câu hỏi');
    }
  } catch (error) {
    loading.value = false;
    const message = error.response?.data?.message || error.message || 'Lỗi tạo câu hỏi. Vui lòng thử lại.';
    toast.error(message);
    throw error;
  }
};

const deleteQuestion = (type, index) => {
  if (quizResult.value[type]) {
    quizResult.value[type].splice(index, 1);
    toast.info('Xóa câu hỏi thành công.');
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

const toggleQuestionSelection = (type, index) => {
  if (selectedByType.value[type].has(index)) {
    selectedByType.value[type].delete(index);
  } else {
    selectedByType.value[type].add(index);
  }
};

const selectAllOfType = (type) => {
  const questions = quizResult.value[type];
  if (!questions) return;

  if (selectedByType.value[type].size === questions.length) {
    selectedByType.value[type].clear();
  } else {
    selectedByType.value[type] = new Set(questions.map((_, i) => i));
  }
};

const addSelectedQuestions = (type) => {
  const questions = quizResult.value[type];
  const indices = Array.from(selectedByType.value[type]).sort((a, b) => a - b);

  let count = 0;
  for (const index of indices) {
    const question = questions[index];
    emit('quiz-generated', {
      type,
      question,
      allQuiz: quizResult.value,
      practiceId: props.practiceId
    });
    // Track added question
    addedQuestions.value[type].push(question);
    count++;
  }

  toast.success(`Đã thêm ${count} câu hỏi`);
  selectedByType.value[type].clear();
};

const selectQuestion = (type, index) => {
  const question = quizResult.value[type][index];
  emit('quiz-generated', {
    type,
    question,
    allQuiz: quizResult.value,
    practiceId: props.practiceId
  });
  // Track added question
  addedQuestions.value[type].push(question);
  toast.success('Câu hỏi đã được chọn!');
};

const startEdit = (type, index) => {
  const question = quizResult.value[type][index];
  editingQuestion.value = { type, index };
  editingData.value = JSON.parse(JSON.stringify(question)); // Deep copy

  // Ensure tieu_de exists even if not in original
  if (!editingData.value.tieu_de) {
    editingData.value.tieu_de = '';
  }
};

const cancelEdit = () => {
  editingQuestion.value = null;
  editingData.value = null;
};

const saveEdit = () => {
  if (!editingQuestion.value) return;

  const { type, index } = editingQuestion.value;
  // Use Object.assign to ensure Vue detects the update
  Object.assign(quizResult.value[type][index], editingData.value);

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

// Functions for editing added questions (right column)
const startEditAddedQuestion = (type, index) => {
  const question = addedQuestions.value[type][index];
  editingAddedQuestion.value = { type, index };
  editingAddedData.value = JSON.parse(JSON.stringify(question)); // Deep copy
};

const cancelEditAddedQuestion = () => {
  editingAddedQuestion.value = null;
  editingAddedData.value = null;
};

const saveEditAddedQuestion = () => {
  if (!editingAddedQuestion.value) return;
  const { type, index } = editingAddedQuestion.value;
  addedQuestions.value[type][index] = editingAddedData.value;
  toast.success('Đã cập nhật câu hỏi');
  cancelEditAddedQuestion();
};

const deleteAddedQuestion = (type, index) => {
  addedQuestions.value[type].splice(index, 1);
  selectedAddedQuestions.value[type].delete(index);
  toast.success('Đã xóa câu hỏi');
};

const toggleAddedQuestionSelection = (type, index) => {
  if (selectedAddedQuestions.value[type].has(index)) {
    selectedAddedQuestions.value[type].delete(index);
  } else {
    selectedAddedQuestions.value[type].add(index);
  }
};

const selectAllAddedOfType = (type) => {
  const totalCount = addedQuestions.value[type].length;
  if (selectedAddedQuestions.value[type].size === totalCount) {
    selectedAddedQuestions.value[type].clear();
  } else {
    selectedAddedQuestions.value[type] = new Set(Array.from({ length: totalCount }, (_, i) => i));
  }
};

const deleteSelectedAddedQuestions = (type) => {
  const indices = Array.from(selectedAddedQuestions.value[type]).sort((a, b) => b - a);
  indices.forEach(idx => addedQuestions.value[type].splice(idx, 1));
  selectedAddedQuestions.value[type].clear();
  toast.success(`Đã xóa ${indices.length} câu hỏi`);
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

  if (!normalized.trich_dan_dap_an) {
    normalized.trich_dan_dap_an = '';
  }

  if (normalized.options && Array.isArray(normalized.options)) {
    normalized.options = normalized.options.map((opt, idx) => {
      if (typeof opt === 'string') {
        return { value: opt, noi_dung: opt, key: String.fromCharCode(65 + idx) };
      }
      return {
        ...opt,
        value: opt.value || opt.noi_dung || opt.text || '',
        noi_dung: opt.noi_dung || opt.value || opt.text || '',
        key: opt.key || opt.chu_cai || String.fromCharCode(65 + idx)
      };
    });
  }

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

const saveQuestionIndividual = async (type, index) => {
  const question = addedQuestions.value[type][index];
  const params = new URLSearchParams(window.location.search);
  const practiceId = params.get('practice_id');
  const appId = params.get('app_id');
  const bookId = params.get('book_id');
  const weekId = params.get('week_id');

  try {
    if (!appId) {
      toast.error('Thiếu app_id. Vui lòng tải lại trang.');
      return;
    }

    if (!practiceId) {
      toast.error('Thiếu practice_id. Vui lòng tải lại trang.');
      return;
    }

    const normalized = normalizeQuestion(question, type);
    const payload = {
      type,
      question: normalized,
      practice_id: parseInt(practiceId),
      week_id: weekId ? parseInt(weekId) : null,
      book_id: bookId ? parseInt(bookId) : null,
      app_id: parseInt(appId)
    };

    console.log('Save payload:', payload);
    const response = await axios.post(route('questions.json.saveQuestion'), payload);

    if (response.data.success) {
      toast.success('Câu hỏi đã được lưu');
      // Auto remove question after successful save
      addedQuestions.value[type].splice(index, 1);
      selectedAddedQuestions.value[type].delete(index);
    } else {
      toast.error(response.data.message || 'Lỗi lưu câu hỏi');
    }
  } catch (error) {
    console.error('Save error:', error);
    console.error('Error response:', error.response?.data);
    toast.error('Lỗi lưu câu hỏi: ' + (error.response?.data?.message || error.message));
  }
};

const saveAllAddedQuestions = async () => {
  const params = new URLSearchParams(window.location.search);
  const practiceId = params.get('practice_id');
  const appId = params.get('app_id');
  const bookId = params.get('book_id');
  const weekId = params.get('week_id');

  const totalQuestions = addedQuestions.value.chon.length + addedQuestions.value.sx.length + addedQuestions.value.noi.length;
  if (totalQuestions === 0) {
    toast.warning('Chưa có câu hỏi để lưu');
    return;
  }

  if (!appId) {
    toast.error('Thiếu app_id. Vui lòng tải lại trang.');
    return;
  }

  if (!practiceId) {
    toast.error('Thiếu practice_id. Vui lòng tải lại trang.');
    return;
  }

  try {
    let savedCount = 0;
    let errors = 0;
    const successfulIndices = { chon: [], sx: [], noi: [] };

    // Save chon questions
    for (let i = 0; i < addedQuestions.value.chon.length; i++) {
      try {
        const normalized = normalizeQuestion(addedQuestions.value.chon[i], 'chon');
        const payload = {
          type: 'chon',
          question: normalized,
          practice_id: parseInt(practiceId),
          week_id: weekId ? parseInt(weekId) : null,
          book_id: bookId ? parseInt(bookId) : null,
          app_id: parseInt(appId)
        };
        const response = await axios.post(route('questions.json.saveQuestion'), payload);
        if (response.data.success) {
          savedCount++;
          successfulIndices.chon.push(i);
        }
      } catch (err) {
        console.error('Error saving chon question:', err);
        errors++;
      }
      await new Promise(resolve => setTimeout(resolve, 300));
    }

    // Save sx questions
    for (let i = 0; i < addedQuestions.value.sx.length; i++) {
      try {
        const normalized = normalizeQuestion(addedQuestions.value.sx[i], 'sx');
        const payload = {
          type: 'sx',
          question: normalized,
          practice_id: parseInt(practiceId),
          week_id: weekId ? parseInt(weekId) : null,
          book_id: bookId ? parseInt(bookId) : null,
          app_id: parseInt(appId)
        };
        const response = await axios.post(route('questions.json.saveQuestion'), payload);
        if (response.data.success) {
          savedCount++;
          successfulIndices.sx.push(i);
        }
      } catch (err) {
        console.error('Error saving sx question:', err);
        errors++;
      }
      await new Promise(resolve => setTimeout(resolve, 300));
    }

    // Save noi questions
    for (let i = 0; i < addedQuestions.value.noi.length; i++) {
      try {
        const normalized = normalizeQuestion(addedQuestions.value.noi[i], 'noi');
        const payload = {
          type: 'noi',
          question: normalized,
          practice_id: parseInt(practiceId),
          week_id: weekId ? parseInt(weekId) : null,
          book_id: bookId ? parseInt(bookId) : null,
          app_id: parseInt(appId)
        };
        const response = await axios.post(route('questions.json.saveQuestion'), payload);
        if (response.data.success) {
          savedCount++;
          successfulIndices.noi.push(i);
        }
      } catch (err) {
        console.error('Error saving noi question:', err);
        errors++;
      }
      await new Promise(resolve => setTimeout(resolve, 300));
    }

    // Remove successfully saved questions in reverse order to avoid index shifting
    successfulIndices.chon.reverse().forEach(idx => {
      addedQuestions.value.chon.splice(idx, 1);
      selectedAddedQuestions.value.chon.delete(idx);
    });
    successfulIndices.sx.reverse().forEach(idx => {
      addedQuestions.value.sx.splice(idx, 1);
      selectedAddedQuestions.value.sx.delete(idx);
    });
    successfulIndices.noi.reverse().forEach(idx => {
      addedQuestions.value.noi.splice(idx, 1);
      selectedAddedQuestions.value.noi.delete(idx);
    });

    if (savedCount > 0) {
      toast.success(`Đã lưu ${savedCount} câu hỏi thành công`);
    }
    if (errors > 0) {
      toast.error(`Có ${errors} câu hỏi lỗi`);
    }
  } catch (error) {
    console.error('Save all error:', error);
    toast.error('Lỗi lưu câu hỏi');
  }
};

// Expose generateQuiz function to parent component
defineExpose({
  generateQuiz
});
</script>

<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-bold mb-6 text-gray-800">Upload Câu hỏi</h3>

    <div class="grid grid-cols-2 gap-6">
      <!-- Left Column: Form & Results -->
      <div>
        <div v-if="!showResults" class="space-y-4">
        <!-- Row 1: Content Input (Full Width) -->
        <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Nội dung câu hỏi</label>
        <textarea
          v-model="contentText"
          placeholder="Nhập nội dung câu hỏi..."
          rows="4"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <!-- Row 2: Context Fields (4 columns) -->
      <div class="grid grid-cols-4 gap-4">
        <!-- Mức độ -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Mức độ</label>
          <select v-model="mucDo" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            <option>Dễ</option>
            <option>Trung bình</option>
            <option>Khó</option>
            <option>Tổng hợp</option>
          </select>
        </div>

        <!-- Lớp -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Lớp</label>
          <input v-model="lop" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
        </div>

        <!-- Môn -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Môn</label>
          <input v-model="mon" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
        </div>

        <!-- Giọng nói -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Giọng nói</label>
          <select v-model="voice" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            <option v-for="v in voices" :key="v.value" :value="v.value">
              {{ v.label }}
            </option>
          </select>
        </div>
      </div>

      <!-- Row 2: File Uploads -->
      <div class="space-y-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Hoặc upload file (PDF, Word, Excel)</label>
          <div class="flex items-center gap-3">
            <input
              type="file"
              @change="handleFileUpload"
              accept=".pdf,.doc,.docx,.xls,.xlsx"
              class="flex-1 px-3 py-2 border border-gray-300 rounded-lg"
            />
            <span v-if="selectedFile" class="text-sm text-green-600 font-semibold whitespace-nowrap">✓ {{ selectedFile.name }}</span>
          </div>
          <p v-if="fileError" class="text-sm text-red-600 mt-1 font-semibold">✕ {{ fileError }}</p>
          <p class="text-xs text-orange-600 mt-1">⚠️ <strong>Giới hạn:</strong> Tối đa 15MB</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Hoặc upload nhiều file (ảnh/PDF)</label>
          <input
            type="file"
            @change="handleMultiFileUpload"
            accept=".pdf,.png,.jpg,.jpeg,.gif,.webp"
            multiple
            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
          />
          <div v-if="selectedFiles.length > 0" class="mt-2 flex flex-wrap gap-2">
            <div v-for="(file, idx) in selectedFiles" :key="idx" class="text-sm text-green-600 bg-green-50 px-2 py-1 rounded flex items-center gap-1">
              <span>✓</span>
              <span>{{ file.name }}</span>
            </div>
          </div>
          <p v-if="multiFileError" class="text-sm text-red-600 mt-1 font-semibold">✕ {{ multiFileError }}</p>
          <p class="text-xs text-orange-600 mt-1">⚠️ <strong>Giới hạn:</strong> 20 file, 50MB tổng, 15MB/file</p>
        </div>
      </div>

      <!-- Row 3: Question Counts by Type -->
      <div class="bg-gray-50 p-4 rounded-lg">
        <h4 class="font-semibold text-gray-800 mb-3">Số lượng câu hỏi theo loại</h4>
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
        @click="generateQuiz"
        :disabled="loading"
        class="w-full px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-semibold rounded-lg transition"
      >
        <span v-if="loading">Đang tạo...</span>
        <span v-else>Tạo câu hỏi</span>
      </button>
    </div>

    <!-- Results -->
    <div v-else-if="quizResult" class="space-y-6">
      <!-- Tabs -->
      <div class="flex gap-2 border-b">
        <button
          v-if="quizResult.chon && quizResult.chon.length > 0"
          @click="activeTab = 'chon'"
          class="px-4 py-2 font-semibold transition"
          :class="activeTab === 'chon' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600'"
        >
          Chọn ({{ quizResult.chon.length }})
        </button>
        <button
          v-if="quizResult.sx && quizResult.sx.length > 0"
          @click="activeTab = 'sx'"
          class="px-4 py-2 font-semibold transition"
          :class="activeTab === 'sx' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600'"
        >
          Sắp xếp ({{ quizResult.sx.length }})
        </button>
        <button
          v-if="quizResult.noi && quizResult.noi.length > 0"
          @click="activeTab = 'noi'"
          class="px-4 py-2 font-semibold transition"
          :class="activeTab === 'noi' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600'"
        >
          Nối ({{ quizResult.noi.length }})
        </button>
      </div>

      <!-- Questions List - Chọn -->
      <div v-if="activeTab === 'chon' && quizResult.chon" class="space-y-4">
        <!-- Select All Header -->
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              :checked="selectedByType.chon.size === quizResult.chon.length && quizResult.chon.length > 0"
              @change="selectAllOfType('chon')"
              class="w-4 h-4"
            />
            <span class="text-sm font-semibold">Chọn tất cả ({{ selectedByType.chon.size }}/{{ quizResult.chon.length }})</span>
          </label>
          <button
            v-if="selectedByType.chon.size > 0"
            @click="addSelectedQuestions('chon')"
            class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            Thêm {{ selectedByType.chon.size }} câu
          </button>
        </div>

        <div v-for="(q, idx) in quizResult.chon" :key="idx" class="p-4 border rounded-lg hover:bg-gray-50" :class="selectedByType.chon.has(idx) ? 'bg-blue-50 border-blue-300' : ''">
          <!-- View Mode -->
          <div v-if="!editingQuestion || editingQuestion.type !== 'chon' || editingQuestion.index !== idx">
            <div class="flex justify-between items-start mb-2">
              <div class="flex items-center gap-3 flex-1">
                <input
                  type="checkbox"
                  :checked="selectedByType.chon.has(idx)"
                  @change="toggleQuestionSelection('chon', idx)"
                  class="w-4 h-4 cursor-pointer mt-1 flex-shrink-0"
                />
                <div>
                  <p v-if="q.tieu_de" class="text-xs text-gray-600 mb-1">{{ q.tieu_de }}</p>
                  <h5 class="font-semibold">{{ idx + 1 }}. {{ q.cau_hoi }}</h5>
                </div>
              </div>
              <div class="flex gap-2 flex-shrink-0">
                <button @click="startEdit('chon', idx)" class="px-2 py-1 text-sm bg-yellow-100 text-yellow-600 rounded hover:bg-yellow-200">Sửa</button>
                <button @click="selectQuestion('chon', idx)" class="px-2 py-1 text-sm bg-blue-100 text-blue-600 rounded hover:bg-blue-200">Chọn</button>
                <button @click="deleteQuestion('chon', idx)" class="px-2 py-1 text-sm bg-red-100 text-red-600 rounded hover:bg-red-200">Xóa</button>
              </div>
            </div>
            <div class="ml-4 space-y-1">
              <div
                v-for="(opt, optIdx) in q.options"
                :key="optIdx"
                class="text-sm px-2 py-1 rounded"
                :class="isCorrectAnswer(q.dap_an_dung, opt.key || String.fromCharCode(65 + optIdx)) ? 'bg-green-200 font-semibold text-green-800' : ''"
              >
                {{ opt.key || String.fromCharCode(65 + optIdx) }}. {{ getOptionText(opt) }}
              </div>
            </div>
          </div>

          <!-- Edit Mode -->
          <div v-else class="space-y-3 bg-yellow-50 p-3 rounded border-2 border-yellow-300">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Tiêu đề (nếu có)</label>
              <input
                v-model="editingData.tieu_de"
                type="text"
                placeholder="Ví dụ: Chọn đáp án đúng"
                class="w-full px-2 py-1 text-sm border rounded"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Câu hỏi</label>
              <input
                v-model="editingData.cau_hoi"
                type="text"
                class="w-full px-2 py-1 text-sm border rounded"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-2">Các lựa chọn</label>
              <div class="space-y-2 ml-2">
                <div v-for="(opt, optIdx) in editingData.options" :key="optIdx" class="flex gap-2 items-start">
                  <span class="text-xs font-semibold py-1">{{ String.fromCharCode(65 + optIdx) }}.</span>
                  <input
                    :value="getOptionText(opt)"
                    @input="updateEditOption(optIdx, opt.noi_dung ? 'noi_dung' : 'value', $event.target.value)"
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

            <div class="flex gap-2 pt-2">
              <button @click="saveEdit" class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700">Lưu</button>
              <button @click="cancelEdit" class="px-3 py-1 text-sm bg-gray-400 text-white rounded hover:bg-gray-500">Hủy</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Questions List - Sắp xếp -->
      <div v-if="activeTab === 'sx' && quizResult.sx" class="space-y-4">
        <!-- Select All Header -->
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              :checked="selectedByType.sx.size === quizResult.sx.length && quizResult.sx.length > 0"
              @change="selectAllOfType('sx')"
              class="w-4 h-4"
            />
            <span class="text-sm font-semibold">Chọn tất cả ({{ selectedByType.sx.size }}/{{ quizResult.sx.length }})</span>
          </label>
          <button
            v-if="selectedByType.sx.size > 0"
            @click="addSelectedQuestions('sx')"
            class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            Thêm {{ selectedByType.sx.size }} câu
          </button>
        </div>

        <div v-for="(q, idx) in quizResult.sx" :key="idx" class="p-4 border rounded-lg hover:bg-gray-50" :class="selectedByType.sx.has(idx) ? 'bg-blue-50 border-blue-300' : ''">
          <!-- View Mode -->
          <div v-if="!editingQuestion || editingQuestion.type !== 'sx' || editingQuestion.index !== idx">
            <div class="flex justify-between items-start mb-2">
              <div class="flex items-center gap-3">
                <input
                  type="checkbox"
                  :checked="selectedByType.sx.has(idx)"
                  @change="toggleQuestionSelection('sx', idx)"
                  class="w-4 h-4 cursor-pointer mt-1"
                />
                <h5 class="font-semibold">{{ idx + 1 }}. {{ q.tieu_de || q.cau_hoi }}</h5>
              </div>
              <div class="flex gap-2">
                <button @click="startEdit('sx', idx)" class="px-2 py-1 text-sm bg-yellow-100 text-yellow-600 rounded hover:bg-yellow-200">Sửa</button>
                <button @click="selectQuestion('sx', idx)" class="px-2 py-1 text-sm bg-blue-100 text-blue-600 rounded hover:bg-blue-200">Chọn</button>
                <button @click="deleteQuestion('sx', idx)" class="px-2 py-1 text-sm bg-red-100 text-red-600 rounded hover:bg-red-200">Xóa</button>
              </div>
            </div>
            <div class="ml-4 space-y-1">
              <div v-for="(opt, optIdx) in q.options" :key="optIdx" class="text-sm">
                {{ optIdx + 1 }}. {{ getOptionText(opt) }}
              </div>
            </div>
          </div>

          <!-- Edit Mode -->
          <div v-else class="space-y-3 bg-yellow-50 p-3 rounded border-2 border-yellow-300">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Tiêu đề / Câu hỏi</label>
              <input
                v-model="editingData.tieu_de"
                type="text"
                class="w-full px-2 py-1 text-sm border rounded"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-2">Các mục cần sắp xếp</label>
              <div class="space-y-2 ml-2">
                <div v-for="(opt, optIdx) in editingData.options" :key="optIdx" class="flex gap-2">
                  <span class="text-xs font-semibold py-1 w-6">{{ optIdx + 1 }}.</span>
                  <input
                    :value="getOptionText(opt)"
                    @input="editingData.options[optIdx] = { ...editingData.options[optIdx], value: $event.target.value, noi_dung: $event.target.value }"
                    type="text"
                    class="flex-1 px-2 py-1 text-xs border rounded"
                  />
                </div>
              </div>
            </div>

            <div class="flex gap-2 pt-2">
              <button @click="saveEdit" class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700">Lưu</button>
              <button @click="cancelEdit" class="px-3 py-1 text-sm bg-gray-400 text-white rounded hover:bg-gray-500">Hủy</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Questions List - Nối -->
      <div v-if="activeTab === 'noi' && quizResult.noi" class="space-y-4">
        <!-- Select All Header -->
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              :checked="selectedByType.noi.size === quizResult.noi.length && quizResult.noi.length > 0"
              @change="selectAllOfType('noi')"
              class="w-4 h-4"
            />
            <span class="text-sm font-semibold">Chọn tất cả ({{ selectedByType.noi.size }}/{{ quizResult.noi.length }})</span>
          </label>
          <button
            v-if="selectedByType.noi.size > 0"
            @click="addSelectedQuestions('noi')"
            class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            Thêm {{ selectedByType.noi.size }} câu
          </button>
        </div>

        <div v-for="(q, idx) in quizResult.noi" :key="idx" class="p-4 border rounded-lg hover:bg-gray-50" :class="selectedByType.noi.has(idx) ? 'bg-blue-50 border-blue-300' : ''">
          <!-- View Mode -->
          <div v-if="!editingQuestion || editingQuestion.type !== 'noi' || editingQuestion.index !== idx">
            <div class="flex justify-between items-start mb-2">
              <div class="flex items-center gap-3">
                <input
                  type="checkbox"
                  :checked="selectedByType.noi.has(idx)"
                  @change="toggleQuestionSelection('noi', idx)"
                  class="w-4 h-4 cursor-pointer mt-1"
                />
                <h5 class="font-semibold">{{ idx + 1 }}. {{ q.tieu_de || q.cau_hoi }}</h5>
              </div>
              <div class="flex gap-2">
                <button @click="startEdit('noi', idx)" class="px-2 py-1 text-sm bg-yellow-100 text-yellow-600 rounded hover:bg-yellow-200">Sửa</button>
                <button @click="selectQuestion('noi', idx)" class="px-2 py-1 text-sm bg-blue-100 text-blue-600 rounded hover:bg-blue-200">Chọn</button>
                <button @click="deleteQuestion('noi', idx)" class="px-2 py-1 text-sm bg-red-100 text-red-600 rounded hover:bg-red-200">Xóa</button>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4 ml-4">
              <div class="space-y-1">
                <div class="font-semibold text-sm text-gray-600">Cột A</div>
                <div v-for="(item, optIdx) in q.cot_a" :key="optIdx" class="text-sm">
                  {{ optIdx + 1 }}. {{ getOptionText(item) }}
                </div>
              </div>
              <div class="space-y-1">
                <div class="font-semibold text-sm text-gray-600">Cột B</div>
                <div v-for="(item, optIdx) in q.cot_b" :key="optIdx" class="text-sm">
                  {{ optIdx + 1 }}. {{ getOptionText(item) }}
                </div>
              </div>
            </div>
          </div>

          <!-- Edit Mode -->
          <div v-else class="space-y-3 bg-yellow-50 p-3 rounded border-2 border-yellow-300">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Tiêu đề / Câu hỏi</label>
              <input
                v-model="editingData.tieu_de"
                type="text"
                class="w-full px-2 py-1 text-sm border rounded"
              />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-2">Cột A</label>
                <div class="space-y-2">
                  <div v-for="(item, itemIdx) in editingData.cot_a" :key="itemIdx" class="flex gap-1">
                    <span class="text-xs font-semibold py-1 w-5">{{ itemIdx + 1 }}.</span>
                    <input
                      :value="getOptionText(item)"
                      @input="updateEditCotA(itemIdx, $event.target.value)"
                      type="text"
                      class="flex-1 px-2 py-1 text-xs border rounded"
                    />
                  </div>
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-2">Cột B</label>
                <div class="space-y-2">
                  <div v-for="(item, itemIdx) in editingData.cot_b" :key="itemIdx" class="flex gap-1">
                    <span class="text-xs font-semibold py-1 w-5">{{ itemIdx + 1 }}.</span>
                    <input
                      :value="getOptionText(item)"
                      @input="updateEditCotB(itemIdx, $event.target.value)"
                      type="text"
                      class="flex-1 px-2 py-1 text-xs border rounded"
                    />
                  </div>
                </div>
              </div>
            </div>

            <div class="flex gap-2 pt-2">
              <button @click="saveEdit" class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700">Lưu</button>
              <button @click="cancelEdit" class="px-3 py-1 text-sm bg-gray-400 text-white rounded hover:bg-gray-500">Hủy</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Reset Button -->
      <button
        @click="showResults = false"
        class="w-full px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold rounded-lg transition"
      >
        Tạo thêm
      </button>
      </div>
      </div>

      <!-- Right Column: Summary Box -->
      <div>
        <div class="sticky top-0 bg-gray-50 p-4 rounded-lg border-2 border-gray-200">
          <div class="flex items-center justify-between mb-4">
            <h4 class="font-bold text-gray-800">Câu hỏi đã chọn</h4>
            <div class="flex items-center gap-2">
              <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-bold">
                {{ addedQuestions.chon.length + addedQuestions.sx.length + addedQuestions.noi.length }}</span>
              <label class="text-xs text-gray-600 cursor-pointer flex items-center gap-1">
                <input
                  type="checkbox"
                  :checked="selectedAddedQuestions.chon.size + selectedAddedQuestions.sx.size + selectedAddedQuestions.noi.size === addedQuestions.chon.length + addedQuestions.sx.length + addedQuestions.noi.length && (addedQuestions.chon.length + addedQuestions.sx.length + addedQuestions.noi.length) > 0"
                  @change="selectAllAddedOfType('chon'); selectAllAddedOfType('sx'); selectAllAddedOfType('noi')"
                  class="w-4 h-4"
                />
                Chọn tất cả
              </label>
            </div>
          </div>

          <div v-if="addedQuestions.chon.length === 0 && addedQuestions.sx.length === 0 && addedQuestions.noi.length === 0" class="text-center py-8 text-gray-500">
            <p class="text-sm">Chưa có câu hỏi nào được chọn</p>
            <p class="text-xs mt-1">Chọn câu hỏi từ bên trái để thêm vào danh sách</p>
          </div>

          <div v-else class="space-y-3 max-h-96 overflow-y-auto">
            <!-- Chọn Questions -->
            <div v-for="(q, idx) in addedQuestions.chon" :key="`chon-${idx}`" class="bg-white p-4 rounded border shadow" :class="selectedAddedQuestions.chon.has(idx) ? 'bg-blue-50 border-blue-300' : ''">
              <div v-if="editingAddedQuestion?.type !== 'chon' || editingAddedQuestion?.index !== idx">
                <div class="flex justify-between items-start mb-3">
                  <div class="flex items-center gap-2">
                    <input
                      type="checkbox"
                      :checked="selectedAddedQuestions.chon.has(idx)"
                      @change="toggleAddedQuestionSelection('chon', idx)"
                      class="w-4 h-4 cursor-pointer"
                    />
                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">Chọn</span>
                    <span class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">✓ Đã lưu</span>
                  </div>
                  <div class="flex gap-2">
                    <button @click="startEditAddedQuestion('chon', idx)" class="px-2 py-1 text-xs text-yellow-600 hover:text-yellow-700">Sửa</button>
                    <button @click="deleteAddedQuestion('chon', idx)" class="px-2 py-1 text-xs text-red-600 hover:text-red-700">Xóa</button>
                  </div>
                </div>
                <p class="text-sm font-semibold text-gray-800 mb-3">{{ idx + 1 }}. {{ q.cau_hoi }}</p>
                <div class="space-y-2 mb-3">
                  <div v-for="(opt, optIdx) in q.options" :key="optIdx" class="text-xs px-3 py-2 rounded" :class="isCorrectAnswer(q.dap_an_dung, opt.key || String.fromCharCode(65 + optIdx)) ? 'bg-green-100 text-green-900 font-semibold' : 'text-gray-700'">
                    {{ opt.key || String.fromCharCode(65 + optIdx) }}. {{ getOptionText(opt) }}
                  </div>
                </div>
                <button @click="saveQuestionIndividual('chon', idx)" class="w-full py-2 bg-green-600 hover:bg-green-700 text-white rounded text-sm font-semibold">Lưu câu hỏi</button>
              </div>

              <!-- Edit Mode -->
              <div v-else class="bg-yellow-50 p-3 rounded border-2 border-yellow-300 space-y-2">
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-1">Câu hỏi</label>
                  <input v-model="editingAddedData.cau_hoi" type="text" class="w-full px-2 py-1 text-sm border rounded" />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-1">Đáp án đúng</label>
                  <select v-model="editingAddedData.dap_an_dung" class="w-full px-2 py-1 text-sm border rounded">
                    <option v-for="(opt, i) in editingAddedData.options" :key="i" :value="opt.key || String.fromCharCode(65 + i)">
                      {{ opt.key || String.fromCharCode(65 + i) }}. {{ getOptionText(opt) }}
                    </option>
                  </select>
                </div>
                <div class="flex gap-2">
                  <button @click="saveEditAddedQuestion" class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700">Lưu</button>
                  <button @click="cancelEditAddedQuestion" class="px-3 py-1 text-sm bg-gray-400 text-white rounded hover:bg-gray-500">Hủy</button>
                </div>
              </div>
            </div>

            <!-- Sắp xếp Questions -->
            <div v-for="(q, idx) in addedQuestions.sx" :key="`sx-${idx}`" class="bg-white p-4 rounded border shadow" :class="selectedAddedQuestions.sx.has(idx) ? 'bg-blue-50 border-blue-300' : ''">
              <div v-if="editingAddedQuestion?.type !== 'sx' || editingAddedQuestion?.index !== idx" class="space-y-3">
              <div class="flex justify-between items-start">
                <div class="flex items-center gap-2">
                  <input
                    type="checkbox"
                    :checked="selectedAddedQuestions.sx.has(idx)"
                    @change="toggleAddedQuestionSelection('sx', idx)"
                    class="w-4 h-4 cursor-pointer"
                  />
                  <span class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Sắp xếp</span>
                </div>
                <div class="flex gap-2">
                  <button @click="startEditAddedQuestion('sx', idx)" class="px-2 py-1 text-xs text-yellow-600 hover:text-yellow-700">Sửa</button>
                  <button @click="deleteAddedQuestion('sx', idx)" class="px-2 py-1 text-xs text-red-600 hover:text-red-700">Xóa</button>
                </div>
              </div>
                <p class="text-xs text-gray-600 mb-1">{{ q.cau_hoi || q.tieu_de || q.tieu_de_tro || '' }}</p>
                <p class="text-sm font-semibold text-gray-800 mb-3">{{ addedQuestions.chon.length + idx + 1 }}. {{ q.cau_hoi || q.tieu_de || q.tieu_de_tro || '' }}</p>
                <div v-if="q.items && q.items.length > 0" class="space-y-1 mb-3 text-xs text-gray-700">
                  <div v-for="(item, optIdx) in q.items" :key="optIdx">
                    {{ optIdx + 1 }}. {{ typeof item === 'string' ? item : item.noi_dung || item.value || JSON.stringify(item) }}
                  </div>
                </div>
                <div v-else-if="q.cot_a && q.cot_a.length > 0" class="space-y-1 mb-3 text-xs text-gray-700">
                  <div v-for="(item, optIdx) in q.cot_a" :key="optIdx">
                    {{ optIdx + 1 }}. {{ typeof item === 'string' ? item : item.noi_dung || item.value || JSON.stringify(item) }}
                  </div>
                </div>
                <div v-else-if="q.options && q.options.length > 0" class="space-y-1 mb-3 text-xs text-gray-700">
                  <div v-for="(opt, optIdx) in q.options" :key="optIdx">
                    {{ optIdx + 1 }}. {{ opt.value || opt.noi_dung || opt }}
                  </div>
                </div>
                <div v-else class="text-xs text-gray-500 mb-3 italic">
                  (Không có dữ liệu để hiển thị)
                </div>
                <button @click="saveQuestionIndividual('sx', idx)" class="w-full py-2 bg-green-600 hover:bg-green-700 text-white rounded text-sm font-semibold">Lưu câu hỏi</button>
              </div>

              <!-- Edit Mode -->
              <div v-else class="bg-yellow-50 p-3 rounded border-2 border-yellow-300 space-y-2">
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-1">Câu hỏi</label>
                  <input v-model="editingAddedData.cau_hoi" type="text" class="w-full px-2 py-1 text-sm border rounded" />
                </div>
                <div class="flex gap-2">
                  <button @click="saveEditAddedQuestion" class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700">Lưu</button>
                  <button @click="cancelEditAddedQuestion" class="px-3 py-1 text-sm bg-gray-400 text-white rounded hover:bg-gray-500">Hủy</button>
                </div>
              </div>
            </div>

            <!-- Nối Questions -->
            <div v-for="(q, idx) in addedQuestions.noi" :key="`noi-${idx}`" class="bg-white p-4 rounded border shadow" :class="selectedAddedQuestions.noi.has(idx) ? 'bg-blue-50 border-blue-300' : ''">
              <div v-if="editingAddedQuestion?.type !== 'noi' || editingAddedQuestion?.index !== idx" class="space-y-3">
              <div class="flex justify-between items-start">
                <div class="flex items-center gap-2">
                  <input
                    type="checkbox"
                    :checked="selectedAddedQuestions.noi.has(idx)"
                    @change="toggleAddedQuestionSelection('noi', idx)"
                    class="w-4 h-4 cursor-pointer"
                  />
                  <span class="inline-block px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">Nối</span>
                </div>
                <div class="flex gap-2">
                  <button @click="startEditAddedQuestion('noi', idx)" class="px-2 py-1 text-xs text-yellow-600 hover:text-yellow-700">Sửa</button>
                  <button @click="deleteAddedQuestion('noi', idx)" class="px-2 py-1 text-xs text-red-600 hover:text-red-700">Xóa</button>
                </div>
              </div>
                <p class="text-xs text-gray-600 mb-1">{{ q.cau_hoi || q.tieu_de }}</p>
                <p class="text-sm font-semibold text-gray-800 mb-3">{{ addedQuestions.chon.length + addedQuestions.sx.length + idx + 1 }}. {{ q.cau_hoi || q.tieu_de }}</p>
                <div class="grid grid-cols-2 gap-4 mb-3 text-xs text-gray-700">
                  <div>
                    <span class="font-semibold">Cột A:</span>
                    <div v-for="(item, optIdx) in q.cot_a" :key="optIdx">{{ optIdx + 1 }}. {{ getOptionText(item) }}</div>
                  </div>
                  <div>
                    <span class="font-semibold">Cột B:</span>
                    <div v-for="(item, optIdx) in q.cot_b" :key="optIdx">{{ optIdx + 1 }}. {{ getOptionText(item) }}</div>
                  </div>
                </div>
                <button @click="saveQuestionIndividual('noi', idx)" class="w-full py-2 bg-green-600 hover:bg-green-700 text-white rounded text-sm font-semibold">Lưu câu hỏi</button>
              </div>

              <!-- Edit Mode -->
              <div v-else class="bg-yellow-50 p-3 rounded border-2 border-yellow-300 space-y-2">
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-1">Câu hỏi</label>
                  <input v-model="editingAddedData.cau_hoi" type="text" class="w-full px-2 py-1 text-sm border rounded" />
                </div>
                <div class="flex gap-2">
                  <button @click="saveEditAddedQuestion" class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700">Lưu</button>
                  <button @click="cancelEditAddedQuestion" class="px-3 py-1 text-sm bg-gray-400 text-white rounded hover:bg-gray-500">Hủy</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div v-if="addedQuestions.chon.length > 0 || addedQuestions.sx.length > 0 || addedQuestions.noi.length > 0" class="mt-4 space-y-2">
          <button
            @click="saveQuestionIndividual('chon', 0)"
            :disabled="addedQuestions.chon.length === 0 && addedQuestions.sx.length === 0 && addedQuestions.noi.length === 0"
            class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold rounded-lg transition"
          >
            💾 Lưu câu hỏi
          </button>
          <button
            @click="saveAllAddedQuestions"
            :disabled="addedQuestions.chon.length === 0 && addedQuestions.sx.length === 0 && addedQuestions.noi.length === 0"
            class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-semibold rounded-lg transition"
          >
            💾 Lưu tất cả ({{ addedQuestions.chon.length + addedQuestions.sx.length + addedQuestions.noi.length }})
          </button>

          <!-- Bulk Delete Button (only show if any selected) -->
          <div v-if="selectedAddedQuestions.chon.size + selectedAddedQuestions.sx.size + selectedAddedQuestions.noi.size > 0" class="grid grid-cols-2 gap-2 pt-2">
            <button
              @click="deleteSelectedAddedQuestions('chon'); deleteSelectedAddedQuestions('sx'); deleteSelectedAddedQuestions('noi')"
              class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition text-sm"
            >
              🗑️ Xóa đã chọn ({{ selectedAddedQuestions.chon.size + selectedAddedQuestions.sx.size + selectedAddedQuestions.noi.size }})
            </button>
            <button
              @click="selectedAddedQuestions.chon.clear(); selectedAddedQuestions.sx.clear(); selectedAddedQuestions.noi.clear()"
              class="px-3 py-2 bg-gray-400 hover:bg-gray-500 text-white font-semibold rounded-lg transition text-sm"
            >
              Bỏ chọn
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
