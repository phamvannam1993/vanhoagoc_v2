<script setup>
import { Head, useForm, usePage } from "@inertiajs/vue3";
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { ErrorMessage, Field, Form } from "vee-validate";
import * as yup from "yup";
import InputError from "@/Components/InputError.vue";
import { computed, defineProps, onMounted, reactive, ref, watch } from "vue";
import { Link } from "@inertiajs/vue3";
import PreviewConnectSentenceOne from "@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceOne.vue";
import PreviewConnectSentenceTwo from "@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceTwo.vue";
import PreviewConnectSentenceThree from "@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceThree.vue";
import PreviewConnectSentenceFour from "@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceFour.vue";
import PreviewConnectSentenceFive from "@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceFive.vue";
import PreviewConnectSentenceSix from "@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceSix.vue";
import PreviewConnectSentenceSeven from "@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceSeven.vue";
import PreviewConnectSentenceEight from "@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceEight.vue";
import PreviewConnectSentenceNine from "@/Pages/SelectSample/Partials/ConnectSentence/PreviewConnectSentenceNine.vue";
import ModalTemplate from "@/Pages/SelectSample/Template/ModalTemplate.vue";
import { useToast } from "vue-toastification";
import Editor from "@tinymce/tinymce-vue";
import PcnlNdgdFields from '@/Components/PcnlNdgdFields.vue';

const props = defineProps({
    template: {
        type: Object
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

const toast = useToast();
const page = usePage();
const query = page.props.query;
const app_id = query.app_id;
const book_id = query.book_id;
const week_id = query.week_id;
const practice_id = query.practice_id;
const submittedDataQuestion = ref([]);
const submittedDataAnswer = ref([]);
const BASE_URL = ref(window.location.origin);
const listVoice = ref([]);
const template_id = ref(props.template.id);
const infoTemplate = ref(props.template.template);
const imagePreview = ref(props.template.image_url);
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
const filteredOptionsQuestion = computed(() =>
    infoTemplate.value.type_question !== null
        ? optionsType.value.filter((option) =>
            infoTemplate.value.type_question.includes(option.value)
        )
        : optionsType
);
const filteredOptionsAnswer = computed(() =>
    infoTemplate.value.type_answer !== null
        ? optionsType.value.filter((option) =>
            infoTemplate.value.type_answer.includes(option.value)
        )
        : optionsType
);
const optionsInput = ref([
    {
        value: "PreviewConnectSentenceOne",
        totalInputQuestion: 3,
        totalInputAnswer: 3,
        componentMap: PreviewConnectSentenceOne
    },
    {
        value: "PreviewConnectSentenceTwo",
        totalInputQuestion: 3,
        totalInputAnswer: 3,
        componentMap: PreviewConnectSentenceTwo
    },
    {
        value: "PreviewConnectSentenceThree",
        totalInputQuestion: 3,
        totalInputAnswer: 3,
        componentMap: PreviewConnectSentenceThree
    },
    {
        value: "PreviewConnectSentenceFour",
        totalInputQuestion: 4,
        totalInputAnswer: 4,
        componentMap: PreviewConnectSentenceFour
    },
    {
        value: "PreviewConnectSentenceFive",
        totalInputQuestion: 4,
        totalInputAnswer: 4,
        componentMap: PreviewConnectSentenceFive
    },
    {
        value: "PreviewConnectSentenceSix",
        totalInputQuestion: 4,
        totalInputAnswer: 4,
        componentMap: PreviewConnectSentenceSix
    },
    {
        value: "PreviewConnectSentenceSeven",
        totalInputQuestion: 2,
        totalInputAnswer: 3,
        componentMap: PreviewConnectSentenceSeven
    },
    {
        value: "PreviewConnectSentenceEight",
        totalInputQuestion: 2,
        totalInputAnswer: 4,
        componentMap: PreviewConnectSentenceEight
    },
    {
        value: "PreviewConnectSentenceNine",
        totalInputQuestion: 2,
        totalInputAnswer: 4,
        componentMap: PreviewConnectSentenceNine
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
const createListAnswer = (items, infoTemplate, oldListAnswer = []) => {
    let length = infoTemplate.value.number_answer;
    return Array.from({ length }, (_, index) => {
        const answerType =
            infoTemplate.value?.type_question !== null
                ? infoTemplate.value?.type_question[0]
                : 1;
        return {
            inputNumber: '' + index,
            inputAnswer:
                answerType == 3
                    ? oldListAnswer[index]?.answer_text || ""
                    : oldListAnswer[index]?.inputAnswer || "",
            type_answer: answerType,
            checked: oldListAnswer[index]?.checked || false,
            image_answer_show: oldListAnswer[index]?.image_answer_show || "",
            image_answer_db: oldListAnswer[index]?.image_answer_db || "",
            audio_answer_show: oldListAnswer[index]?.audio_answer_show || "",
            audio_answer_db: oldListAnswer[index]?.audio_answer_db || "",
            showTitleAnswer: false,
            nameVoiceAnswer: ""
        };
    });
};
const createListAnswerConnect = (items, infoTemplate, oldListAnswer = []) => {
    let length = infoTemplate.value.number_answer_connect;
    return Array.from({ length }, (_, index) => {
        const answerType =
            infoTemplate.value?.type_answer !== null
                ? infoTemplate.value?.type_answer[0]
                : 1;
        return {
            inputNumber: '' + index,
            inputAnswer:
                answerType == 3
                    ? oldListAnswer[index]?.answer_text || ""
                    : oldListAnswer[index]?.inputAnswer || "",
            type_answer: answerType,
            checked: oldListAnswer[index]?.checked || false,
            image_answer_show: oldListAnswer[index]?.image_answer_show || "",
            image_answer_db: oldListAnswer[index]?.image_answer_db || "",
            audio_answer_show: oldListAnswer[index]?.audio_answer_show || "",
            audio_answer_db: oldListAnswer[index]?.audio_answer_db || "",
            showTitleAnswerConnect: false,
            nameVoiceAnswerConnect: ""
        };
    });
};
const form = useForm({
    title: "Điền câu hỏi vào đây",
    listAnswer: reactive(
        createListAnswer(template_id.value, infoTemplate, []) || []
    ),
    listAnswerConnect: reactive(
        createListAnswerConnect(template_id.value, infoTemplate, []) || []
    ),
    audio_title_db: "",
    audio_title_show: "",
    video_title_db: "",
    video_title_show: "",
    isYouTubeVideo: false,
    reading_type: 2,
    image_reading_show: "",
    image_reading_db: "",
    image_background_show: "",
    image_background_db: "",
    file_reading_name: "",
    file_background_name: "",
    content: "",
    image_show: "",
    image_db: "",
    pcnl: "",
    ndgd: "",
    template_id: template_id.value,
    type: props.template.type,
    video_title_name: "",
    dataNew: reactive({
        template_current: template_id.value,
        new_type: props.template.type,
        new_template_id: "",
        app_id: app_id
    })
});
const rules = {
    title: yup.string().required("Tiêu đề không được để trống"),
    listAnswer: yup
        .array()
        .of(
            yup.object({
                inputAnswer: yup
                    .string()
                    .trim()
                    .required("Không được để trống câu hỏi")
            })
        )
        .min(1, "Cần ít nhất một câu hỏi"),
    listAnswerConnect: yup
        .array()
        .of(
            yup.object({
                inputAnswer: yup
                    .string()
                    .trim()
                    .required("Không được để trống câu trả lời")
            })
        )
        .min(1, "Cần ít nhất một đáp án")
};
const getQuestionVal = (item) => {
    let result = "";

    if (item.type_answer === 1) {
        result = item.inputAnswer;
    } else if (item.type_answer === 2) {
        result = item.image_answer_db;
    } else {
        result = item.audio_answer_db;
    }

    return result;
};
const getAnswerVal = (item) => {
    let result = "";

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
    const maxQuestion = infoTemplate.value.max_length_question ? parseInt(infoTemplate.value.max_length_question) : null;
    const maxAnswer = infoTemplate.value.max_length_answer ? parseInt(infoTemplate.value.max_length_answer) : null;
    if (maxQuestion && form.content && form.content.length > maxQuestion) {
        toast.warning(`Câu hỏi vượt quá ${maxQuestion} ký tự cho phép (hiện tại: ${form.content.length} ký tự)`);
        return;
    }
    for (let i = 0; i < form.listAnswer.length; i++) {
        const ans = form.listAnswer[i].inputAnswer ?? '';
        if (maxAnswer && ans.length > maxAnswer) {
            toast.warning(`Đáp án ${i + 1} vượt quá ${maxAnswer} ký tự cho phép (hiện tại: ${ans.length} ký tự)`);
            return;
        }
    }

    submittedDataQuestion.value = form.listAnswer.map((item) => ({
        answer_val: getQuestionVal(item),
        answer_text: item.inputAnswer ?? "",
        inputNumber: item.inputNumber,
        type_answer: item.type_answer
    }));
    submittedDataAnswer.value = form.listAnswerConnect.map((item) => ({
        answer_val: getAnswerVal(item),
        answer_text: item.inputAnswer ?? "",
        inputNumber: item.inputNumber,
        type_answer: item.type_answer
    }));
    const formData = new FormData();
    formData.append("title", form.title);
    formData.append("listAnswer", JSON.stringify(submittedDataQuestion.value));
    formData.append(
        "listAnswerConnect",
        JSON.stringify(submittedDataAnswer.value)
    );
    formData.append("audio_title_db", form.audio_title_db);
    formData.append("audio_val", form.audio_title_db);
    formData.append("audio_title_show", form.audio_title_show);
    formData.append("video_title_db", form.video_title_db);
    formData.append("question_video_url", form.video_title_db);
    formData.append("video_title_show", form.video_title_show);
    formData.append("isYouTubeVideo", form.isYouTubeVideo);
    formData.append("app_id", app_id);
    formData.append("book_id", book_id);
    formData.append("week_id", week_id);
    formData.append("practice_id", practice_id);
    formData.append("template_id", template_id.value);
    formData.append("reading_val", form.image_reading_db ?? '');
    formData.append("background", form.image_background_db ?? '');
    formData.append("file_reading_name", form.file_reading_name ?? '');
    formData.append("file_background_name", form.file_background_name ?? '');
    formData.append('content', form.content ?? '');
    formData.append('image_show', form.image_show ?? '');
    formData.append('image_db', form.image_db ?? '');
    formData.append("pcnl", form.pcnl);
    formData.append("ndgd", form.ndgd);
  formData.append('competency_id', form.competency_id ?? '');
  formData.append('competency_component_id', form.competency_component_id ?? '');
  formData.append('pcnl_detail', form.pcnl_detail);
  formData.append('educational_content_id', form.educational_content_id ?? '');
  formData.append('ndgd_requirement', form.ndgd_requirement);
    formData.append("tem_playable_id", template_id.value);
    formData.append('link', updatedUrl.value);
    axios
        .post(route("questionEditors.json.postCreateGame"), formData)
        .then((response) => {
            if (response.data.status) {
                toast.success("Thêm mới câu hỏi thành công");
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
const fileVideoTitle = ref(null);
const fileNameVideoTitle = ref("");
const openModalVideoTitle = ref(false);
const inputUrl = ref("");
const videoUrl = ref(null);
const isYouTubeVideo = ref(false); // Biến kiểm tra nếu video là từ YouTube
const youtubeEmbedUrl = ref(""); // Biến chứa URL nhúng của YouTube
const tempUrlFileVideoTitle = ref("");
const tempFileNameTitle = ref("");
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
    const file = event.target.files[0];
    const formData = new FormData();
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

// Question
const handleChangeTypeQuestion = (index) => {
    const item = form.listAnswer.find((item, indexList) => indexList === index);
    item.audio_answer_show = "";
    item.audio_answer_db = "";
    item.image_answer_show = "";
    item.image_answer_db = "";
};
const fileInputQuestion = ref([]);
const fileAudioQuestion = ref([]);
const openPopoverIndexAnswer = ref(null);
const handlePopoverChange = (visible, index) => {
    if (visible) {
        openPopoverIndexAnswer.value = index;
    } else {
        openPopoverIndexAnswer.value = null;
    }
};
const triggerFileInputQuestion = (index) => {
    const inputElement = fileInputQuestion.value[index];
    if (inputElement) {
        inputElement.value = "";
        inputElement.click(); // Kích hoạt click
    } else {
        console.error("Không tìm thấy phần tử input tại index:", index);
    }
};
const handleFileChangeQuestion = async (index) => {
    const inputElement = fileInputQuestion.value[index];
    if (inputElement && inputElement.files.length > 0) {
        const file = inputElement.files[0];
        const item = form.listAnswer.find((item, indexList) => indexList === index);
        try {
            const formData = new FormData();
            formData.append("file", file);
            isUploading.value = true;
            const res = await axios.post("/api/upload-file", formData);
            item.image_answer_show = res.data.s3_file_url;
            item.image_answer_db = res.data.file_url;
        } catch (error) {
            console.error("Lỗi upload:", error);
        } finally {
            isUploading.value = false;
        }
    } else {
        console.error("Không có file nào được chọn tại index:", index);
    }
};
const triggerAudioQuestion = (index) => {
    const inputElement = fileAudioQuestion.value[index];
    if (inputElement) {
        inputElement.value = "";
        inputElement.click();
        openPopoverIndexAnswer.value = null;
    } else {
        console.error("Không tìm thấy phần tử input tại index:", index);
    }
};
const handleUploadAudioQuestion = async (index) => {
    const inputElement = fileAudioQuestion.value[index];
    if (inputElement && inputElement.files.length > 0) {
        const file = inputElement.files[0];
        const item = form.listAnswer.find((item, indexList) => indexList === index);
        item.nameVoiceAnswer = file.name;
        try {
            isUploading.value = true;
            const formData = new FormData();
            formData.append("file", file);
            const res = await axios.post("/api/upload-file", formData);
            item.audio_answer_show = res.data.s3_file_url;
            item.audio_answer_db = res.data.file_url;
        } catch (error) {
            console.error("Lỗi upload:", error);
        } finally {
            isUploading.value = false;
        }
    } else {
        console.error("Không có file nào được chọn tại index:", index);
    }
};
const textToSpeechQuestion = async (item, index) => {
    const question = form.listAnswer.find(
        (item, indexList) => indexList === index
    );
    const params = {
        text: question.inputAnswer,
        voice_type: item.type
    };
    question.nameVoiceAnswer = item.name;
    try {
        openPopoverIndexAnswer.value = null;
        isUploading.value = true;
        const res = await axios.post(route("voice-types.textToSpeech"), params);
        question.audio_answer_show = res.data.s3_url;
        question.audio_answer_db = res.data.url;
    } catch (error) {
        console.error("Lỗi upload:", error);
    } finally {
        isUploading.value = false;
    }
};
const removeAudioQuestion = (index) => {
    const item = form.listAnswer.find((item, indexList) => indexList === index);
    item.audio_answer_show = "";
    item.audio_answer_db = "";
};
const removeImageQuestion = (index) => {
    const item = form.listAnswer.find((item, indexList) => indexList === index);
    item.image_answer_show = "";
    item.image_answer_db = "";
};

// Answer
const handleChangeTypeAnswer = (index) => {
    const item = form.listAnswerConnect.find(
        (item, indexList) => indexList === index
    );
    item.audio_answer_show = "";
    item.audio_answer_db = "";
    item.image_answer_show = "";
    item.image_answer_db = "";
};
const fileInputAnswer = ref([]);
const fileAudioAnswer = ref([]);
const openPopoverIndexAnswerConnect = ref(null);
const triggerFileInputAnswer = (index) => {
    const inputElement = fileInputAnswer.value[index];
    if (inputElement) {
        inputElement.value = "";
        inputElement.click(); // Kích hoạt click
    } else {
        console.error("Không tìm thấy phần tử input tại index:", index);
    }
};
const handlePopoverChangeConnect = (visible, index) => {
    if (visible) {
        openPopoverIndexAnswerConnect.value = index;
    } else {
        openPopoverIndexAnswerConnect.value = null;
    }
};
const handleFileChangeAnswer = async (index) => {
    const inputElement = fileInputAnswer.value[index];
    if (inputElement && inputElement.files.length > 0) {
        const file = inputElement.files[0];
        const item = form.listAnswerConnect.find(
            (item, indexList) => indexList === index
        );
        try {
            isUploading.value = true;
            const formData = new FormData();
            formData.append("file", file);
            const res = await axios.post("/api/upload-file", formData);
            item.image_answer_show = res.data.s3_file_url;
            item.image_answer_db = res.data.file_url;
        } catch (error) {
            console.error("Lỗi upload:", error);
        } finally {
            isUploading.value = false;
        }
    } else {
        console.error("Không có file nào được chọn tại index:", index);
    }
};
const triggerAudioAnswer = (index) => {
    const inputElement = fileAudioAnswer.value[index];
    if (inputElement) {
        inputElement.value = "";
        inputElement.click();
        openPopoverIndexAnswerConnect.value = null;
    } else {
        console.error("Không tìm thấy phần tử input tại index:", index);
    }
};
const handleUploadAudioAnswer = async (index) => {
    const inputElement = fileAudioAnswer.value[index];
    if (inputElement && inputElement.files.length > 0) {
        const file = inputElement.files[0];
        const item = form.listAnswerConnect.find(
            (item, indexList) => indexList === index
        );
        item.nameVoiceAnswerConnect = file.name;
        try {
            isUploading.value = true;
            const formData = new FormData();
            formData.append("file", file);
            const res = await axios.post("/api/upload-file", formData);
            item.audio_answer_show = res.data.s3_file_url;
            item.audio_answer_db = res.data.file_url;
        } catch (error) {
            console.error("Lỗi upload:", error);
        } finally {
            isUploading.value = false;
        }
    } else {
        console.error("Không có file nào được chọn tại index:", index);
    }
};
const textToSpeechAnswer = async (item, index) => {
    const answer = form.listAnswerConnect.find(
        (item, indexList) => indexList === index
    );
    answer.nameVoiceAnswerConnect = item.name;
    const params = {
        text: answer.inputAnswer,
        voice_type: item.type
    };
    try {
        openPopoverIndexAnswerConnect.value = null;
        isUploading.value = true;
        const res = await axios.post(route("voice-types.textToSpeech"), params);
        answer.audio_answer_show = res.data.s3_url;
        answer.audio_answer_db = res.data.url;
    } catch (error) {
        console.error("Lỗi upload:", error);
    } finally {
        isUploading.value = false;
    }
};
const removeAudioAnswer = (index) => {
    const item = form.listAnswerConnect.find(
        (item, indexList) => indexList === index
    );
    item.audio_answer_show = "";
    item.audio_answer_db = "";
};
const removeImageAnswer = (index) => {
    const item = form.listAnswerConnect.find(
        (item, indexList) => indexList === index
    );
    item.image_answer_show = "";
    item.image_answer_db = "";
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
            form.listAnswer
        );
        let newListAnswerConnect = createListAnswerConnect(
            newValue,
            infoTemplate,
            form.listAnswerConnect
        );

        form.listAnswer.splice(0, form.listAnswer.length, ...newListAnswer);
        form.listAnswerConnect.splice(
            0,
            form.listAnswerConnect.length,
            ...newListAnswerConnect
        );
    }
);
const getQuestionMaxLength = () => {
  return infoTemplate.value?.max_length_question ? parseInt(infoTemplate.value.max_length_question) : 255;
};
const getAnswerMaxLength = () => {
  return infoTemplate.value?.max_length_answer ? parseInt(infoTemplate.value.max_length_answer) : 150;
};
const goBack = () => {
    window.location = route("questionEditors.createExercise", {
        app_id,
        book_id,
        week_id,
        practice_id
    });
};
</script>

<template>
    <Head title="Create Question Connect" />

    <SchoolLayout :breadcrumbs=breadcrumbs>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Create Question Connect
            </h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-11/12 sm:px-6 lg:px-8">
                <h1 class="custom-ml text-[30px] font-bold text-[#2C75E3]">
                    Tạo câu hỏi
                </h1>
                <Form @submit="handleSubmit" v-slot="{ errors }" class="w-full">
                    <div class="flex w-full">
                        <div class="content-page w-full sm:px-6 lg:px-8">
                            <div
                                class="custom-height relative mt-6 w-full rounded-2xl border border-solid border-blue-400 bg-white pt-2 max-md:max-w-full"
                            >
                                <div class="mt-2 w-full gap-4">
                                    <label
                                        for="appNameInput"
                                        class="block text-base font-semibold text-black"
                                    >
                                        Tiêu đề
                                    </label>
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
                            <span @click="textToSpeech(item)">
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
                                <div class="mt-2 w-11/12 gap-4">
                                    <div class="custom-gap mt-4 flex">
                                        <div class="mb-4 w-1/2 gap-4">
                                            <div class="w-full">
                                                <div class="text-sm font-bold">Các câu hỏi:</div>
                                                <div
                                                    v-for="(item, index) in form.listAnswer"
                                                    :key="index"
                                                    class="mt-2 flex-col items-center gap-4"
                                                >
                                                    <div class="w-full">
                                                        <a-select
                                                            class="input-search w-1/3"
                                                            v-model:value="item.type_answer"
                                                            show-search
                                                            placeholder="Text"
                                                            size="large"
                                                            :options="filteredOptionsQuestion"
                                                            :filter-option="filterOption"
                                                            @change="handleChangeTypeQuestion(index)"
                                                        ></a-select>
                                                    </div>
                                                    <div class="mt-1 flex w-3/4 items-center gap-4">
                                                        <input
                                                            v-model="item.inputNumber"
                                                            class="h-[40px] w-[50px] rounded-md border-2 border-solid border-[#E5E5E5]"
                                                        />
                                                        <a-textarea
                                                            v-if="
                                                                    item.type_answer === 1 || item.type_answer === 3
                                                                  "
                                                            :placeholder="`Câu hỏi ${index + 1}`"
                                                            :allow-clear="true"
                                                            v-model:value="item.inputAnswer"
                                                            :rows="3"
                                                            :class="`mt-[0.125rem] ${item.type_answer === 1 ? 'mb-[0.4rem]' : ''}`"
                                                            class="w-full"
                                                        >
                                                        </a-textarea>
                                                        <div class="flex justify-between items-center text-xs mt-[2px]">
                                                          <span v-if="item.type_answer !== 3 && item.inputAnswer.length > getQuestionMaxLength()" class="text-red-500 font-medium">Vượt ký tự, chọn Âm thanh</span>
                                                          <span v-else></span>
                                                          <span :class="item.type_answer !== 3 && item.inputAnswer.length > getQuestionMaxLength() ? 'text-red-500 font-medium' : 'text-gray-400'">{{ item.inputAnswer.length }}/{{ getAnswerMaxLength() }}</span>
                                                        </div>
                                                        <div v-if="item.type_answer === 2" class="flex w-full items-center gap-2">
                                                            <img
                                                                v-if="item.image_answer_show"
                                                                :src="item.image_answer_show"
                                                                @click="triggerFileInputQuestion(index)"
                                                                alt="Preview"
                                                                class="mt-1 h-[145px] w-full cursor-pointer object-contain"
                                                            />
                                                            <img
                                                                v-else
                                                                class="mt-1 h-[145px] w-full cursor-pointer"
                                                                src="/images/icon-select-image.png"
                                                                alt=""
                                                                @click="triggerFileInputQuestion(index)"
                                                            />
                                                            <img
                                                                v-if="item.image_answer_show"
                                                                @click="removeImageQuestion(index)"
                                                                class="h-[30px] w-[30px] cursor-pointer"
                                                                src="/images/icon-game-choose-correct/icon-delete.png"
                                                                alt=""
                                                            />
                                                        </div>
                                                        <input
                                                            type="file"
                                                            accept="image/*"
                                                            class="hidden"
                                                            ref="fileInputQuestion"
                                                            @change="handleFileChangeQuestion(index)"
                                                        />
                                                        <div v-if="item.type_answer === 3">
                                                            <div class="flex flex-col items-center gap-y-4">
                                                                <div>
                                                                    <div class="flex items-center gap-4">
                                                                        <img
                                                                            v-if="item.audio_answer_show"
                                                                            class="mt-2 h-[30px] w-[30px] cursor-pointer"
                                                                            src="/images/icon-sound.png"
                                                                            alt=""
                                                                        />
                                                                        <a-popover
                                                                            :open="openPopoverIndexAnswer === index"
                                                                            @open-change="
                                                                                        (visible) =>
                                                                                          handlePopoverChange(visible, index)
                                                                                      "
                                                                            v-else
                                                                            trigger="click"
                                                                        >
                                                                            <template #content>
                                                                                <div
                                                                                    @click="triggerAudioQuestion(index)"
                                                                                    class="cursor-pointer border border-transparent p-2 transition duration-300 hover:rounded-xl hover:bg-[#d9d9d9]"
                                                                                >
                                                                                    Tải file âm thanh thanh lên
                                                                                </div>
                                                                                <div
                                                                                    v-for="(
                                                                                        item, indexVoice
                                                                                      ) in listVoice"
                                                                                    :key="indexVoice"
                                                                                    class="cursor-pointer border border-transparent p-2 transition duration-300 hover:rounded-xl hover:bg-[#d9d9d9]"
                                                                                >
                                                                                  <span
                                                                                      @click="
                                                                                      textToSpeechQuestion(item, index)
                                                                                    "
                                                                                  >
                                                                                    {{ item.name }}
                                                                                  </span>
                                                                                </div>
                                                                            </template>
                                                                            <img
                                                                                class="mt-2 h-[30px] w-[30px] cursor-pointer"
                                                                                src="/images/icon-game-choose-correct/icon-sound-grey.png"
                                                                                alt=""
                                                                            />
                                                                        </a-popover>
                                                                        <!--                                                                        <a-checkbox-->
                                                                        <!--                                                                            class="custom-checkbox mt-2"-->
                                                                        <!--                                                                            v-model:checked="-->
                                                                        <!--                                                                                item.checked-->
                                                                        <!--                                                                            "-->
                                                                        <!--                                                                        >-->
                                                                        <!--                                                                        </a-checkbox>-->
                                                                    </div>
                                                                </div>
                                                                <div
                                                                    v-if="item.audio_answer_show"
                                                                    class="flex flex-col items-center gap-y-4"
                                                                >
                                                                    <div class="relative">
                                                                        <audio
                                                                            class="w-[7rem]"
                                                                            ref="audioPlayer"
                                                                            :src="item.audio_answer_show"
                                                                            controls
                                                                            @mouseenter="item.showTitleAnswer = true"
                                                                            @mouseleave="item.showTitleAnswer = false"
                                                                        ></audio>
                                                                        <div
                                                                            v-if="item.showTitleAnswer"
                                                                            class="tooltip absolute"
                                                                        >
                                                                            {{ item.nameVoiceAnswer }}
                                                                        </div>
                                                                    </div>
                                                                    <img
                                                                        @click="removeAudioQuestion(index)"
                                                                        src="/images/icon-game-choose-correct/icon-delete.png"
                                                                        alt=""
                                                                        class="h-[30px] w-[30px] cursor-pointer"
                                                                    />
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <input
                                                            type="file"
                                                            accept="audio/*"
                                                            class="hidden"
                                                            ref="fileAudioQuestion"
                                                            @change="handleUploadAudioQuestion(index)"
                                                        />
                                                    </div>
                                                </div>
                                                <!--                                                <Field-->
                                                <!--                                                    name="listAnswer"-->
                                                <!--                                                    class="hidden"-->
                                                <!--                                                    :rules="rules.listAnswer"-->
                                                <!--                                                    v-model="form.listAnswer"-->
                                                <!--                                                >-->
                                                <!--                                                </Field>-->
                                                <!--                                                <ErrorMessage-->
                                                <!--                                                    class="text-sm text-red-600"-->
                                                <!--                                                    name="listAnswer"-->
                                                <!--                                                />-->
                                                <!--                                                <InputError-->
                                                <!--                                                    class="mt-2"-->
                                                <!--                                                    :message="-->
                                                <!--                                                        form.errors.listAnswer-->
                                                <!--                                                    "-->
                                                <!--                                                />-->
                                            </div>
                                            <div
                                                v-if="
                                                      infoTemplate.number_answer ===
                                                      infoTemplate.number_answer_connect
                                                    "
                                                class="text-semibold mt-2 flex items-center italic text-[#2C75E3]"
                                            >
                                                (Câu hỏi ở cột trái tương ứng với đáp án ở cột bên phải.
                                                Người dùng không cần đảo đáp án)
                                            </div>
                                            <div
                                                v-else
                                                class="text-semibold mt-2 flex items-center italic text-[#2C75E3]"
                                            >
                                                (Mỗi câu hỏi ở cột trái có thể nối với 1-2 hoặc 3... đáp
                                                án ở cột phải. Ở cột bên phải, bạn hãy đảo đáp án và
                                                điền số đáp án tương ứng: Đáp án cho câu hỏi 0 điền số
                                                0, đáp án cho câu hỏi 1 điền số 1... Đáp án nhiễu (sai)
                                                điền -1)
                                            </div>
                                        </div>
                                        <div class="mb-4 w-1/2 gap-4">
                                            <div class="w-full">
                                                <div class="text-sm font-bold">
                                                    Các đáp án tương ứng:
                                                </div>
                                                <div
                                                    v-for="(item, index) in form.listAnswerConnect"
                                                    :key="index"
                                                    class="mt-2 flex-col items-center gap-4"
                                                >
                                                    <div class="w-full">
                                                        <a-select
                                                            class="input-search w-1/3"
                                                            v-model:value="item.type_answer"
                                                            show-search
                                                            placeholder="Text"
                                                            size="large"
                                                            :options="filteredOptionsAnswer"
                                                            :filter-option="filterOption"
                                                            @change="handleChangeTypeAnswer(index)"
                                                        ></a-select>
                                                    </div>
                                                    <div class="mt-1 flex w-3/4 items-center gap-4">
                                                        <input
                                                            v-model="item.inputNumber"
                                                            class="h-[40px] w-[50px] rounded-md border-2 border-solid border-[#E5E5E5]"
                                                        />
                                                        <a-textarea
                                                            v-if="
                                                                    item.type_answer === 1 || item.type_answer === 3
                                                                  "
                                                            placeholder="Bạn nhập nội dung hoặc tải ảnh ở đây"
                                                            :allow-clear="true"
                                                            v-model:value="item.inputAnswer"
                                                            :rows="3"
                                                            :class="`mt-[0.125rem] ${item.type_answer === 1 ? 'mb-[0.4rem]' : ''}`"
                                                            class="w-full"
                                                        >
                                                        </a-textarea>
                                                        <div class="flex justify-between items-center text-xs mt-[2px]">
                                                          <span v-if="item.type_answer !== 3 && item.inputAnswer.length > getAnswerMaxLength()" class="text-red-500 font-medium">Vượt ký tự, chọn Âm thanh</span>
                                                          <span v-else></span>
                                                          <span :class="item.type_answer !== 3 && item.inputAnswer.length > getAnswerMaxLength() ? 'text-red-500 font-medium' : 'text-gray-400'">{{ item.inputAnswer.length }}/{{ getAnswerMaxLength() }}</span>
                                                        </div>
                                                        <div v-if="item.type_answer === 2" class="flex w-full items-center gap-2">
                                                            <img
                                                                v-if="item.image_answer_show"
                                                                :src="item.image_answer_show"
                                                                @click="triggerFileInputQuestion(index)"
                                                                alt="Preview"
                                                                class="mb-[0.675rem] mt-1 h-[145px] w-full cursor-pointer object-contain"
                                                            />
                                                            <img
                                                                v-else
                                                                class="mb-[0.675rem] mt-1 h-[145px] w-full cursor-pointer"
                                                                src="/images/icon-select-image.png"
                                                                alt=""
                                                                @click="triggerFileInputAnswer(index)"
                                                            />
                                                            <div>
                                                                <img
                                                                    v-if="item.image_answer_show"
                                                                    @click="removeImageAnswer(index)"
                                                                    class="h-[30px] w-[30px] cursor-pointer"
                                                                    src="/images/icon-game-choose-correct/icon-delete.png"
                                                                    alt=""
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
                                                        <div v-if="item.type_answer === 3">
                                                            <div class="flex flex-col items-center gap-y-4">
                                                                <div>
                                                                    <div class="flex items-center gap-4">
                                                                        <img
                                                                            v-if="item.audio_answer_show"
                                                                            class="mt-2 h-[30px] w-[30px] cursor-pointer"
                                                                            src="/images/icon-sound.png"
                                                                            alt=""
                                                                        />
                                                                        <a-popover
                                                                            :open="
                                        openPopoverIndexAnswerConnect === index
                                      "
                                                                            @open-change="
                                        (visible) =>
                                          handlePopoverChangeConnect(
                                            visible,
                                            index,
                                          )
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
                                                                                    v-for="(
                                            item, indexVoice
                                          ) in listVoice"
                                                                                    :key="indexVoice"
                                                                                    class="cursor-pointer border border-transparent p-2 transition duration-300 hover:rounded-xl hover:bg-[#d9d9d9]"
                                                                                >
                                          <span
                                              @click="
                                              textToSpeechAnswer(item, index)
                                            "
                                          >
                                            {{ item.name }}
                                          </span>
                                                                                </div>
                                                                            </template>
                                                                            <img
                                                                                class="mt-2 h-[30px] w-[30px] cursor-pointer"
                                                                                src="/images/icon-game-choose-correct/icon-sound-grey.png"
                                                                                alt=""
                                                                            />
                                                                        </a-popover>
                                                                        <!--                                                                        <a-checkbox-->
                                                                        <!--                                                                            class="custom-checkbox mt-2"-->
                                                                        <!--                                                                            v-model:checked="-->
                                                                        <!--                                                                                item.checked-->
                                                                        <!--                                                                            "-->
                                                                        <!--                                                                        >-->
                                                                        <!--                                                                        </a-checkbox>-->
                                                                    </div>
                                                                </div>
                                                                <div
                                                                    v-if="item.audio_answer_show"
                                                                    class="flex flex-col items-center gap-y-4"
                                                                >
                                                                    <div class="relative">
                                                                        <audio
                                                                            class="w-[7rem]"
                                                                            ref="audioPlayer"
                                                                            :src="item.audio_answer_show"
                                                                            controls
                                                                            @mouseenter="
                                        item.showTitleAnswerConnect = true
                                      "
                                                                            @mouseleave="
                                        item.showTitleAnswerConnect = false
                                      "
                                                                        ></audio>
                                                                        <div
                                                                            v-if="item.showTitleAnswerConnect"
                                                                            class="tooltip absolute"
                                                                        >
                                                                            {{ item.nameVoiceAnswerConnect }}
                                                                        </div>
                                                                    </div>
                                                                    <img
                                                                        @click="removeAudioAnswer(index)"
                                                                        src="/images/icon-game-choose-correct/icon-delete.png"
                                                                        alt=""
                                                                        class="h-[30px] w-[30px] cursor-pointer"
                                                                    />
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
                                                <!--                                                <Field-->
                                                <!--                                                    name="listAnswerConnect"-->
                                                <!--                                                    class="hidden"-->
                                                <!--                                                    :rules="rules.listAnswerConnect"-->
                                                <!--                                                    v-model="form.listAnswerConnect"-->
                                                <!--                                                >-->
                                                <!--                                                </Field>-->
                                                <!--                                                <ErrorMessage-->
                                                <!--                                                    class="text-sm text-red-600"-->
                                                <!--                                                    name="listAnswerConnect"-->
                                                <!--                                                />-->
                                                <!--                                                <InputError-->
                                                <!--                                                    class="mt-2"-->
                                                <!--                                                    :message="-->
                                                <!--                                                        form.errors.listAnswerConnect-->
                                                <!--                                                    "-->
                                                <!--                                                />-->
                                            </div>
                                            <div class="flex w-full gap-4">
                                                <div class="flex w-full items-center gap-4">
                                                    <div class="flex w-full gap-2">
                                                        <img v-if="!form.content && !form.image_show" @click="addReading" src="/images/reading/add-read.png" alt="" class="cursor-pointer">
                                                        <img v-else @click="addReading" src="/images/reading/edit-reading.png" alt="" class="cursor-pointer">
                                                    </div>
                                                </div>
                                                <div class="flex w-full items-center gap-4">
                                                    <div class="flex w-full gap-2">
                                                        <div>
                                                            <img v-if="!form.image_background_show" @click="triggerFileInputBackground" src="/images/reading/add-background.png" alt="" class="cursor-pointer">
                                                            <img v-else @click="triggerFileInputBackground" src="/images/reading/edit-background.png" alt="" class="cursor-pointer">
                                                        </div>
                                                        <div
                                                            v-if="form.image_background_show"
                                                            class="flex items-center gap-2"
                                                        >
                                                            <div
                                                                class="max-w-[100px] truncate 2xl-custom:max-w-[200px]"
                                                            >
                                                                {{ form.image_background_show }}
                                                            </div>
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
                    <div class="custom-ml mt-4 flex gap-4">
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
                        <!--                        <a-button class="custom-bg text-black" size="large">-->
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
<style scoped lang="scss">
.custom-height {
    min-height: 600px;
    padding-left: 2.5rem;
}

.custom-grid {
    display: inline-grid;
    row-gap: 0.5rem;
}

.custom-ml {
    margin-left: 2rem;
}

.custom-gap {
    gap: 10rem;
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
