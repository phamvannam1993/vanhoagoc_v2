<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { ErrorMessage, Field, Form } from 'vee-validate';
import * as yup from 'yup';
import InputError from '@/Components/InputError.vue';
import { reactive, ref, defineProps, computed, onMounted, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import PreviewChooseCorrectOne from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectOne.vue';
import PreviewChooseCorrectTwo from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectTwo.vue';
import PreviewChooseCorrectThree from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectThree.vue';
import PreviewChooseCorrectFour from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectFour.vue';
import PreviewChooseCorrectFive from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectFive.vue';
import PreviewChooseCorrectSix from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectSix.vue';
import PreviewChooseCorrectSeven from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectSeven.vue';
import PreviewChooseCorrectEight from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectEight.vue';
import PreviewChooseCorrectNine from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectNine.vue';
import PreviewChooseCorrectTen from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectTen.vue';
import PreviewChooseCorrectEleven from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectEleven.vue';
import PreviewChooseCorrectTwelve from '@/Pages/SelectSample/Partials/ChooseCorrect/PreviewChooseCorrectTwelve.vue';
import PreviewDragDropThree from '@/Pages/SelectSample/Partials/DragDrop/PreviewDragDropThree.vue';
import ModalTemplate from '@/Pages/SelectSample/Template/ModalTemplate.vue';
import Editor from "@tinymce/tinymce-vue";

const props = defineProps({
    template: {
        type: Object,
    },
    practice:  {
        type: Object
    },
});

const breadcrumbs = [
    {'title': 'App', 'url': route('apps.dashboard')},
    {'title': props.practice.week.book.app.name, 'url': route('books.index', {appId: props.practice.week.book.app.id})},
    {'title': props.practice.week.book.title, 'url': route('weeks.index', {
        app_id: props.practice.week.book.app.id,
        book_id: props.practice.week.book.id
    })},
    {'title': props.practice.week.name, 'url': route('lessons.index', {
        app_id: props.practice.week.book.app.id,
        book_id: props.practice.week.book.id,
        week_id: props.practice.week.id,
    })},
    {'title': props.practice.name, 'url': route('questionEditors.index', {
        app_id: props.practice.week.book.app.id,
        book_id: props.practice.week.book.id,
        week_id: props.practice.week.id,
        practice_id: props.practice.id,
    })},
]

const optionsInput = ref([
  {
    value: 'PreviewChooseCorrectOne',
    totalInput: 2,
    componentMap: PreviewChooseCorrectOne,
  },
  {
    value: 'PreviewChooseCorrectTwo',
    totalInput: 3,
    componentMap: PreviewChooseCorrectTwo,
  },
  {
    value: 'PreviewChooseCorrectThree',
    totalInput: 4,
    componentMap: PreviewChooseCorrectThree,
  },
  {
    value: 'PreviewChooseCorrectFour',
    totalInput: 4,
    componentMap: PreviewChooseCorrectFour,
  },
  {
    value: 'PreviewChooseCorrectFive',
    totalInput: 6,
    componentMap: PreviewChooseCorrectFive,
  },
  {
    value: 'PreviewChooseCorrectSix',
    totalInput: 3,
    componentMap: PreviewChooseCorrectSix,
  },
  {
    value: 'PreviewChooseCorrectSeven',
    totalInput: 4,
    componentMap: PreviewChooseCorrectSeven,
  },
  {
    value: 'PreviewChooseCorrectEight',
    totalInput: 3,
    componentMap: PreviewChooseCorrectEight,
  },
  {
    value: 'PreviewChooseCorrectNine',
    totalInput: 4,
    componentMap: PreviewChooseCorrectNine,
  },
  {
    value: 'PreviewChooseCorrectTen',
    totalInput: 3,
    componentMap: PreviewChooseCorrectTen,
  },
  {
    value: 'PreviewChooseCorrectEleven',
    totalInput: 4,
    componentMap: PreviewChooseCorrectEleven,
  },
  {
    value: 'PreviewDragDropThree',
    totalInput: 4,
    componentMap: PreviewDragDropThree,
  },
]);
const toast = useToast();
const page = usePage();
const query = page.props.query;
const app_id = query.app_id;
const book_id = query.book_id ? query.book_id : 0;
const week_id = query.week_id ? query.week_id : 0;
const practice_id = query.practice_id;
const submittedData = ref([]);
const BASE_URL = ref(window.location.origin);
const listVoice = ref([]);
const template_id = ref(query.template_id);
const infoTemplate = ref(props.template.template);
const imagePreview = ref(props.template.image_url);
const updatedUrl = ref('');

// PCNL / NDGD
const pcnlCompetencies = ref([]);
const pcnlComponents = ref([]);
const pcnlEducationalContents = ref([]);
const pcnlIsInitialLoad = ref(true);

const loadPcnlComponents = async (competencyId) => {
    if (!competencyId) { pcnlComponents.value = []; return; }
    const res = await axios.get(route('admins.competency-components.json.list', {
        competency_id: competencyId,
        per_page: 200,
    }));
    if (res.data.status) {
        pcnlComponents.value = res.data.data.data.map(v => ({ value: v.id, label: v.name }));
    }
};

onMounted(async () => {
    getListVoice();
    let currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('template_id', template_id.value);
    updatedUrl.value = currentUrl.toString();

    const [resC, resE] = await Promise.all([
        axios.get(route('admins.competencies.json.list', { per_page: 200 })),
        axios.get(route('admins.educational-contents.json.list', { per_page: 200 })),
    ]);
    if (resC.data.status) {
        pcnlCompetencies.value = resC.data.data.data.map(v => ({ value: v.id, label: v.name }));
    }
    if (resE.data.status) {
        pcnlEducationalContents.value = resE.data.data.data.map(v => ({ value: v.id, label: v.name }));
    }
    pcnlIsInitialLoad.value = false;
});
const currentComponent = computed(() => {
  const item = optionsInput.value.find(
    (item) => item.value === infoTemplate.value.preview,
  );
  return item.componentMap || [];
});
const createListAnswer = (templateId, infoTemplate, oldListAnswer = []) => {
  let length = infoTemplate.value.number_answer;
  return Array.from({ length }, (_, index) => ({
    inputAnswer: oldListAnswer[index]?.inputAnswer || '',
    type_answer:
      infoTemplate.value?.type_answer !== null
        ? infoTemplate.value?.type_answer[0]
        : 1,
    checked: oldListAnswer[index]?.checked || false,
    image_answer_show: oldListAnswer[index]?.image_answer_show || '',
    image_answer_db: oldListAnswer[index]?.image_answer_db || '',
    audio_answer_show: oldListAnswer[index]?.audio_answer_show || '',
    audio_answer_db: oldListAnswer[index]?.audio_answer_db || '',
    nameVoice: '',
  }));
};
const form = useForm({
  title: infoTemplate.value.name ?? 'Chọn đáp án đúng',
  audio_title_db: '',
  audio_title_show: '',
  video_title_db: '',
  video_title_show: '',
  isYouTubeVideo: false,
  question: '',
  question_type:
    infoTemplate.value?.type_question !== null
      ? infoTemplate.value?.type_question[0]
      : 1,
  image_question_show: '',
  image_question_db: '',
  audio_question_show: '',
  audio_question_db: '',
  template_id: template_id.value,
  type: props.template.type,
  listAnswer: reactive(
    createListAnswer(template_id.value, infoTemplate, []) || [],
  ),
  reading_type: 2,
  image_reading_show: '',
  image_reading_db: '',
  image_background_show: '',
  image_background_db: '',
  file_reading_name: '',
  file_background_name: '',
  pcnl: '',
  ndgd: '',
  competency_id: null,
  competency_component_id: null,
  pcnl_detail: '',
  educational_content_id: null,
  ndgd_requirement: '',
  video_title_name: '',
  content: "",
  image_show: "",
  image_db: "",
  dataNew: reactive({
    template_current: template_id.value,
    new_type: props.template.type,
    new_template_id: '',
    app_id: app_id,
  }),
});
const addInput = () => {
  form.listAnswer.push({
    inputAnswer: '',
    type_answer:
      infoTemplate.value?.type_answer !== null
        ? infoTemplate.value?.type_answer[0]
        : 1,
    checked: false,
    image_answer_show: '',
    image_answer_db: '',
    audio_answer_show: '',
    audio_answer_db: '',
  });
};
const removeInput = (index) => {
  form.listAnswer.splice(index, 1);
};
const optionsType = ref([
  {
    value: 1,
    label: 'Text',
  },
  {
    value: 2,
    label: 'Ảnh',
  },
  {
    value: 3,
    label: 'Âm thanh',
  },
]);
const filteredOptionsQuestion = computed(() =>
  infoTemplate.value.type_question !== null
    ? optionsType.value.filter((option) =>
        infoTemplate.value.type_question.includes(option.value),
      )
    : optionsType,
);
const filteredOptionsAnswer = computed(() =>
  infoTemplate.value.type_answer !== null
    ? optionsType.value.filter((option) =>
        infoTemplate.value.type_answer.includes(option.value),
      )
    : optionsType,
);
const fileNameVideoTitle = ref('');
const isUploading = ref(false);
const popoverVisible = ref(false);
const rules = {
  name: yup.string().required('Tên trò chơi không được để trống'),
  question: yup.string().required('Câu hỏi không được để trống'),
};
const getAnswerVal = (item) => {
  let result = '';

  if (item.type_answer === 1) {
    result = item.inputAnswer;
  } else if (item.type_answer === 2) {
    result = item.image_answer_db;
  } else {
    result = item.audio_answer_db;
  }

  return result;
};
const handleSubmit = () => {
  submittedData.value = form.listAnswer.map((item) => ({
    type_answer: item.type_answer,
    answer_val: getAnswerVal(item),
    checked: item.checked,
    answer_text: item.inputAnswer
  }));
  const formData = new FormData();
  formData.append('title', form.title);
  formData.append('audio_title_db', form.audio_title_db);
  formData.append('audio_val', form.audio_title_db);
  formData.append('audio_title_show', form.audio_title_show);
  formData.append('video_title_db', form.video_title_db);
  formData.append('video_title_show', form.video_title_show);
  formData.append('question_video_url', form.video_title_db);
  formData.append('isYouTubeVideo', form.isYouTubeVideo);
  formData.append('question_type', form.question_type);
  formData.append('image_question_show', form.image_question_show);
  formData.append('image_question_db', form.image_question_db);
  formData.append('audio_question_show', form.audio_question_show);
  formData.append('audio_question_db', form.audio_question_db);
  formData.append('audio_ques_val', form.audio_question_db);
  formData.append('listAnswer', JSON.stringify(submittedData.value));
  formData.append('app_id', app_id);
  formData.append('book_id', book_id);
  formData.append('week_id', week_id);
  formData.append('practice_id', practice_id);
  formData.append('template_id', template_id.value);
  formData.append('reading_val', form.image_reading_db ?? '');
  formData.append('background', form.image_background_db ?? '');
  formData.append('file_reading_name', form.file_reading_name ?? '');
  formData.append('file_background_name', form.file_background_name ?? '');
  formData.append('content', form.content ?? '');
  formData.append('image_show', form.image_show ?? '');
  formData.append('image_db', form.image_db ?? '');
  formData.append('pcnl', form.pcnl);
  formData.append('ndgd', form.ndgd);
  formData.append('competency_id', form.competency_id ?? '');
  formData.append('competency_component_id', form.competency_component_id ?? '');
  formData.append('pcnl_detail', form.pcnl_detail);
  formData.append('educational_content_id', form.educational_content_id ?? '');
  formData.append('ndgd_requirement', form.ndgd_requirement);
  formData.append('tem_playable_id', template_id.value);
  formData.append('link', updatedUrl.value);

  if (form.question_type === 2) {
    formData.append('question_val', form.image_question_db);
  } else {
    formData.append('question_val', form.question);
  }

  axios
    .post(route('questionEditors.json.postCreateGame'), formData)
    .then((response) => {
      if (response.data.status) {
        toast.success('Thêm mới câu hỏi thành công');
        setTimeout(function () {
          location.href = route('questionEditors.index', {
            practice_id,
            app_id,
            book_id,
            week_id,
          });
        }, 500);
      } else {
        // form error
      }
    })
    .catch((error) => {
      toast.error(error);
    });
};
const getListVoice = async () => {
  const res = await axios.get(route('voice-types.list'));
  listVoice.value = res.data.data;
};
const openPreview = ref(false);
const showPreview = () => {
  openPreview.value = true;
};

// Title
const fileAudioTitle = ref(null);
const triggerAudioTitle = () => {
  fileAudioTitle.value.click();
  popoverVisible.value = false;
};
const handleUploadAudio = async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  try {
    popoverVisible.value = false;
    isUploading.value = true;
    const formData = new FormData();
    formData.append('file', file);
    const res = await axios.post('/api/upload-file', formData);
    form.audio_title_show = res.data.s3_file_url;
    form.audio_title_db = res.data.file_url;
  } catch (error) {
    console.error('Lỗi upload:', error);
  } finally {
    isUploading.value = false;
  }
};
const removeAudioTitle = () => {
  form.audio_title_show = '';
  form.audio_title_db = '';
};
const textToSpeech = async (item) => {
  const params = {
    text: form.title,
    voice_type: item.type,
  };
  try {
    isUploading.value = true;
    popoverVisible.value = false;
    const res = await axios.post(route('voice-types.textToSpeech'), params);
    form.audio_title_show = res.data.s3_url;
    form.audio_title_db = res.data.url;
  } catch (error) {
    console.error('Lỗi upload:', error);
  } finally {
    isUploading.value = false;
  }
};
const fileVideoTitle = ref(null);
const openModalVideoTitle = ref(false);
const inputUrl = ref('');
const videoUrl = ref(null);
const isYouTubeVideo = ref(false);
const youtubeEmbedUrl = ref('');
const tempUrlFileVideoTitle = ref('');
const tempFileNameTitle = ref('');
const showModalVideoTitle = () => {
  openModalVideoTitle.value = true;
};
const triggerFileInput = () => {
  fileVideoTitle.value.click();
};
const handleOk = () => {
  fileNameVideoTitle.value = tempFileNameTitle.value;
  form.video_title_name = tempFileNameTitle.value;
  if (isYouTubeVideo.value) {
    form.video_title_db = youtubeEmbedUrl.value;
    form.video_title_show = youtubeEmbedUrl.value;
    form.isYouTubeVideo = isYouTubeVideo.value;
  } else {
    form.video_title_show = videoUrl.value;
    form.video_title_db = tempUrlFileVideoTitle.value;
    form.isYouTubeVideo = isYouTubeVideo.value;
  }
  openModalVideoTitle.value = false;
};
const updateVideoPreview = () => {
  const url = inputUrl.value.trim();

  if (isValidYouTubeUrl(url)) {
    isYouTubeVideo.value = true;
    const videoId = getYouTubeVideoId(url);
    if (videoId) {
      youtubeEmbedUrl.value = `https://www.youtube.com/embed/${videoId}`;
      videoUrl.value = null;
      tempFileNameTitle.value = `YouTube Video: ${videoId}`;
    } else {
      videoUrl.value = null;
    }
  } else if (isValidVideoUrl(url)) {
    isYouTubeVideo.value = false;
    videoUrl.value = url;
    youtubeEmbedUrl.value = '';
  } else {
    isYouTubeVideo.value = false;
    videoUrl.value = null;
    youtubeEmbedUrl.value = '';
  }
};
const isValidYouTubeUrl = (url) => {
  // Kiểm tra URL có phải là video YouTube không
  const youtubePattern =
    /^(https?:\/\/)?(www\.)?(youtube|youtu|vimeo)\.(com|be)\/.+/;
  return youtubePattern.test(url);
};
const isValidVideoUrl = (url) => {
  // Kiểm tra URL có phải là video (bằng cách kiểm tra file extension)
  const videoPattern = /\.(mp4|webm|ogg)$/i;
  return videoPattern.test(url);
};
const getYouTubeVideoId = (url) => {
  // Lấy ID video YouTube từ URL
  const youtubeIdPattern =
    /(?:youtube\.com\/(?:[^/\n\s]+\/\S+\/|\S+\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
  const match = url.match(youtubeIdPattern);
  return match ? match[1] : null;
};
const handleFileVideoTitle = async (event) => {
  const file = event.target.files[0];
  if (file && file.type.startsWith('video/')) {
    try {
      const formData = new FormData();
      formData.append('file', file);
      isUploading.value = true;
      const res = await axios.post('/api/upload-file', formData);
      videoUrl.value = res.data.s3_file_url;
      tempUrlFileVideoTitle.value = res.data.file_url;
      tempFileNameTitle.value = file.name;
      isYouTubeVideo.value = false;
      youtubeEmbedUrl.value = '';
    } catch (error) {
      console.error('Lỗi upload:', error);
    } finally {
      isUploading.value = false;
    }
  } else {
    console.log('Error file');
  }
};

// Question
const handleChangeTypeQuestion = (value) => {
  form.question_type = value;
  form.image_question_db = '';
  form.image_question_show = '';
  form.audio_question_show = '';
  form.audio_question_db = '';
};
const fileInputQuestion = ref(null);
const fileAudioQuestion = ref(null);
const popoverVisibleQuestion = ref(false);
const triggerAudioQuestion = () => {
  fileAudioQuestion.value.click();
  popoverVisibleQuestion.value = false;
};
const triggerFileInputQuestion = () => {
  if (fileInputQuestion.value) {
    fileInputQuestion.value.value = '';
    fileInputQuestion.value.click();
    popoverVisibleQuestion.value = false;
  }
};
const handleUploadAudioQuestion = async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  popoverVisibleQuestion.value = false;
  try {
    const formData = new FormData();
    formData.append('file', file);
    isUploading.value = true;
    const res = await axios.post('/api/upload-file', formData);
    form.audio_question_show = res.data.s3_file_url;
    form.audio_question_db = res.data.file_url;
  } catch (error) {
    console.error('Lỗi upload:', error);
  } finally {
    isUploading.value = false;
  }
};
const handleFileChangeQuestion = async (event) => {
  const file = event.target.files[0];

  if (!file) return;
  const isImage = file.type.startsWith('image/');

  if (!isImage) {
    return;
  }
  try {
    const formData = new FormData();
    formData.append('file', file);
    isUploading.value = true;
    const res = await axios.post('/api/upload-file', formData);
    form.image_question_show = res.data.s3_file_url;
    form.image_question_db = res.data.file_url;
  } catch (error) {
    console.error('Lỗi upload:', error);
  } finally {
    isUploading.value = false;
  }
};
const removeImageQuestion = () => {
  form.image_question_show = '';
  form.image_question_db = '';
};
const removeAudioQuestion = () => {
  form.audio_question_show = '';
  form.audio_question_db = '';
};
const textToSpeechQuestion = async (item) => {
  const params = {
    text: form.question,
    voice_type: item.type,
  };
    try {
        isUploading.value = true;
        popoverVisibleQuestion.value = false;
        const res = await axios.post(route('voice-types.textToSpeech'), params);
        form.audio_question_show = res.data.s3_url;
        form.audio_question_db = res.data.url;
    } catch (error) {
        console.error('Lỗi upload:', error);
    } finally {
        isUploading.value = false;
    }
};

// Answer
const handleChangeTypeAnswer = (index) => {
  const item = form.listAnswer.find((item, indexList) => indexList === index);
  item.audio_answer_db = '';
  item.audio_answer_show = '';
  item.image_answer_show = '';
  item.image_answer_db = '';
};
const openPopoverIndex = ref(null);
const fileInputAnswer = ref([]);
const fileAudioAnswer = ref([]);
const triggerFileInputAnswer = (index) => {
  const inputElement = fileInputAnswer.value[index];
  if (inputElement) {
    inputElement.value = '';
    inputElement.click();
  } else {
    console.error('Không tìm thấy phần tử input tại index:', index);
  }
};
const handlePopoverChange = (visible, index) => {
  if (visible) {
    openPopoverIndex.value = index;
  } else {
    openPopoverIndex.value = null;
  }
};
const handleFileChangeAnswer = async (index) => {
  const inputElement = fileInputAnswer.value[index];
  if (inputElement && inputElement.files.length > 0) {
    const file = inputElement.files[0];
    const item = form.listAnswer.find((item, indexList) => indexList === index);
    try {
      const formData = new FormData();
      formData.append('file', file);
      isUploading.value = true;
      const res = await axios.post('/api/upload-file', formData);
      item.image_answer_show = res.data.s3_file_url;
      item.image_answer_db = res.data.file_url;
    } catch (error) {
      console.error('Lỗi upload:', error);
    } finally {
      isUploading.value = false;
    }
  } else {
    console.error('Không có file nào được chọn tại index:', index);
  }
};
const triggerAudioAnswer = (index) => {
  const inputElement = fileAudioAnswer.value[index];
  if (inputElement) {
    inputElement.value = '';
    inputElement.click();
    openPopoverIndex.value = null;
  } else {
    console.error('Không tìm thấy phần tử input tại index:', index);
  }
};
const showTitle = ref(false);
const handleUploadAudioAnswer = async (index) => {
  const inputElement = fileAudioAnswer.value[index];
  if (inputElement && inputElement.files.length > 0) {
    const file = inputElement.files[0];
    const item = form.listAnswer.find((item, indexList) => indexList === index);
    item.nameVoice = file.name;
    try {
      isUploading.value = true;
      const formData = new FormData();
      formData.append('file', file);
      const res = await axios.post('/api/upload-file', formData);
      item.audio_answer_show = res.data.s3_file_url;
      item.audio_answer_db = res.data.file_url;
    } catch (error) {
      console.error('Lỗi upload:', error);
    } finally {
      isUploading.value = false;
    }
  } else {
    console.error('Không có file nào được chọn tại index:', index);
  }
};
const textToSpeechAnswer = async (item, index) => {
  const answer = form.listAnswer.find((item, indexList) => indexList === index);
  answer.nameVoice = item.name;
  const params = {
    text: answer.inputAnswer,
    voice_type: item.type,
  };
  try {
    openPopoverIndex.value = null;
    isUploading.value = true;
    const res = await axios.post(route('voice-types.textToSpeech'), params);
    answer.audio_answer_show = res.data.s3_url;
    answer.audio_answer_db = res.data.url;
  } catch (error) {
    console.error('Lỗi upload:', error);
  } finally {
    isUploading.value = false;
  }
};
const removeAudioAnswer = (index) => {
  const item = form.listAnswer.find((item, indexList) => indexList === index);
  item.audio_answer_show = '';
  item.audio_answer_db = '';
};
const removeImageAnswer = (index) => {
  const item = form.listAnswer.find((item, indexList) => indexList === index);
  item.image_answer_show = '';
  item.image_answer_db = '';
};

// Bài đọc
const fileInputReading = ref(null);
const errorImage = ref("");
const errorContent = ref("");
const type_text = ref(1);
const active_text = ref(1);
const active_image = ref(2);
const apiKey = import.meta.env.VITE_TINY_MCE_API_KEY;
const openModalReading = ref(false);
const addReading = () => {
    openModalReading.value = true;
};
const triggerFileInputReading = () => {
    fileInputReading.value.value = "";
    fileInputReading.value.click();
};
const removeContent = () => {
    if (type_text.value === 1) {
        form.content = "";
    } else {
        form.image_show = "";
        form.image_db = "";
    }
};
const configEditor = ref({});
const tinyMCEEditor = ref(null);
configEditor.value = {
    height: "50vh",
    plugins: "lists link image table code help wordcount",
    menubar: "file edit view format tools table",
    statusbar: false,
    language: "vi",

    font_family_formats:
        "Arial=arial,helvetica,sans-serif;" +
        "Times New Roman=times new roman,times,serif;" +
        "Courier New=courier new,courier,monospace;" +
        "Georgia=georgia,serif;" +
        "Verdana=verdana,sans-serif;" +
        "Gilroy=gilroy,sans-serif;" +
        "Roboto=roboto, sans-serif;", // Thêm các font family ở đây
    paste_data_images: false,
    setup: (editor) => {
        editor.on("paste", (event) => {
            const clipboardData = event.clipboardData || window.clipboardData;
            if (clipboardData && clipboardData.items) {
                for (let item of clipboardData.items) {
                    if (item.type.startsWith("image/")) {
                        event.preventDefault();
                        console.warn("⚠️ Không thể dán hình ảnh!");
                        return;
                    }
                }
            }
        });

        // Chặn kéo thả ảnh
        editor.on("dragover drop", (event) => {
            const items = event.dataTransfer?.items;
            if (items) {
                for (let item of items) {
                    if (item.type.startsWith("image/")) {
                        event.preventDefault();
                        console.warn("⚠️ Không thể kéo thả hình ảnh!");
                        return;
                    }
                }
            }
        });
    }
};
const uploadFile = async () => {
    const file = event.target.files[0];

    if (!file) return;
    const isImage = file.type.startsWith("image/");

    if (!isImage) {
        return;
    }
    try {
        const formData = new FormData();
        formData.append("file", file);
        isUploading.value = true;
        const res = await axios.post("/api/upload-file", formData);
        form.image_show = res.data.s3_file_url;
        form.image_db = res.data.file_url;
    } catch (error) {
        console.error("Lỗi upload:", error);
    } finally {
        isUploading.value = false;
    }
};
const activeImage = () => {
    type_text.value = 2;
    active_image.value = 1;
    active_text.value = 2;
    errorContent.value = "";
};
const activeText = () => {
    type_text.value = 1;
    active_image.value = 2;
    active_text.value = 1;
    errorImage.value = "";
};
const saveReading = () => {
    openModalReading.value = false;
}

// Background
const fileInputBackground = ref(null);
const triggerFileInputBackground = () => {
  if (fileInputBackground.value) {
    fileInputBackground.value.value = '';
    fileInputBackground.value.click();
  }
};
const handleFileChangeBackground = async (event) => {
  const file = event.target.files[0];

  if (!file) return;
  const isImage = file.type.startsWith('image/');

  if (!isImage) {
    return;
  }
  try {
    isUploading.value = true;
    const formData = new FormData();
    formData.append('file', file);
    const res = await axios.post('/api/upload-file', formData);
    form.image_background_show = file.name;
    form.file_background_name = file.name;
    form.image_background_db = res.data.file_url;
  } catch (error) {
    console.error('Lỗi upload:', error);
  } finally {
    isUploading.value = false;
  }
};
const removeImageBackground = () => {
  form.image_background_show = '';
  form.image_background_db = '';
  form.file_background_name = '';
};
const filterOption = (input, option) => {
  return option.value.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};
const getQuestionMaxLength = () => {
  return infoTemplate.value?.max_length_question ? parseInt(infoTemplate.value.max_length_question) : 255;
};
const getAnswerMaxLength = () => {
  return infoTemplate.value?.max_length_answer ? parseInt(infoTemplate.value.max_length_answer) : 150;
};
const openTemplateModal = ref(false);
const showTemplate = () => {
  openTemplateModal.value = true;
};
// Theo dõi sự thay đổi
watch(() => form.competency_id, (val) => {
    if (pcnlIsInitialLoad.value) return;
    form.competency_component_id = null;
    loadPcnlComponents(val);
});

watch(
  () => form.dataNew.new_template_id,
  async (newValue, oldValue) => {
    if (!newValue || newValue === oldValue) return;
    template_id.value = newValue;
    let currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('template_id', newValue);
    updatedUrl.value = currentUrl.toString();
    const res = await axios.get(
      route('templates.json.detail', { question_template_id: newValue }),
    );
    infoTemplate.value = res.data.data.template;
    imagePreview.value = res.data.data.image_url;
    let newListAnswer = createListAnswer(
      newValue,
      infoTemplate,
      form.listAnswer,
    );
    form.listAnswer.splice(0, form.listAnswer.length, ...newListAnswer);
  },
);
const goBack = () => {
  window.location = route('questionEditors.createExercise', {
    app_id: app_id,
    book_id: book_id,
    practice_id: practice_id,
    week_id: week_id,
    template_id: props.template.id,
  });
};
</script>

<template>
  <Head title="New Question" />

  <SchoolLayout :breadcrumbs=breadcrumbs>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        New Question
      </h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-full md:w-11/12">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">Tạo câu hỏi</h1>
        <Form v-slot="{ errors }" @submit="handleSubmit" class="w-full">
          <div class="flex w-full">
            <div class="content-page w-full">
              <div
                class="custom-height relative mt-6 w-full rounded-2xl border border-solid border-blue-400 bg-white pt-2 max-md:max-w-full"
              >
                <div class="mt-2 flex flex-col md:flex-row w-full gap-4">
                  <div class="w-[4rem] font-bold">Tiêu đề:</div>
                  <div class="flex w-11/12 gap-4">
                    <Field
                      name="title"
                      :rules="rules.title"
                      v-model="form.title"
                    >
                      <a-input
                        placeholder="Bạn điền tên"
                        :allow-clear="true"
                        v-model:value="form.title"
                        size="large"
                        class="w-2/4"
                      >
                      </a-input>
                    </Field>
                    <ErrorMessage class="text-sm text-red-600" name="title" />
                    <InputError class="mt-2" :message="form.errors.title" />
                    <div class="flex items-center">
                      <div
                        class="flex items-center gap-2"
                        v-if="form.audio_title_show"
                      >
                        <img
                          class="h-[30px] w-[30px] cursor-pointer"
                          src="/images/icon-sound.png"
                          alt=""
                          @click="triggerAudioTitle"
                        />
                        <audio
                          ref="audioPlayer"
                          :src="form.audio_title_show"
                          controls
                        ></audio>
                        <img
                          @click="removeAudioTitle"
                          class="h-[30px] w-[30px] cursor-pointer"
                          src="/images/icon-game-choose-correct/icon-delete.png"
                          alt=""
                        />
                      </div>
                      <a-popover
                        v-model:open="popoverVisible"
                        v-else
                        trigger="click"
                      >
                        <template #content>
                          <div
                            @click="triggerAudioTitle"
                            class="cursor-pointer border border-transparent p-2 transition duration-300 hover:rounded-xl hover:bg-[#d9d9d9]"
                          >
                            Tải file âm thanh lên
                          </div>
                          <input
                            type="file"
                            accept="audio/*"
                            class="hidden"
                            ref="fileAudioTitle"
                            @change="handleUploadAudio"
                          />
                          <div
                            v-for="(item, index) in listVoice"
                            :key="index"
                            class="cursor-pointer border border-transparent p-2 transition duration-300 hover:rounded-xl hover:bg-[#d9d9d9]"
                          >
                            <span @click="textToSpeech(item)">{{
                              item.name
                            }}</span>
                          </div>
                        </template>
                        <img
                          class="h-[30px] w-[30px] cursor-pointer"
                          src="/images/icon-game-choose-correct/icon-sound-grey.png"
                          alt=""
                        />
                      </a-popover>
                    </div>
                    <div class="flex items-center">
                      <img
                        v-if="fileNameVideoTitle"
                        class="cursor-pointer"
                        src="/images/icon-game-choose-correct/icon-video-new.png"
                        alt=""
                        @click="showModalVideoTitle"
                      />
                      <img
                        v-else
                        class="cursor-pointer"
                        src="/images/icon-game-choose-correct/upload-video.png"
                        alt=""
                        @click="showModalVideoTitle"
                      />
                      <span class="ml-2" v-if="fileNameVideoTitle">
                        {{ fileNameVideoTitle }}
                      </span>
                      <a-modal v-model:open="openModalVideoTitle" :width="1000">
                        <template #title>
                          <h1 class="text-[18px] font-bold text-[#000000]">
                            Câu hỏi video
                          </h1>
                        </template>
                        <div class="mt-16 flex justify-center gap-4">
                          <div>
                            <a-input
                              v-model:value="inputUrl"
                              size="large"
                              placeholder="Nhập link:"
                              class="custom-input h-[38px] rounded-2xl"
                              @input="updateVideoPreview"
                            />
                          </div>
                          <div class="relative flex">
                            <div class="flex items-center gap-4">
                              <img
                                class="h-[38px] cursor-pointer"
                                src="/images/icon-select.png"
                                alt=""
                                @click="triggerFileInput"
                              />
                              <span v-if="!videoUrl && !youtubeEmbedUrl">
                                Không có tệp nào nào được chọn
                              </span>
                              <input
                                type="file"
                                accept="video/*"
                                class="hidden"
                                ref="fileVideoTitle"
                                @change="handleFileVideoTitle"
                              />
                            </div>
                          </div>
                        </div>
                        <div class="mt-4 flex justify-center">
                          <div v-if="isYouTubeVideo">
                            <!-- Nhúng video YouTube -->
                            <iframe
                              :src="youtubeEmbedUrl"
                              frameborder="0"
                              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                              allowfullscreen
                              width="600"
                              height="300"
                            >
                            </iframe>
                          </div>
                          <div v-if="videoUrl">
                            <!-- Nếu không phải YouTube, hiển thị video bình thường -->
                            <video
                              :src="videoUrl"
                              controls
                              width="600"
                              height="300"
                            />
                          </div>
                        </div>
                        <template #footer>
                          <a-button
                            key="submit"
                            type="primary"
                            @click="handleOk"
                            >Xác nhận
                          </a-button>
                        </template>
                      </a-modal>
                    </div>
                  </div>
                </div>
                <div class="mt-2 flex flex-col md:flex-row w-full gap-4">
                  <div class="w-[4rem] font-bold">Câu hỏi:</div>
                  <div class="w-4/5">
                    <div class="w-2/4">
                      <a-select
                        class="input-search w-1/2"
                        v-model:value="form.question_type"
                        show-search
                        placeholder="Text"
                        size="large"
                        :options="filteredOptionsQuestion"
                        :filter-option="filterOption"
                        @change="handleChangeTypeQuestion"
                      ></a-select>
                    </div>
                    <div class="mt-[0.125rem] flex gap-4">
                      <a-textarea
                        v-if="
                          form.question_type === 1 || form.question_type === 3
                        "
                        placeholder="Bạn nhập nội dung hoặc tải ảnh ở đây"
                        :allow-clear="true"
                        v-model:value="form.question"
                        :rows="3"
                        class="w-full"
                      >
                      </a-textarea>
                      <div v-if="form.question_type !== 3" class="flex justify-between items-center text-xs mt-[2px]">
                        <span v-if="form.question.length > getQuestionMaxLength()" class="text-red-500 font-medium">Vượt ký tự, chọn Âm thanh</span>
                        <span v-else></span>
                        <span :class="form.question.length > getQuestionMaxLength() ? 'text-red-500 font-medium' : 'text-gray-400'">{{ form.question.length }}/{{ getQuestionMaxLength() }}</span>
                      </div>
                      <div class="flex gap-2" v-if="form.question_type === 2">
                        <img
                          v-if="form.image_question_show"
                          :src="form.image_question_show"
                          @click="triggerFileInputQuestion"
                          alt="Preview"
                          class="h-[212px] w-[376px] cursor-pointer object-contain"
                        />
                        <img
                          v-else
                          class="cursor-pointer"
                          src="/images/icon-select-image.png"
                          alt=""
                          @click="triggerFileInputQuestion"
                        />
                        <input
                          id="file-input"
                          type="file"
                          accept="image/*"
                          class="hidden"
                          ref="fileInputQuestion"
                          @change="handleFileChangeQuestion"
                        />
                        <img
                          v-if="form.image_question_show"
                          @click="removeImageQuestion"
                          class="h-[30px] w-[30px] cursor-pointer"
                          src="/images/icon-game-choose-correct/icon-delete.png"
                          alt=""
                        />
                      </div>
                      <div v-if="form.question_type === 3">
                        <div
                          v-if="form.audio_question_show"
                          class="inline-grid gap-y-2"
                        >
                          <div class="flex items-center gap-2">
                            <img
                              class="h-[30px] w-[30px] cursor-pointer"
                              src="/images/icon-sound.png"
                              alt=""
                            />
                            <audio
                              ref="audioPlayer"
                              :src="form.audio_question_show"
                              controls
                            ></audio>
                          </div>
                          <img
                            @click="removeAudioQuestion"
                            src="/images/icon-game-choose-correct/icon-delete.png"
                            alt=""
                            class="cursor-pointer"
                          />
                        </div>
                        <a-popover
                          v-model:open="popoverVisibleQuestion"
                          v-else
                          trigger="click"
                        >
                          <template #content>
                            <div
                              @click="triggerAudioQuestion"
                              class="cursor-pointer border border-transparent p-2 transition duration-300 hover:rounded-xl hover:bg-[#d9d9d9]"
                            >
                              Tải file âm thanh lên
                            </div>
                            <input
                              type="file"
                              accept="audio/*"
                              class="hidden"
                              ref="fileAudioQuestion"
                              @change="handleUploadAudioQuestion"
                            />
                            <div
                              v-for="(item, index) in listVoice"
                              :key="index"
                              class="cursor-pointer border border-transparent p-2 transition duration-300 hover:rounded-xl hover:bg-[#d9d9d9]"
                            >
                              <span @click="textToSpeechQuestion(item)">
                                {{ item.name }}</span
                              >
                            </div>
                          </template>
                          <img
                            class="h-[30px] w-[30px] cursor-pointer"
                            src="/images/icon-game-choose-correct/icon-sound-grey.png"
                            alt=""
                          />
                        </a-popover>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="mt-2 flex flex-col md:flex-row w-full gap-4">
                  <div class="w-[4rem] font-bold">Đáp án:</div>
                  <div
                    class="mr-4 grid w-full grid-cols gap-4 md:grid-cols-3 lg:grid-cols-4"
                  >
                    <div
                      :class="`flex w-2/3 flex-col`"
                      v-for="(item, index) in form.listAnswer"
                      :key="index"
                    >
                      <div class="w-full">
                        <a-select
                          class="input-search w-[10rem]"
                          v-model:value="item.type_answer"
                          show-search
                          placeholder="Text"
                          size="large"
                          :options="filteredOptionsAnswer"
                          :filter-option="filterOption"
                          @change="handleChangeTypeAnswer(index)"
                        ></a-select>
                      </div>
                      <!-- Textarea -->
                      <a-textarea
                        v-if="item.type_answer === 1 || item.type_answer === 3"
                        placeholder="Bạn nhập nội dung hoặc tải ảnh ở đây"
                        :allow-clear="true"
                        v-model:value="item.inputAnswer"
                        :rows="3"
                        :class="`mt-[0.125rem] w-full ${item.type_answer === 1 ? 'mb-[0.4rem]' : ''}`"
                      >
                      </a-textarea>
                      <div v-if="item.type_answer !== 3" class="flex justify-between items-center text-xs mt-[2px]">
                        <span v-if="item.inputAnswer.length > getAnswerMaxLength()" class="text-red-500 font-medium">Vượt ký tự, chọn Âm thanh</span>
                        <span v-else></span>
                        <span :class="item.inputAnswer.length > getAnswerMaxLength() ? 'text-red-500 font-medium' : 'text-gray-400'">{{ item.inputAnswer.length }}/{{ getAnswerMaxLength() }}</span>
                      </div>
                      <div v-if="item.type_answer === 2">
                        <div v-if="item.image_answer_show">
                          <img
                            :src="item.image_answer_show"
                            @click="triggerFileInputAnswer(index)"
                            alt="Preview"
                            class="mb-[0.675rem] mt-1 h-[145px] w-full cursor-pointer object-cover"
                          />
                        </div>
                        <div v-else>
                          <img
                            class="mb-[0.675rem] mt-1 h-[145px] cursor-pointer"
                            src="/images/icon-select-image.png"
                            alt=""
                            @click="triggerFileInputAnswer(index)"
                          />
                        </div>
                      </div>
                      <input
                        type="file"
                        accept="image/*"
                        class="hidden"
                        ref="fileInputAnswer"
                        @change="handleFileChangeAnswer(index)"
                      />
                      <div
                        :class="`mt-2 flex items-center justify-center gap-2 ${item.type_answer === 3 && !item.audio_answer_show ? 'mt-[1.375rem]' : ''}`"
                      >
                        <div class="flex gap-2">
                          <a-checkbox
                            class="custom-checkbox"
                            v-model:checked="item.checked"
                          >
                          </a-checkbox>
                          <a-button
                            v-if="infoTemplate.add_answer === 1"
                            class="max-w-fit"
                            type="primary"
                            danger
                            @click="removeInput(index)"
                          >
                            Xóa
                          </a-button>
                        </div>
                        <img
                          v-if="
                            item.type_answer === 2 && item.image_answer_show
                          "
                          @click="removeImageAnswer(index)"
                          class="h-[30px] w-[30px] cursor-pointer"
                          src="/images/icon-game-choose-correct/icon-delete.png"
                          alt=""
                        />

                        <div
                          v-if="item.type_answer === 3"
                          class="flex items-center gap-2"
                        >
                          <div class="items-center">
                            <img
                              v-if="item.audio_answer_show"
                              class="h-[30px] w-[30px] cursor-pointer"
                              src="/images/icon-sound.png"
                              alt=""
                            />
                            <a-popover
                              :open="openPopoverIndex === index"
                              @open-change="
                                (visible) => handlePopoverChange(visible, index)
                              "
                              v-else
                              trigger="click"
                            >
                              <template #content>
                                <div
                                  @click="triggerAudioAnswer(index)"
                                  class="cursor-pointer border border-transparent p-2 transition duration-300 hover:rounded-xl hover:bg-[#d9d9d9]"
                                >
                                  Tải file âm thanh thanh lên
                                </div>
                                <div
                                  v-for="(item, indexVoice) in listVoice"
                                  :key="indexVoice"
                                  class="cursor-pointer border border-transparent p-2 transition duration-300 hover:rounded-xl hover:bg-[#d9d9d9]"
                                >
                                  <span
                                    @click="textToSpeechAnswer(item, index)"
                                  >
                                    {{ item.name }}
                                  </span>
                                </div>
                              </template>
                              <img
                                class="h-[30px] w-[30px] cursor-pointer"
                                src="/images/icon-game-choose-correct/icon-sound-grey.png"
                                alt=""
                              />
                            </a-popover>
                          </div>

                          <div class="flex items-center">
                            <div
                              v-if="item.audio_answer_show"
                              class="flex items-center gap-4"
                            >
                              <div class="relative">
                                <audio
                                  class="w-[7rem]"
                                  ref="audioPlayer"
                                  :src="item.audio_answer_show"
                                  controls
                                  @mouseenter="showTitle = true"
                                  @mouseleave="showTitle = false"
                                ></audio>
                                <div v-if="showTitle" class="tooltip absolute">
                                  {{ item.nameVoice }}
                                </div>
                              </div>
                              <img
                                v-if="
                                  item.type_answer === 3 &&
                                  item.audio_answer_show
                                "
                                @click="removeAudioAnswer(index)"
                                src="/images/icon-game-choose-correct/icon-delete.png"
                                alt=""
                                class="h-[30px] w-[30px] cursor-pointer"
                              />
                            </div>
                          </div>
                        </div>
                      </div>
                      <input
                        type="file"
                        accept="audio/*"
                        class="hidden"
                        ref="fileAudioAnswer"
                        @change="handleUploadAudioAnswer(index)"
                      />
                    </div>
                  </div>
                </div>
                <div
                  class="mt-2 text-[14px] font-semibold italic text-[#2C75E3]"
                >
                  (Tích chọn vào ô đáp án đúng)
                </div>
                <div
                  v-if="infoTemplate.add_answer === 1"
                  class="mt-2 flex w-11/12 items-center gap-4"
                >
                  <img
                    @click="addInput"
                    class="cursor-pointer"
                    src="/images/icon-plus.png"
                    alt=""
                  />
                  Thêm đáp án
                </div>
                <div class="mb-4 mt-2 flex justify-end gap-8">
                  <div class="flex w-1/4 items-center gap-4">
                    <div class="flex w-full gap-2">
                        <img v-if="!form.content && !form.image_show" @click="addReading" src="/images/reading/add-read.png" alt="" class="cursor-pointer">
                        <img v-else @click="addReading" src="/images/reading/edit-reading.png" alt="" class="cursor-pointer">
<!--                        <a-button class="custom-bg text-black" @click="addReading" size="large">-->
<!--                           Thêm bài đọc-->
<!--                        </a-button>-->
<!--                      <a-select-->
<!--                        class="input-search w-1/3"-->
<!--                        v-model:value="form.reading_type"-->
<!--                        show-search-->
<!--                        placeholder="Text"-->
<!--                        size="large"-->
<!--                        :options="optionsType"-->
<!--                        :filter-option="filterOption"-->
<!--                        disabled-->
<!--                      ></a-select>-->
<!--                      <div-->
<!--                        v-if="form.image_reading_show"-->
<!--                        class="flex items-center gap-2"-->
<!--                      >-->
<!--                        <div>{{ form.image_reading_show }}</div>-->
<!--                        <img-->
<!--                          @click="removeImageReading"-->
<!--                          class="h-[30px] w-[30px] cursor-pointer"-->
<!--                          src="/images/icon-game-choose-correct/icon-delete.png"-->
<!--                          alt=""-->
<!--                        />-->
<!--                      </div>-->
<!--                      <div v-else>-->
<!--                        <img-->
<!--                          @click="triggerFileInputReading"-->
<!--                          src="/images/icon-select.png"-->
<!--                          alt=""-->
<!--                          class="cursor-pointer"-->
<!--                        />-->
<!--                      </div>-->
<!--                      <input-->
<!--                        type="file"-->
<!--                        accept="image/*"-->
<!--                        class="hidden"-->
<!--                        ref="fileInputReading"-->
<!--                        @change="handleFileChangeReading"-->
<!--                      />-->
                    </div>
                  </div>
                  <div class="flex w-1/4 items-center gap-4">
<!--                    <div class="w-[6rem] font-bold">Ảnh nền</div>-->
                    <div class="flex w-full gap-2">
                        <div>
                            <img v-if="!form.image_background_show" @click="triggerFileInputBackground" src="/images/reading/add-background.png" alt="" class="cursor-pointer">
                            <img v-else @click="triggerFileInputBackground" src="/images/reading/edit-background.png" alt="" class="cursor-pointer">
                        </div>
                      <div
                        v-if="form.image_background_show"
                        class="flex items-center gap-2"
                      >
                        <div>{{ form.image_background_show }}</div>
                        <img
                          @click="removeImageBackground"
                          class="h-[30px] w-[30px] cursor-pointer"
                          src="/images/icon-game-choose-correct/icon-delete.png"
                          alt=""
                        />
                      </div>
                      <input
                        type="file"
                        accept="image/*"
                        class="hidden"
                        ref="fileInputBackground"
                        @change="handleFileChangeBackground"
                      />
                    </div>
                  </div>
                </div>
              </div>
              <div class="mt-4 border-t pt-4">
                <!-- PCNL -->
                <div class="mb-4">
                  <p class="font-bold mb-2">PCNL</p>
                  <div class="flex gap-3 mb-2">
                    <a-select
                      class="w-1/2"
                      placeholder="Phẩm chất năng lực"
                      v-model:value="form.competency_id"
                      :options="pcnlCompetencies"
                      allow-clear
                      size="large"
                      show-search
                      :filter-option="(input, opt) => opt.label.toLowerCase().includes(input.toLowerCase())"
                    />
                    <a-select
                      class="w-1/2"
                      placeholder="Thành phần năng lực"
                      v-model:value="form.competency_component_id"
                      :options="pcnlComponents"
                      :disabled="!form.competency_id"
                      allow-clear
                      size="large"
                      show-search
                      :filter-option="(input, opt) => opt.label.toLowerCase().includes(input.toLowerCase())"
                    />
                  </div>
                  <a-input
                    placeholder="Biểu hiện cụ thể của thành phần năng lực"
                    v-model:value="form.pcnl_detail"
                    size="large"
                  />
                </div>
                <!-- NDGD -->
                <div>
                  <p class="font-bold mb-2">NDGD</p>
                  <a-select
                    class="w-full mb-2"
                    placeholder="Nội dung giáo dục"
                    v-model:value="form.educational_content_id"
                    :options="pcnlEducationalContents"
                    allow-clear
                    size="large"
                    show-search
                    :filter-option="(input, opt) => opt.label.toLowerCase().includes(input.toLowerCase())"
                  />
                  <a-input
                    placeholder="Yêu cầu cần đạt của NDGD"
                    v-model:value="form.ndgd_requirement"
                    size="large"
                  />
                </div>
              </div>
            </div>
          </div>
          <div class="mt-4 flex flex-wrap gap-4">
            <Link
              :href="
                route('questionEditors.index', {
                  app_id: app_id,
                  book_id: book_id,
                  practice_id: practice_id,
                  week_id: week_id,
                })
              "
            >
              <a-button class="custom-bg text-black" size="large">
                Danh sách
              </a-button>
            </Link>
            <!--                        <a-button-->
            <!--                            @click="showPreview"-->
            <!--                            class="custom-bg text-black"-->
            <!--                            size="large"-->
            <!--                        >-->
            <!--                            Hướng dẫn-->
            <!--                        </a-button>-->
            <a-button
              class="text-white"
              size="large"
              type="primary"
              html-type="submit"
            >
              Xác nhận
            </a-button>
            <a-button @click="goBack" class="custom-bg text-black" size="large">
              Quay lại
            </a-button>
            <a-button
              @click="showTemplate"
              class="custom-bg text-black"
              size="large"
            >
              Đổi mẫu
            </a-button>
            <a-button
              @click="showPreview"
              class="custom-bg text-black"
              size="large"
            >
              Preview
            </a-button>
          </div>
        </Form>
      </div>
    </div>
    <a-modal v-model:open="openModalReading" :width="1000">
          <template #title>Thêm bài đọc</template>
          <div class="flex gap-2 w-full">
              <div class="app-page w-full py-4">
                  <div class="content-page mx-auto w-full">
                      <div class="flex mt-4 gap-4">
                          <div>
                              <img @click="activeText" class="cursor-pointer" v-if="active_text === 1"
                                   src="/images/reading/type_text_active.png" alt="">
                              <img @click="activeText" class="cursor-pointer" v-if="active_image === 1"
                                   src="/images/reading/type_text_deactive.png" alt="">
                          </div>
                          <div>
                              <img @click="activeImage" class="cursor-pointer" v-if="active_image === 1"
                                   src="/images/reading/type_image_active.png" alt="">
                              <img @click="activeImage" class="cursor-pointer" v-if="active_text === 1"
                                   src="/images/reading/type_image_deactive.png" alt="">
                          </div>
                      </div>
                      <div class="mt-4">
                          <Form>
                              <Editor
                                  v-if="type_text === 1"
                                  v-model="form.content"
                                  :api-key="apiKey"
                                  :init="configEditor"
                                  ref="tinyMCEEditor"
                              />
                              <div v-if="errorContent" class="mt-4 text-red-600">
                                  {{ errorContent }}
                              </div>
                              <div v-if="type_text !== 1"
                                   class="flex justify-center h-[70vh] items-center rounded-2xl border border-solid border-blue-400 bg-white">
                                  <img v-if="form.image_show" class="h-[60vh]" :src="form.image_show" alt="">
                                  <div v-else class="justify-items-center">
                                      <img
                                          @click="triggerFileInputReading"
                                          src="/images/sample-match-photo-3.png"
                                          alt="image-select"
                                          class="cursor-pointer w-[416px] h-[250px]"
                                      />
                                      <img
                                          class="h-[38px] mt-2 cursor-pointer"
                                          src="/images/icon-select.png"
                                          alt=""
                                          @click="triggerFileInputReading"
                                      />
                                  </div>
                              </div>
                              <div v-if="errorImage" class="text-red-600">
                                  {{ errorImage }}
                              </div>
                              <div class="relative mt-4 flex items-center mb-4">
                                  <div v-if="type_text !== 1" @click="triggerFileInputReading" class="flex gap-4 mt-4">
                                      <span>Tải lên</span>
                                      <img
                                          class="h-[25px] cursor-pointer"
                                          src="/images/icon-upload-file.png"
                                          alt=""
                                      />
                                  </div>
                                  <input
                                      id="file-input"
                                      type="file"
                                      accept="image/*"
                                      class="hidden"
                                      ref="fileInputReading"
                                      @change="uploadFile"
                                  />
                                  <div class="absolute right-0 flex gap-4 mt-4">
                                      <a-button
                                          @click="removeContent"
                                          class="text-white"
                                          size="middle"
                                          type="primary"
                                          danger
                                      >
                                          Xóa
                                      </a-button>
                                      <a-button
                                          class="text-white"
                                          size="middle"
                                          type="primary"
                                          @click="saveReading"
                                      >
                                          Xác nhận
                                      </a-button>
                                  </div>
                              </div>
                          </Form>
                      </div>
                  </div>
              </div>
          </div>
          <template #footer> </template>
      </a-modal>
    <component
      v-if="infoTemplate.preview"
      :is="currentComponent"
      v-model:open-preview="openPreview"
      v-model:data="form"
    >
    </component>
    <a-modal v-else v-model:open="openPreview" :width="1000">
      <template #title>Preview mẫu game</template>
      <div class="flex gap-2">
        <img :src="imagePreview" alt="" />
      </div>
      <template #footer> </template>
    </a-modal>
    <modal-template
      v-model:open-template-modal="openTemplateModal"
      v-model:data="form.dataNew"
    >
    </modal-template>
    <a-modal
      v-model:open="isUploading"
      centered
      :closable="false"
      :footer="null"
      :maskClosable="false"
    >
      <div class="flex flex-col items-center justify-center p-4">
        <a-spin size="large" />
        <p class="mt-4 text-lg font-medium">Đang tải lên...</p>
      </div>
    </a-modal>
  </SchoolLayout>
</template>
<style lang="scss">
.custom-height {
  min-height: 450px;
  padding: 1rem;
}
.custom-grid {
  display: inline-grid;
}
.custom-checkbox .ant-checkbox-inner {
  width: 30px;
  height: 30px; /* Tăng kích thước hình vuông checkbox */
}
.custom-checkbox .ant-checkbox-inner::after {
  width: 10px; /* Độ dài của dấu tích */
  height: 16px; /* Chiều cao của dấu tích */
  border-width: 3px; /* Độ dày nét của dấu tích */
  top: 50%; /* Đảm bảo căn giữa */
  left: 50%;
  transform: translate(-50%, -50%) rotate(45deg); /* Căn chỉnh */
}
.tooltip {
  top: 0;
  right: 0;
  transform: translateX(-50%);
  background-color: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 5px 10px;
  border-radius: 5px;
  font-size: 12px;
  white-space: nowrap;
  z-index: 9999; /* Đảm bảo tooltip không bị che khuất */
  transition: opacity 0.2s ease-in-out;
  opacity: 1;
}
</style>
