<script setup>
import { Head, useForm as useInertiaForm, usePage } from "@inertiajs/vue3";
import SchoolLayout from "@/Layouts/SchoolLayout.vue"";
import { ErrorMessage, Field, Form } from "vee-validate";
import * as yup from "yup";
import InputError from "@/Components/InputError.vue";
import { computed, defineProps, onMounted, reactive, ref, watch } from "vue";
import { Link } from "@inertiajs/vue3";
import PreviewDragDropOne from "@/Pages/SelectSample/Partials/DragDrop/PreviewDragDropOne.vue";
import PreviewDragDropTwo from "@/Pages/SelectSample/Partials/DragDrop/PreviewDragDropTwo.vue";
import PreviewDragDropThree from "@/Pages/SelectSample/Partials/DragDrop/PreviewDragDropThree.vue";
import PreviewDragDropFour from "@/Pages/SelectSample/Partials/DragDrop/PreviewDragDropFour.vue";
import ModalTemplate from "@/Pages/SelectSample/Template/ModalTemplate.vue";
import { useToast } from "vue-toastification";
import Editor from "@tinymce/tinymce-vue";
import PcnlNdgdFields from '@/Components/PcnlNdgdFields.vue';

const props = defineProps({
    template: {
        type: Object
    },
    data: {
        type: Object
    },
    questionEditor: {
        type: Object
    },
});

const breadcrumbs = [
    {'title': 'App', 'url': route('apps.dashboard')},
    {'title': props.questionEditor.practice.week.book.app.name, 'url': route('books.index', {appId: props.questionEditor.practice.week.book.app.id})},
    {'title': props.questionEditor.practice.week.book.title, 'url': route('weeks.index', {
        app_id: props.questionEditor.practice.week.book.app.id,
        book_id: props.questionEditor.practice.week.book.id
    })},
    {'title': props.questionEditor.practice.week.name, 'url': route('lessons.index', {
        app_id: props.questionEditor.practice.week.book.app.id,
        book_id: props.questionEditor.practice.week.book.id,
        week_id: props.questionEditor.practice.week.id,
    })},
    {'title': props.questionEditor.practice.name, 'url': route('questionEditors.index', {
        app_id: props.questionEditor.practice.week.book.app.id,
        book_id: props.questionEditor.practice.week.book.id,
        week_id: props.questionEditor.practice.week.id,
        practice_id: props.questionEditor.practice.id,
    })},
    {'title': props.questionEditor.title, 'url': ''},
]

const toast = useToast();
const page = usePage();
const query = page.props.query;
const app_id = query.app_id;
const book_id = query.book_id;
const week_id = query.week_id;
const practice_id = query.practice_id;
const template_id = ref(props.template.id);
const BASE_URL = ref(window.location.origin);
const listVoice = ref([]);
const imagePreview = ref(props.template.image_url);
const infoTemplate = ref(props.template.template);
const optionsType = ref([
    {
        value: 1,
        label: "Text"
    },
    {
        value: 2,
        label: "Ảnh"
    },
    {
        value: 3,
        label: "Âm thanh"
    }
]);
const optionsInput = ref([
    {
        value: "PreviewDragDropOne",
        totalInput: 2,
        componentMap: PreviewDragDropOne
    },
    {
        value: "PreviewDragDropTwo",
        totalInput: 3,
        componentMap: PreviewDragDropTwo
    },
    {
        value: "PreviewDragDropThree",
        totalInput: 4,
        componentMap: PreviewDragDropThree
    },
    {
        value: "PreviewDragDropFour",
        totalInput: 4,
        componentMap: PreviewDragDropFour
    }
]);
const updatedUrl = ref('');

onMounted(() => {
    getListVoice();
    let currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('template_id', template_id.value);
    updatedUrl.value = currentUrl.toString();
});
const currentComponent = computed(() => {
    const item = optionsInput.value.find(
        (item) => item.value === infoTemplate.value.preview
    );
    return item.componentMap || [];
});
const fileVideoTitle = ref(null);
const fileNameVideoTitle = ref(props.data.question_video_url_db);
const openModalVideoTitle = ref(false);
const inputUrl = ref("");
const isYouTubeVideo = ref(props.data.isYouTubeVideo);
const videoUrl = ref(
    !isYouTubeVideo.value ? props.data.question_video_url : null
);
const youtubeEmbedUrl = ref(
    isYouTubeVideo.value ? props.data.question_video_url : null
);
const tempUrlFileVideoTitle = ref("");
const tempFileNameTitle = ref("");
const createListAnswer = (items, infoTemplate, oldListAnswer = [], forceUseTemplate = false) => {
    let length;
    let sourceData = oldListAnswer || [];

    if (forceUseTemplate) {
        length = infoTemplate.value.number_answer;
    } else {
        const numberListAnswerDb = props.data.listAnswer?.filter(
            item => item.type_answer !== null || item.type !== undefined
        ) || [];

        length = numberListAnswerDb.length || infoTemplate.value.number_answer;
        sourceData = numberListAnswerDb.length > 0 ? numberListAnswerDb : oldListAnswer;
    }

    return Array.from({ length }, (_, index) => ({
        inputAnswer: sourceData[index]?.inputAnswer || sourceData[index]?.value || sourceData[index]?.answer_val || sourceData[index]?.answer_text || "",
        type_answer: sourceData[index]?.type_answer,
        inputNumber: sourceData[index]?.inputNumber || "",
        nameVoice: ""
    }));
};

const createListAnswerConnect = (answerConnects = []) => {
    // For linking questions, answer_connects contains cot_b (right column) items
    if (!answerConnects || !Array.isArray(answerConnects)) {
        return [];
    }
    return answerConnects.map((item, index) => ({
        inputAnswer: item.value || item.answer_val || item.answer_text || "",
        type_answer: item.type_answer || 1,
        inputNumber: item.inputNumber || String(index),
        nameVoice: ""
    }));
};
const form = useInertiaForm({
    id: props.data.id,
    title: props.data.title,
    audio_title_db: props.data.audio_val_db || "",
    audio_title_show: props.data.audio_val || "",
    video_title_db: props.data.question_video_url_db || "",
    video_title_show: props.data.question_video_url || "",
    isYouTubeVideo: props.data.isYouTubeVideo,
    reading_type: 2,
    image_reading_show: props.data.reading_val_show || "",
    image_reading_db: props.data.reading_val || "",
    image_background_show: props.data.background_show || "",
    image_background_db: props.data.background || "",
    file_reading_name: props.data.file_reading_name,
    file_background_name: props.data.file_background_name,
    contentReading:  props.data.reading_doc ?? '',
    image_show: props.data.reading_val_show,
    image_db: props.data.reading_val,
    pcnl: props.data.pcnl || "",
    ndgd: props.data.ndgd || "",
    competency_id: props.data.competency_id ? Number(props.data.competency_id) : null,
    competency_component_id: props.data.competency_component_id ? Number(props.data.competency_component_id) : null,
    pcnl_detail: props.data.pcnl_detail || '',
    educational_content_id: props.data.educational_content_id ? Number(props.data.educational_content_id) : null,
    ndgd_requirement: props.data.ndgd_requirement || '',
    image_question_show: props.data.img_show || "",
    image_question_db: props.data.img || "",
    content: props.data.question_val || "",
    answer: props.data.question_array || "",
    answerCorrect: props.data.right_answer_array || "",
    listAnswer: reactive(
        createListAnswer(template_id.value, infoTemplate, props.data.listAnswer) ||
        []
    ),
    listAnswerConnect: reactive(
        createListAnswerConnect(props.data.answer_connects) || []
    ),
    template_id: template_id.value,
    type: props.template.type,
    video_title_name: props.data.question_video_url || "",
    dataNew: reactive({
        template_current: template_id.value,
        new_type: props.template.type,
        new_template_id: "",
        app_id: app_id
    })
});
const rules = {
    title: yup.string().required("Tiêu đề không được để trống")
};
const submittedData = ref([]);
const submittedConnectData = ref([]);
const handleSubmit = () => {
    // For listAnswer (left column - cot_a), use the correct format
    submittedData.value = form.listAnswer.map((item, idx) => ({
        type: 'text',
        type_answer: 1,
        value: item.inputAnswer,
        answer_val: item.inputAnswer,
        checked: false,
        inputNumber: idx === 0 ? '' : String(idx),
        answer_text: item.inputAnswer
    }));

    // For linking questions, prepare answer_connects data (right column - cot_b)
    if (form.listAnswerConnect && form.listAnswerConnect.length > 0) {
        submittedConnectData.value = form.listAnswerConnect.map((item, idx) => ({
            type: 'text',
            type_answer: 1,
            value: item.inputAnswer,
            answer_val: item.inputAnswer,
            inputNumber: String(idx),
            answer_text: item.inputAnswer
        }));
    }

    const formData = new FormData();
    formData.append("id", form.id);
    formData.append("title", form.title);
    formData.append("audio_title_db", form.audio_title_db);
    formData.append("audio_val", form.audio_title_db);
    formData.append("audio_title_show", form.audio_title_show);
    formData.append("video_title_db", form.video_title_db);
    formData.append("question_video_url", form.video_title_db);
    formData.append("video_title_show", form.video_title_show);
    formData.append("isYouTubeVideo", form.isYouTubeVideo);
    formData.append("question_val", form.content);
    formData.append("question_type", 1);
    formData.append("question_img", form.image_question_db);
    formData.append("pcnl", form.pcnl);
    formData.append("ndgd", form.ndgd);
  formData.append('competency_id', form.competency_id ?? '');
  formData.append('competency_component_id', form.competency_component_id ?? '');
  formData.append('pcnl_detail', form.pcnl_detail);
  formData.append('educational_content_id', form.educational_content_id ?? '');
  formData.append('ndgd_requirement', form.ndgd_requirement);
    formData.append("app_id", app_id);
    formData.append("book_id", book_id);
    formData.append("week_id", week_id);
    formData.append("practice_id", practice_id);
    formData.append("template_id", form.dataNew.new_template_id == '' ? form.template_id : form.dataNew.new_template_id);
    formData.append("reading_val", form.image_reading_db ?? '');
    formData.append("background", form.image_background_db ?? '');
    formData.append('file_reading_name', form.file_reading_name ?? '');
    formData.append('file_background_name', form.file_background_name ?? '');
    formData.append('content', form.contentReading ?? '');
    formData.append('image_show', form.image_show ?? '');
    formData.append('image_db', form.image_db ?? '');
    formData.append("tem_playable_id", form.template_id);
    formData.append("listAnswer", JSON.stringify(submittedData.value));
    if (submittedConnectData.value.length > 0) {
        formData.append("listAnswerConnect", JSON.stringify(submittedConnectData.value));
    }
    formData.append('link', updatedUrl.value);
    axios
        .post(route("questionEditors.json.postEditGame"), formData)
        .then((response) => {
            if (response.data.status) {
                toast.success("Cập nhật câu hỏi thành công");
                setTimeout(function() {
                    location.href = route("questionEditors.index", {
                        practice_id,
                        app_id,
                        book_id,
                        week_id
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
const addInput = () => {
    form.listAnswer.push({
        inputAnswer: "",
        inputNumber: "",
        type_answer: 1
    });
};
const removeInput = (index) => {
    form.listAnswer.splice(index, 1);
};
const getListVoice = async () => {
    const res = await axios.get(route("voice-types.list"));
    listVoice.value = res.data.data;
};

// Title
const fileAudioTitle = ref(null);
const isUploading = ref(false);
const popoverVisible = ref(false);
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
        formData.append("file", file);
        const res = await axios.post("/api/upload-file", formData);
        form.audio_title_show = res.data.s3_file_url;
        form.audio_title_db = res.data.file_url;
    } catch (error) {
        console.error("Lỗi upload:", error);
    } finally {
        isUploading.value = false;
    }
};
const removeAudioTitle = () => {
    form.audio_title_show = "";
    form.audio_title_db = "";
};
const textToSpeech = async (item) => {
    const params = {
        text: form.title,
        voice_type: item.type
    };
    popoverVisible.value = false;
    try {
        isUploading.value = true;
        const res = await axios.post(route("voice-types.textToSpeech"), params);
        form.audio_title_show = res.data.s3_url;
        form.audio_title_db = res.data.url;
    } catch (error) {
        console.error("Lỗi upload:", error);
    } finally {
        isUploading.value = false;
    }
};
const showModalVideoTitle = () => {
    openModalVideoTitle.value = true;
};
const triggerFileVideoInput = () => {
    fileVideoTitle.value.click(); // Kích hoạt sự kiện click trên input
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
    // Kiểm tra nếu video URL nhập vào hợp lệ
    const url = inputUrl.value.trim();

    if (isValidYouTubeUrl(url)) {
        isYouTubeVideo.value = true;
        // Lấy ID video YouTube từ URL
        const videoId = getYouTubeVideoId(url);
        if (videoId) {
            youtubeEmbedUrl.value = `https://www.youtube.com/embed/${videoId}`;
            videoUrl.value = null; // Nếu là YouTube, không cần src video file
            tempFileNameTitle.value = `YouTube Video: ${videoId}`; // Tạm dùng videoId làm tên
        } else {
            videoUrl.value = null;
        }
    } else if (isValidVideoUrl(url)) {
        isYouTubeVideo.value = false;
        videoUrl.value = url; // Đặt trực tiếp URL video nếu không phải YouTube
        youtubeEmbedUrl.value = ""; // Xóa URL nhúng YouTube nếu không phải video YouTube
    } else {
        isYouTubeVideo.value = false;
        videoUrl.value = null;
        youtubeEmbedUrl.value = "";
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
    formData.append("file", file);
    if (file && file.type.startsWith("video/")) {
        try {
            const formData = new FormData();
            formData.append("file", file);
            isUploading.value = true;
            const res = await axios.post("/api/upload-file", formData);
            videoUrl.value = res.data.s3_file_url;
            tempUrlFileVideoTitle.value = res.data.file_url;
            tempFileNameTitle.value = file.name;
            isYouTubeVideo.value = false;
            youtubeEmbedUrl.value = "";
        } catch (error) {
            console.error("Lỗi upload:", error);
        } finally {
            isUploading.value = false;
        }
    } else {
        console.log("Error file");
    }
};
const fileInput = ref(null);
const triggerFileInput = () => {
    if (fileInput.value) {
        fileInput.value.value = "";
        fileInput.value.click(); // Kích hoạt sự kiện click trên input
    }
};
const handleFileChange = async (event) => {
    const file = event.target.files[0];

    if (!file) return;
    const isImage = file.type.startsWith("image/");

    if (!isImage) {
        return;
    }
    try {
        isUploading.value = true;
        const formData = new FormData();
        formData.append("file", file);
        const res = await axios.post("/api/upload-file", formData);
        form.image_question_show = res.data.s3_file_url;
        form.image_question_db = res.data.file_url;
    } catch (error) {
        console.error("Lỗi upload:", error);
    } finally {
        isUploading.value = false;
    }
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
        form.contentReading = "";
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
    doctype: '<!DOCTYPE html>',

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
        fileInputBackground.value.value = "";
        fileInputBackground.value.click();
    }
};
const handleFileChangeBackground = async (event) => {
    const file = event.target.files[0];

    if (!file) return;
    const isImage = file.type.startsWith("image/");

    if (!isImage) {
        return;
    }
    try {
        isUploading.value = true;
        const formData = new FormData();
        formData.append("file", file);
        const res = await axios.post("/api/upload-file", formData);
        form.image_background_show = file.name;
        form.file_background_name = file.name;
        form.image_background_db = res.data.file_url;
    } catch (error) {
        console.error("Lỗi upload:", error);
    } finally {
        isUploading.value = false;
    }
};
const removeImageBackground = () => {
    form.image_background_show = "";
    form.image_background_db = "";
    form.file_background_name = "";
};

const openPreview = ref(false);
const showPreview = () => {
    openPreview.value = true;
};
const filterOption = (input, option) => {
    return option.value.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};
const openTemplateModal = ref(false);
const showTemplate = () => {
    openTemplateModal.value = true;
};
// Theo dõi sự thay đổi
watch(
    () => form.dataNew.new_template_id,
    async (newValue, oldValue) => {
        if (!newValue || newValue === oldValue) return;
        template_id.value = newValue;
        let currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('template_id', newValue);
        updatedUrl.value = currentUrl.toString();
        const res = await axios.get(
            route("templates.json.detail", { question_template_id: newValue })
        );
        infoTemplate.value = res.data.data.template;
        imagePreview.value = res.data.data.image_url;
        let newListAnswer = createListAnswer(
            newValue,
            infoTemplate,
            form.listAnswer,
            true
        );
        form.listAnswer.splice(0, form.listAnswer.length, ...newListAnswer);
    }
);
const goBack = () => {
    window.location = route("questionEditors.index", {
        app_id,
        book_id,
        week_id,
        practice_id
    });
};
const note = ref(" (Copy cụm mã       <#>1</#>       để tạo các chỗ trống như mẫu. Thêm dấu cách trước và sau <#>1</#> để có khoảng trống phù hợp)");
const getQuestionMaxLength = () => {
  return infoTemplate.value?.max_length_question ? parseInt(infoTemplate.value.max_length_question) : 255;
};
const getAnswerMaxLength = () => {
  return infoTemplate.value?.max_length_answer ? parseInt(infoTemplate.value.max_length_answer) : 150;
};
</script>

<template>
    <Head title="Edit Question Drag Drop" />

    <SchoolLayout :breadcrumbs=breadcrumbs>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit Question Drag Drop
            </h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-11/12 sm:px-6 lg:px-8">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Chỉnh sửa câu hỏi</h1>
                <Form @submit="handleSubmit" v-slot="{ errors }" class="w-full">
                    <div class="flex w-full">
                        <div class="content-page w-full sm:px-6 lg:px-8">
                            <div
                                class="custom-height relative mt-6 w-full rounded-2xl border border-solid border-blue-400 bg-white pt-2 max-md:max-w-full"
                            >
                                <div class="mt-2 w-full">
                                    <label
                                        for="appNameInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                        Nội dung câu hỏi
                                    </label>
                                    <div class="mt-2 flex w-full gap-4">
                                        <div class="flex w-11/12 gap-4">
                                            <Field
                                                name="title"
                                                :rules="rules.title"
                                                v-model="form.title"
                                            >
                                                <a-input
                                                    placeholder="Điền câu hỏi vào đây"
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
                                                <a-popover v-model:open="popoverVisible" v-else trigger="click">
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
                              <span @click="textToSpeech(item)"
                              >{{ item.name }}</span
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
                                                <a-modal
                                                    v-model:open="openModalVideoTitle"
                                                    :width="1000"
                                                >
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
                                                                    @click="triggerFileVideoInput"
                                                                />
                                                                <span v-if="!videoUrl && !youtubeEmbedUrl">
                                  Không có tệp nào được chọn
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
                                </div>
                                <div class="mt-2 flex w-11/12 gap-4">
                                    <img
                                        v-if="form.image_question_show"
                                        :src="form.image_question_show"
                                        @click="triggerFileInput"
                                        alt="Preview"
                                        class="h-[212px] w-[376px] cursor-pointer object-contain"
                                    />
                                    <img
                                        v-else
                                        class="cursor-pointer"
                                        src="/images/icon-select-image.png"
                                        alt=""
                                        @click="triggerFileInput"
                                    />
                                    <input
                                        id="file-input"
                                        type="file"
                                        accept="image/*"
                                        class="hidden"
                                        ref="fileInput"
                                        @change="handleFileChange"
                                    />
                                    <div class="w-full">
                                        <div class="mt-2 font-semibold italic text-[#2C75E3] text-[14px]">{{ note }}
                                        </div>
                                        <div>
                                            <Field
                                                name="content"
                                                v-model="form.content"
                                            >
                                                <a-textarea
                                                    placeholder="Bạn nhập nội dung "
                                                    :allow-clear="true"
                                                    v-model:value="form.content"
                                                    :rows="8"
                                                    class="w-full"
                                                >
                                                </a-textarea>
                                                <div class="flex justify-between items-center text-xs mt-[2px]">
                                                  <span v-if="form.content.length > getQuestionMaxLength()" class="text-red-500 font-medium">Vượt ký tự, chọn Âm thanh</span>
                                                  <span v-else></span>
                                                  <span :class="form.content.length > getQuestionMaxLength() ? 'text-red-500 font-medium' : 'text-gray-400'">{{ form.content.length }}/{{ getQuestionMaxLength() }}</span>
                                                </div>
                                            </Field>
                                            <!--                      <ErrorMessage-->
                                            <!--                        class="text-sm text-red-600"-->
                                            <!--                        name="content"-->
                                            <!--                      />-->
                                            <!--                      <InputError class="mt-2" :message="form.errors.content" />-->
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 w-11/12 gap-4">
                                    <div class="w-full font-bold">
                                        Các đáp án <span class="font-semibold text-[#2C75E3] italic">(Hãy đảo thứ tự đáp án và điền số 1 vào ô trống cho đáp án số 1, số 2 cho đáp án số 2... Đáp án nhiễu (sai) để trống)</span>
                                    </div>
                                    <div
                                        class="mr-4 grid w-full grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4"
                                    >
                                        <div
                                            :class="`flex w-2/3 flex-col`"
                                            v-for="(item, index) in form.listAnswer"
                                            :key="index"
                                        >
                                            <!-- Textarea -->
                                            <a-textarea
                                                placeholder="Bạn nhập nội dung"
                                                :allow-clear="true"
                                                v-model:value="item.inputAnswer"
                                                :rows="2"
                                                class="w-full mb-[0.4rem] mt-[0.125rem]"
                                            >
                                            </a-textarea>
                                            <div v-if="item.type_answer !== 3" class="flex justify-between items-center text-xs mt-[2px]">
                                              <span v-if="item.inputAnswer.length > getAnswerMaxLength()" class="text-red-500 font-medium">Vượt ký tự, chọn Âm thanh</span>
                                              <span v-else></span>
                                              <span :class="item.inputAnswer.length > getAnswerMaxLength() ? 'text-red-500 font-medium' : 'text-gray-400'">{{ item.inputAnswer.length }}/{{ getAnswerMaxLength() }}</span>
                                            </div>
                                            <div
                                                :class="`mt-2 flex items-center justify-center gap-2`"
                                            >
                                                <div class="flex gap-4 items-center">
                                                    <input
                                                        v-model="item.inputNumber"
                                                        class="h-[40px] w-[40px] rounded-md border-2 border-solid border-[#E5E5E5]"
                                                    />
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
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Linking question right column (cot_b) -->
                                <div v-if="form.listAnswerConnect && form.listAnswerConnect.length > 0" class="mt-6 w-11/12">
                                    <div class="w-full font-bold">
                                        Cột B - Đáp án / Kết quả
                                    </div>
                                    <div class="mr-4 grid w-full grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4 mt-4">
                                        <div
                                            :class="`flex w-2/3 flex-col`"
                                            v-for="(item, index) in form.listAnswerConnect"
                                            :key="`connect-${index}`"
                                        >
                                            <a-textarea
                                                placeholder="Đáp án / Kết quả"
                                                :allow-clear="true"
                                                v-model:value="item.inputAnswer"
                                                :rows="2"
                                                class="w-full mb-[0.4rem] mt-[0.125rem]"
                                            >
                                            </a-textarea>
                                            <div class="flex justify-between items-center text-xs mt-[2px]">
                                              <span v-if="item.inputAnswer.length > getAnswerMaxLength()" class="text-red-500 font-medium">Vượt ký tự</span>
                                              <span v-else></span>
                                              <span :class="item.inputAnswer.length > getAnswerMaxLength() ? 'text-red-500 font-medium' : 'text-gray-400'">{{ item.inputAnswer.length }}/{{ getAnswerMaxLength() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="infoTemplate.add_answer === 1" class="mt-4 flex w-11/12 items-center gap-4">
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
                                            <img v-if="!form.contentReading && !form.image_show" @click="addReading" src="/images/reading/add-read.png" alt="" class="cursor-pointer">
                                            <img v-else @click="addReading" src="/images/reading/edit-reading.png" alt="" class="cursor-pointer">
                                        </div>
                                    </div>
                                    <div class="flex w-1/4 items-center gap-4">
                                        <div class="flex w-full gap-2">
                                            <div>
                                                <img v-if="!form.image_background_show" @click="triggerFileInputBackground" src="/images/reading/add-background.png" alt="" class="cursor-pointer">
                                                <img v-else @click="triggerFileInputBackground" src="/images/reading/edit-background.png" alt="" class="cursor-pointer">
                                            </div>
                                            <div
                                                v-if="form.image_background_show"
                                                class="flex items-center gap-2"
                                            >
                                                <div>{{ form.file_background_name }}</div>
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
              <PcnlNdgdFields
                v-model:competencyId="form.competency_id"
                v-model:competencyComponentId="form.competency_component_id"
                v-model:pcnlDetail="form.pcnl_detail"
                v-model:educationalContentId="form.educational_content_id"
                v-model:ndgdRequirement="form.ndgd_requirement"
              />
                        </div>
                        <!--                        <div class="relative mt-6 w-1/4 sm:px-6 lg:px-8">-->
                        <!--                            <img-->
                        <!--                                class="h-[148px] w-[263px]"-->
                        <!--                                :src="-->
                        <!--                                    BASE_URL +-->
                        <!--                                    '/images/games/' +-->
                        <!--                                    props.template.image-->
                        <!--                                "-->
                        <!--                                alt=""-->
                        <!--                            />-->
                        <!--                        </div>-->
                    </div>
                    <div class="mt-4 flex gap-4">
                        <!--                        <a-button class="custom-bg text-black" size="large">-->
                        <!--                            Hướng dẫn-->
                        <!--                        </a-button>-->
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
                                    v-model="form.contentReading"
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
            <template #footer></template>
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
    min-height: 439px;
    padding-left: 2.5rem;
}

.custom-grid {
    display: inline-grid;
}
</style>
