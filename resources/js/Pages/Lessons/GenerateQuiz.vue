<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useToast } from 'vue-toastification';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import LessonDraftHistory from "@/Pages/Lesson/LessonDraftHistory.vue";
import Editor from '@tinymce/tinymce-vue';
import GenerateQuizForm from "@/Components/GenerateQuizForm.vue";
import GenerateQuizResults from "@/Components/GenerateQuizResults.vue";

const toast = useToast();
const props = defineProps({
  app_id: String,
  book_id: String,
  week_id: String,
  practice_id: String,
});

// Get from URL search params if not in props
const getQueryParam = (param) => {
  const searchParams = new URLSearchParams(window.location.search);
  return searchParams.get(param);
};

// Form state
const generateTypes = ref(new Set(['exercises'])); // Có thể tạo exercises, text, hoặc audio
const contentText = ref('');
const selectedFiles = ref([]);
const voice = ref('nova');
const lop = ref('Lớp 1');
const mon = ref('Toán');
const mucDo = ref('Dễ');
const counts = ref({ chon: 2, sx: 2, noi: 1 });

// Loading & Results
const loading = ref(false); // For save operations
const generatingQuiz = ref(false); // For quiz generation (questions)
const generatingText = ref(false); // For text generation
const generatingAudio = ref(false); // For audio generation
const generationStatus = ref(''); // Status message for current generation step
const pollingTaskIds = ref(new Map()); // Map of { type: taskId }
const pollingIntervals = ref(new Map()); // Map of { type: interval }
const results = ref(new Map()); // Map of { type: result }
const quizResult = ref(null); // Store generated quiz result
const selectedByType = ref({ chon: new Set(), sx: new Set(), noi: new Set() }); // Track checkboxes in left column
const addedQuestions = ref({ chon: [], sx: [], noi: [] }); // Track questions added to right column
const savedQuestions = ref(new Set()); // Track saved question identifiers
const selectedAddedQuestions = ref({ chon: new Set(), sx: new Set(), noi: new Set() }); // Track selected questions in right column
const editingQuestion = ref(null); // { type, index }
const editingData = ref(null);
const selectedQuestionTab = ref('chon'); // Track which tab is selected in right column

const toggleType = (type) => {
  if (generateTypes.value.has(type)) {
    generateTypes.value.delete(type);
  } else {
    generateTypes.value.add(type);
  }
};

const voices = [
  { label: 'Nova (Nữ trẻ)', value: 'nova' },
  { label: 'Shimmer', value: 'shimmer' },
  { label: 'Echo', value: 'echo' },
  { label: 'Fable', value: 'fable' },
  { label: 'Onyx', value: 'onyx' },
  { label: 'Alloy', value: 'alloy' },
];

const apiKey = import.meta.env.VITE_TINY_MCE_API_KEY;
const editorConfig = {
  height: '400px',
  plugins: 'lists link image table code help wordcount',
  menubar: 'file edit view format tools table',
  statusbar: true,
  language: 'vi',
  readonly: true,
  toolbar: 'undo redo | formatselect fontselect fontsizeselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image',
  font_family_formats:
    'Arial=arial,helvetica,sans-serif;' +
    'Times New Roman=times new roman,times,serif;' +
    'Courier New=courier new,courier,monospace;' +
    'Georgia=georgia,serif;' +
    'Verdana=verdana,sans-serif;',
};

const historyModal = ref(null);

const canGenerate = computed(() => {
  return (contentText.value.trim().length > 0 || selectedFiles.value.length > 0) && generateTypes.value.size > 0;
});

const hasSelectedQuestions = computed(() => {
  if (!addedQuestions.value) return false;
  const count = (addedQuestions.value.chon?.length || 0) +
                (addedQuestions.value.sx?.length || 0) +
                (addedQuestions.value.noi?.length || 0);
  return count > 0;
});

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files || []);
  selectedFiles.value = files.filter(f => {
    const isValid = f.size <= 15 * 1024 * 1024; // 15MB max
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

const pollTaskStatus = (taskId) => {
  if (!taskId) return;

  const interval = setInterval(async () => {
    try {
      const response = await axios.get(route('lessons.json.taskStatus', { task_id: taskId }));

      if (response.data.status === 'done' || response.data.status === 'completed') {
        clearInterval(interval);
        results.value.set('audio', response.data);
        pollingTaskIds.value.delete('audio');
        pollingIntervals.value.delete('audio');
        generationStatus.value = '✓ Tạo sách nói hoàn thành';

        // Auto-save audio if practice_id is provided
        const practice_id = getQueryParam('practice_id');
        if (practice_id && response.data.result_url) {
          try {
            const saveResponse = await axios.post(route('lessons.json.saveAudioResult'), {
              practice_id: practice_id,
              result_url: response.data.result_url,
              app_id: getQueryParam('app_id'),
              book_id: getQueryParam('book_id'),
              week_id: getQueryParam('week_id'),
            });
            if (saveResponse.data.success) {
              toast.success('✓ Tạo sách nói thành công! Nội dung đã tự động lưu.');
            } else {
              toast.success('✓ Tạo sách nói thành công!');
            }
          } catch (error) {
            toast.success('✓ Tạo sách nói thành công!');
            console.error('Auto-save audio error:', error);
          }
        } else {
          toast.success('Tạo sách nói thành công!');
        }

        // Check if all tasks are done
        if (pollingTaskIds.value.size === 0) {
          loading.value = false;
          generationStatus.value = '✓ Hoàn thành!';
          // Mở lịch sử khi tất cả tasks hoàn thành
          onGenerationComplete();
          // Clear status after 2 seconds
          setTimeout(() => {
            generationStatus.value = '';
          }, 2000);
        }
      } else if (response.data.status === 'error' || response.data.status === 'failed') {
        clearInterval(interval);
        pollingTaskIds.value.delete('audio');
        pollingIntervals.value.delete('audio');
        generationStatus.value = '✗ Lỗi tạo sách nói';
        toast.error(response.data.error_message || 'Lỗi tạo sách nói');

        // Check if all tasks are done
        if (pollingTaskIds.value.size === 0) {
          loading.value = false;
        }
      }
    } catch (error) {
      console.error('Poll error:', error);
    }
  }, 10000); // Poll mỗi 10s

  pollingIntervals.value.set('audio', interval);
};

const generateLesson = async () => {
  if (generateTypes.value.size === 0) {
    toast.error('Vui lòng chọn ít nhất một loại bài giảng');
    return;
  }

  // For text/audio generation, validate content
  if ((generateTypes.value.has('text') || generateTypes.value.has('audio')) &&
      !contentText.value.trim() && selectedFiles.value.length === 0) {
    toast.error('Vui lòng nhập nội dung hoặc chọn file cho bài đọc/sách nói');
    return;
  }

  loading.value = true;
  results.value.clear();
  generationStatus.value = '';

  try {
    // Xử lý theo thứ tự: Exercises → Text → Audio

    // 1. Tạo câu hỏi trước (nếu được chọn)
    if (generateTypes.value.has('exercises')) {
      generationStatus.value = '⏳ Đang tạo câu hỏi...';
      try {
        // Determine input_type based on file type
        let inputType = 'text';
        if (selectedFiles.value.length > 0) {
          const fileExt = selectedFiles.value[0].name.split('.').pop().toLowerCase();
          // If multiple files or images, use 'image'
          if (selectedFiles.value.length > 1 || ['png', 'jpg', 'jpeg', 'gif', 'webp'].includes(fileExt)) {
            inputType = 'image';
          } else {
            // PDF, Word, Excel = 'file'
            inputType = 'file';
          }
        }

        const formData = new FormData();

        // Add required fields
        formData.append('input_type', inputType);
        formData.append('muc_do', mucDo.value);
        formData.append('content_text', contentText.value);
        formData.append('lop', lop.value);
        formData.append('mon', mon.value);

        // Add counts
        formData.append('counts[chon]', counts.value.chon);
        formData.append('counts[sx]', counts.value.sx);
        formData.append('counts[noi]', counts.value.noi);

        // Add files if provided
        if (selectedFiles.value.length > 0) {
          if (selectedFiles.value.length === 1) {
            // Single file - use 'file' parameter
            formData.append('file', selectedFiles.value[0]);
          } else {
            // Multiple files - use 'files[]' array
            selectedFiles.value.forEach((file) => {
              formData.append('files[]', file);
            });
          }
        }

        console.log('Generating quiz with input_type:', inputType);
        const response = await axios.post(route('lessons.json.generateQuiz'), formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data.success) {
          console.log('Quiz generated successfully');
          quizResult.value = response.data.data; // Store the quiz result
          generationStatus.value = '✓ Tạo câu hỏi hoàn thành';
          toast.success('✓ Tạo câu hỏi thành công!');
          // Reset loading once exercises are done
          if (!generateTypes.value.has('text') && !generateTypes.value.has('audio')) {
            loading.value = false;
            setTimeout(() => { generationStatus.value = ''; }, 2000);
          }
        } else {
          throw new Error(response.data.message || 'Lỗi tạo câu hỏi');
        }
      } catch (error) {
        console.error('Quiz generation error:', error);
        generationStatus.value = '✗ Lỗi tạo câu hỏi';
        toast.error('Lỗi tạo câu hỏi: ' + (error?.response?.data?.message || error?.message || 'Vui lòng thử lại'));
        loading.value = false;
      }
      // Chờ 1 giây trước khi tạo bài đọc
      await new Promise(resolve => setTimeout(resolve, 1000));
    }

    // 2. Tạo text (nếu được chọn)
    if (generateTypes.value.has('text')) {
      generationStatus.value = '⏳ Đang tạo bài đọc...';
      try {
        let contentUrls = [];
        if (selectedFiles.value.length > 0) {
          contentUrls = await uploadFiles();
        }

        const basePayload = {
          content_text: contentText.value,
          content_urls: contentUrls.length > 0 ? contentUrls : undefined,
          lop: lop.value,
          mon: mon.value,
          app_id: props.app_id || getQueryParam('app_id'),
          book_id: props.book_id || getQueryParam('book_id'),
          week_id: props.week_id || getQueryParam('week_id'),
          practice_id: props.practice_id || getQueryParam('practice_id'),
        };

        Object.keys(basePayload).forEach(key => basePayload[key] === undefined && delete basePayload[key]);

        const response = await axios.post(route('lessons.json.generateText'), basePayload);
        if (response.data.success) {
          results.value.set('text', response.data.result);
          generationStatus.value = '✓ Tạo bài đọc hoàn thành';
          if (response.data.auto_saved) {
            toast.success('✓ Bài đọc tạo thành công! Nội dung đã tự động lưu.');
          } else {
            toast.success('✓ Bài đọc tạo thành công!');
          }
          // Reset loading if no audio will be generated
          if (!generateTypes.value.has('audio')) {
            loading.value = false;
            setTimeout(() => { generationStatus.value = ''; }, 2000);
          }
        }
      } catch (error) {
        generationStatus.value = '✗ Lỗi tạo bài đọc';
        toast.error('Lỗi tạo bài đọc: ' + (error.response?.data?.message || error.message));
        loading.value = false;
      }
      // Chờ 1 giây trước khi tạo sách nói
      await new Promise(resolve => setTimeout(resolve, 1000));
    }

    // 3. Tạo audio sau cùng (nếu được chọn)
    if (generateTypes.value.has('audio')) {
      generationStatus.value = '⏳ Đang tạo sách nói (2-3 phút)...';
      try {
        let contentUrls = [];
        if (selectedFiles.value.length > 0) {
          contentUrls = await uploadFiles();
        }

        const basePayload = {
          content_text: contentText.value,
          content_urls: contentUrls.length > 0 ? contentUrls : undefined,
          lop: lop.value,
          mon: mon.value,
          voice: voice.value,
          app_id: props.app_id || getQueryParam('app_id'),
          book_id: props.book_id || getQueryParam('book_id'),
          week_id: props.week_id || getQueryParam('week_id'),
          practice_id: props.practice_id || getQueryParam('practice_id'),
        };

        Object.keys(basePayload).forEach(key => basePayload[key] === undefined && delete basePayload[key]);

        const response = await axios.post(route('lessons.json.generateAudio'), basePayload);
        if (response.data.success) {
          pollingTaskIds.value.set('audio', response.data.task_id);
          toast.info('⏳ Đang tạo sách nói (2-3 phút)...');
          pollTaskStatus(response.data.task_id);
        }
      } catch (error) {
        generationStatus.value = '✗ Lỗi tạo sách nói';
        toast.error('Lỗi tạo sách nói: ' + (error.response?.data?.message || error.message));
        loading.value = false;
      }
    }

    // If no audio polling, show completion message
    if (!generateTypes.value.has('audio') || pollingTaskIds.value.size === 0) {
      generationStatus.value = '✓ Hoàn thành!';
      // Clear status after 2 seconds
      setTimeout(() => {
        generationStatus.value = '';
      }, 2000);
    }
  } catch (error) {
    generationStatus.value = '✗ Lỗi tạo bài giảng';
    pollingIntervals.value.forEach(interval => clearInterval(interval));
    pollingIntervals.value.clear();
    toast.error(error.response?.data?.message || error.message || 'Lỗi tạo bài giảng');
  } finally {
    // Always reset loading at the end
    loading.value = false;
  }
};

const onGenerationComplete = () => {
  // Auto-open history modal after generation completes
  if (historyModal.value) {
    historyModal.value.openModal();
  }
};

const reset = () => {
  contentText.value = '';
  selectedFiles.value = [];
  results.value.clear();
  loading.value = false;
  pollingTaskIds.value.clear();
  pollingIntervals.value.forEach(interval => clearInterval(interval));
  pollingIntervals.value.clear();
};

const createMore = () => {
  quizResult.value = null;
  selectedByType.value = { chon: new Set(), sx: new Set(), noi: new Set() };
  addedQuestions.value = { chon: [], sx: [], noi: [] };
  selectedQuestionTab.value = 'chon';
  generationStatus.value = '';
  contentText.value = '';
  selectedFiles.value = [];
};

const isQuestionSaved = (type, question) => {
  return savedQuestions.value.has(`${type}-${question.cau_hoi}`);
};

const addQuestionToRight = (type, idx) => {
  const question = quizResult.value[type][idx];
  const alreadyAdded = addedQuestions.value[type].some(q =>
    JSON.stringify(q) === JSON.stringify(question)
  );
  if (!alreadyAdded) {
    addedQuestions.value[type].push(JSON.parse(JSON.stringify(question)));
    toast.success('✓ Thêm câu hỏi vào box phải');
  } else {
    toast.info('Câu hỏi đã có trong box phải rồi');
  }
};

const removeSavedQuestionsFromRight = (type, cau_hoi) => {
  const indices = [];
  addedQuestions.value[type].forEach((q, idx) => {
    if (q.cau_hoi === cau_hoi) {
      indices.push(idx);
    }
  });
  // Remove in reverse order to avoid index shifting
  indices.reverse().forEach(idx => {
    addedQuestions.value[type].splice(idx, 1);
    selectedAddedQuestions.value[type].delete(idx);
  });
};

const saveSingleQuestion = async (type, idx) => {
  try {
    loading.value = true;
    const question = quizResult.value[type][idx];

    const payload = {
      type: type,
      question: question,
      practice_id: parseInt(props.practice_id || getQueryParam('practice_id')),
      app_id: parseInt(props.app_id || getQueryParam('app_id')),
      book_id: parseInt(props.book_id || getQueryParam('book_id')),
      week_id: parseInt(props.week_id || getQueryParam('week_id'))
    };

    const response = await axios.post(route('questions.json.saveQuestion'), payload);

    if (response.data.success) {
      savedQuestions.value.add(`${type}-${question.cau_hoi}`);
      removeSavedQuestionsFromRight(type, question.cau_hoi);
      toast.success('✓ Lưu câu hỏi thành công!');
    } else {
      throw new Error(response.data.message || 'Lỗi lưu câu hỏi');
    }
  } catch (error) {
    console.error('Save error:', error);
    toast.error(error.response?.data?.message || error.message || 'Lỗi lưu câu hỏi');
  } finally {
    loading.value = false;
  }
};

const saveSelectedAddedQuestions = async () => {
  try {
    loading.value = true;
    const selectedQuestions = [];

    // Collect selected Chọn questions
    Array.from(selectedAddedQuestions.value.chon).forEach(idx => {
      selectedQuestions.push({
        type: 'chon',
        data: addedQuestions.value.chon[idx]
      });
    });

    // Collect selected Sắp xếp questions
    Array.from(selectedAddedQuestions.value.sx).forEach(idx => {
      selectedQuestions.push({
        type: 'sx',
        data: addedQuestions.value.sx[idx]
      });
    });

    // Collect selected Nối questions
    Array.from(selectedAddedQuestions.value.noi).forEach(idx => {
      selectedQuestions.push({
        type: 'noi',
        data: addedQuestions.value.noi[idx]
      });
    });

    if (selectedQuestions.length === 0) {
      toast.info('Vui lòng chọn ít nhất 1 câu hỏi');
      return;
    }

    // Save each question one by one
    for (let i = 0; i < selectedQuestions.length; i++) {
      const q = selectedQuestions[i];
      const payload = {
        type: q.type,
        question: q.data,
        practice_id: parseInt(props.practice_id || getQueryParam('practice_id')),
        app_id: parseInt(props.app_id || getQueryParam('app_id')),
        book_id: parseInt(props.book_id || getQueryParam('book_id')),
        week_id: parseInt(props.week_id || getQueryParam('week_id'))
      };

      const response = await axios.post(route('questions.json.saveQuestion'), payload);

      if (response.data.success) {
        savedQuestions.value.add(`${q.type}-${q.data.cau_hoi}`);
        removeSavedQuestionsFromRight(q.type, q.data.cau_hoi);
      } else {
        throw new Error(response.data.message || 'Lỗi lưu câu hỏi');
      }

      // Small delay between saves
      if (i < selectedQuestions.length - 1) {
        await new Promise(resolve => setTimeout(resolve, 500));
      }
    }

    toast.success(`✓ Lưu ${selectedQuestions.length} câu hỏi thành công!`);
    // Clear selection
    selectedAddedQuestions.value = { chon: new Set(), sx: new Set(), noi: new Set() };
  } catch (error) {
    console.error('Save error:', error);
    toast.error(error.response?.data?.message || error.message || 'Lỗi lưu câu hỏi');
  } finally {
    loading.value = false;
  }
};

const addSelectedQuestions = (type) => {
  const selected = Array.from(selectedByType.value[type]);
  selected.forEach(idx => {
    const question = JSON.parse(JSON.stringify(quizResult.value[type][idx]));
    // Check if already added (by comparing question content)
    const alreadyAdded = addedQuestions.value[type].some(q =>
      JSON.stringify(q) === JSON.stringify(question)
    );
    if (!alreadyAdded) {
      addedQuestions.value[type].push(question);
    }
  });
  toast.success(`✓ Đã thêm ${selected.length} câu hỏi`);
  // Clear selection after adding
  selectedByType.value[type].clear();
};

const startEdit = (type, index, fromAdded = false) => {
  editingQuestion.value = { type, index, fromAdded };
  let original;
  if (fromAdded) {
    original = addedQuestions.value[type][index];
  } else {
    original = quizResult.value[type][index];
  }
  editingData.value = JSON.parse(JSON.stringify(original));
};

const cancelEdit = () => {
  editingQuestion.value = null;
  editingData.value = null;
};

const saveEdit = () => {
  if (editingQuestion.value) {
    const { type, index, fromAdded } = editingQuestion.value;
    if (fromAdded) {
      addedQuestions.value[type][index] = editingData.value;
    } else {
      quizResult.value[type][index] = editingData.value;
    }
    toast.success('Đã cập nhật câu hỏi');
    cancelEdit();
  }
};

const deleteQuestion = (type, index) => {
  quizResult.value[type].splice(index, 1);
  selectedByType.value[type].delete(index);
  toast.info('Đã xóa câu hỏi');
};

const handleSaveQuestions = async () => {
  try {
    loading.value = true;
    generationStatus.value = '⏳ Đang lưu câu hỏi...';

    // Collect all added questions
    const allQuestions = [];

    // Collect added Chọn questions
    addedQuestions.value.chon.forEach(q => {
      allQuestions.push({
        type: 'chon',
        data: q
      });
    });

    // Collect added Sắp xếp questions
    addedQuestions.value.sx.forEach(q => {
      allQuestions.push({
        type: 'sx',
        data: q
      });
    });

    // Collect added Nối questions
    addedQuestions.value.noi.forEach(q => {
      allQuestions.push({
        type: 'noi',
        data: q
      });
    });

    if (allQuestions.length === 0) {
      toast.info('Không có câu hỏi nào để lưu');
      loading.value = false;
      return;
    }

    // Save each question one by one
    for (let i = 0; i < allQuestions.length; i++) {
      const q = allQuestions[i];
      const payload = {
        type: q.type,
        question: q.data,
        practice_id: parseInt(props.practice_id || getQueryParam('practice_id')),
        app_id: parseInt(props.app_id || getQueryParam('app_id')),
        book_id: parseInt(props.book_id || getQueryParam('book_id')),
        week_id: parseInt(props.week_id || getQueryParam('week_id'))
      };

      const response = await axios.post(route('questions.json.saveQuestion'), payload);

      if (response.data.success) {
        savedQuestions.value.add(`${q.type}-${q.data.cau_hoi}`);
        removeSavedQuestionsFromRight(q.type, q.data.cau_hoi);
      } else {
        throw new Error(response.data.message || 'Lỗi lưu câu hỏi');
      }

      // Small delay between saves
      if (i < allQuestions.length - 1) {
        await new Promise(resolve => setTimeout(resolve, 500));
      }
    }

    generationStatus.value = '✓ Câu hỏi đã lưu thành công!';
    toast.success('✓ Lưu ' + allQuestions.length + ' câu hỏi thành công!');

    // Wait 2 seconds then auto-show history modal
    await new Promise(resolve => setTimeout(resolve, 2000));

    // Auto-show history modal
    if (historyModal.value) {
      historyModal.value.openModal();
    }

    // Clear addedQuestions after saving (should already be empty due to removeSavedQuestionsFromRight)
    selectedByType.value = { chon: new Set(), sx: new Set(), noi: new Set() };
    selectedAddedQuestions.value = { chon: new Set(), sx: new Set(), noi: new Set() };
    selectedQuestionTab.value = 'chon';
    generationStatus.value = '';
  } catch (error) {
    console.error('Save questions error:', error);
    generationStatus.value = '✗ ' + (error.response?.data?.message || error.message || 'Lỗi lưu câu hỏi');
    toast.error(error.response?.data?.message || error.message || 'Lỗi lưu câu hỏi');
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  return () => {
    pollingIntervals.value.forEach(interval => clearInterval(interval));
  };
});
</script>

<template>
  <Head title="Tạo bài giảng" />

  <SchoolLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Tạo bài giảng</h2>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-5xl px-4">
        <!-- Header Navigation -->
        <div class="mb-6 flex items-center justify-between">
          <Link :href="route('lessons.index', { app_id: props.app_id || getQueryParam('app_id'), book_id: props.book_id || getQueryParam('book_id'), week_id: props.week_id || getQueryParam('week_id') })" class="text-blue-600 hover:text-blue-800 font-medium">
            ← Quay lại
          </Link>
          <h1 class="text-2xl font-bold text-blue-600">Tạo nội dung bằng AI</h1>
          <LessonDraftHistory
            ref="historyModal"
            :practice-id="parseInt(props.practice_id || getQueryParam('practice_id'))"
            @approve="loadDrafts"
          />
        </div>

        <!-- Main Content -->
        <div class="space-y-6">
          <!-- Form -->
          <GenerateQuizForm
            :generate-types="generateTypes"
            :content-text="contentText"
            :selected-files="selectedFiles"
            :muc-do="mucDo"
            :lop="lop"
            :mon="mon"
            :voice="voice"
            :counts="counts"
            :can-generate="canGenerate"
            :generating-quiz="generatingQuiz"
            :generating-text="generatingText"
            :generating-audio="generatingAudio"
            :generation-status="generationStatus"
            :voices="voices"
            @toggle-type="toggleType"
            @update-content="contentText = $event"
            @update-files="selectedFiles = Array.from($event)"
            @update-muc-do="mucDo = $event"
            @update-lop="lop = $event"
            @update-mon="mon = $event"
            @update-voice="voice = $event"
            @update-counts="counts = $event"
            @generate="generateLesson"
          />

          <!-- Results -->
          <GenerateQuizResults
            :quiz-result="quizResult"
            :selected-question-tab="selectedQuestionTab"
            :editing-question="editingQuestion"
            :selected-by-type="selectedByType"
            :loading="loading"
            @update-tab="selectedQuestionTab = $event"
            @add-questions="addSelectedQuestions"
            @save-questions="handleSaveQuestions"
            @edit="startEdit"
            @delete="deleteQuestion"
            @update-edit="editingData = $event"
            @save-edit="saveEdit"
            @cancel-edit="cancelEdit"
          />
        </div>
      </div>
    </div>
  </SchoolLayout>
</template>

<style scoped>
.prose {
  font-size: 0.875rem;
  line-height: 1.5;
}
</style>
