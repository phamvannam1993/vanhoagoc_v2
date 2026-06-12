<script setup>
import { Head, useForm, usePage } from "@inertiajs/vue3";
import SchoolLayout from "@/Layouts/SchoolLayout.vue"";
import Editor from "@tinymce/tinymce-vue";
import { ErrorMessage, Field, Form } from "vee-validate";
import * as yup from "yup";
import InputError from "@/Components/InputError.vue";
import { useToast } from "vue-toastification";
import { nextTick, ref } from "vue";
import ImageUploader from "@/Components/ImageUploader.vue";

const props = defineProps({
    practice_images: {
      type: Object
    },
});

const apiKey = import.meta.env.VITE_TINY_MCE_API_KEY;
const page = usePage();
const query = page.props.query;
const app_id = query.app_id;
const book_id = query.book_id;
const week_id = query.week_id;
const practice_id = query.practice_id;
const errorImage = ref("");
const errorContent = ref("");
const errorPdf = ref("");
const type_text = ref(1);
const active_text = ref(1);
const active_image = ref(2);
const form = useForm({
    content: "",
    image_show: "",
    image_db: "",
    pdf_show: "",
    pdf_db: ""
});
const toast = useToast();
const rules = {
    content: yup.string().required("Nội dung không được để trống")
};
const handleSubmit = () => {
    if (activeType.value === 'text' && !form.content) {
        errorContent.value = "Nội dung không được để trống!";
        return;
    }

    if (activeType.value === 'image' && !form.image_db) {
        errorImage.value = "Hình ảnh không được để trống!";
        return;
    }

    if (activeType.value === 'pdf' && !form.pdf_db) {
        errorImage.value = "Pdf file không được để trống!";
        return;
    }

    const formData = new FormData();
    formData.append("app_id", app_id);
    formData.append("week_id", week_id);
    formData.append("book_id", book_id);
    formData.append("practice_id", practice_id);
    formData.append("content", form.content);
    formData.append("image_show", form.image_show);
    formData.append("image_db", form.image_db);
    formData.append("pdf_db", form.pdf_db);
    formData.append("type_text", type_text.value);
    formData.append("active_text", active_text.value);
    formData.append("active_image", active_image.value);
    axios
        .post(route("lessons.reading.json.store"), formData)
        .then((response) => {
            if (response.data.status) {
                toast.success("Tạo bài đọc thành công");
                setTimeout(function() {
                    location.href = route("lessons.index", {
                        app_id: app_id,
                        book_id: book_id,
                        week_id: week_id
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
const removeContent = () => {
    if (activeType.value === 'text') {
        form.content = "";
    } else if (activeType.value === 'image') {
        form.image_show = "";
        form.image_db = "";
    } else {
        form.pdf_show = "";
        form.pdf_db = "";
    }
};
const configEditor = ref({});
const tinyMCEEditor = ref(null);
configEditor.value = {
    height: "70vh",
    plugins: "lists link image table code help wordcount",
    menubar: "file edit view format tools table", // Loại bỏ menu 'insert'
    statusbar: false,
    language: "vi",
    // toolbar: 'undo redo | formatselect fontselect fontsizeselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image', // Thêm formatselect và fontselect vào toolbar
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
        // Chặn copy-paste ảnh
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
const handleEditorInit = (editor) => {
    tinyMCEEditor.value = editor;
    console.log("TinyMCE đã sẵn sàng!");
};
const isUploading = ref(false);

// Image
const fileInput = ref(null);
const triggerFileInput = () => {
    fileInput.value.value = "";
    fileInput.value.click();
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

// Pdf
const fileInputPdf = ref(null);
const triggerFilePdf = () => {
    fileInputPdf.value.value = "";
    fileInputPdf.value.click();
};

const uploadFilePdf = async () => {
    const file = event.target.files[0];
    if (!file) return;

    if (file.type !== 'application/pdf') {
      return;
    }

    try {
        const formData = new FormData();
        formData.append("file", file);
        isUploading.value = true;
        const res = await axios.post("/api/upload-file", formData);
        form.pdf_show = res.data.s3_file_url;
        form.pdf_db = res.data.file_url;
    } catch (error) {
        console.error("Lỗi upload:", error);
    } finally {
        isUploading.value = false;
    }
};


const goBack = () => {
    window.location = route("lessons.index", { app_id, book_id, week_id });
};
const activeType = ref('text');
const setActive = (type) => {
    activeType.value = type;

    if (type !== 'text') {
        errorContent.value = "";
    }

    if (type !== 'image') {
        errorImage.value = "";
    }
}
const removeImages = ref([])
const handleUploadedImages = async (images) => {
    try {
        const formData = new FormData();
        images.forEach((img) => {
            formData.append("files[]", img.file);
        });

        formData.append("practice_id", practice_id);

        formData.append("remove_entities", JSON.stringify(removeImages.value));

        const res = await axios.post(route('lessons.reading.json.update-image'), formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            }
        });
        toast.success(res.data.message);
        if (res.data.status) {
            setTimeout(function () {
                location.href = route('lessons.index', {
                app_id: app_id,
                book_id: book_id,
                week_id: week_id,
                });
            }, 500);
        }
    } catch (err) {
        console.error("Lỗi upload:", err);
    } finally {
    }
};
</script>

<template>
    <Head title="Create Reading" />
    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">App</h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-full md:w-10/12">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Thêm bài đọc</h1>
                <div class="flex mt-4 gap-4">
                    <button class="px-4 py-2 font-medium bg-gray-300 rounded-md hover:bg-gray-400 transition" @click="setActive('text')"
                        :class="activeType === 'text' ? 'bg-gray-400' : ''">
                        Dạng text
                    </button>
                    <button class="px-4 py-2 font-medium bg-gray-300 rounded-md hover:bg-gray-400 transition" @click="setActive('image')"
                        :class="activeType === 'image' ? 'bg-gray-400' : ''">
                        Dạng ảnh
                    </button>
                    <button class="px-4 py-2 font-medium bg-gray-300 rounded-md hover:bg-gray-400 transition" @click="setActive('multi-image')"
                        :class="activeType === 'multi-image' ? 'bg-gray-400' : ''">
                        Dạng nhiều ảnh
                    </button>
                    <button class="px-4 py-2 font-medium bg-gray-300 rounded-md hover:bg-gray-400 transition" @click="setActive('pdf')"
                        :class="activeType === 'pdf' ? 'bg-gray-400' : ''">
                        Dạng pdf
                    </button>
                </div>
                <div class="mt-4">
                    <Form @submit="handleSubmit">
                        <Editor
                            v-if="activeType === 'text'"
                            v-model="form.content"
                            :api-key="apiKey"
                            :init="configEditor"
                            ref="tinyMCEEditor"
                        />
                        <div v-if="errorContent" class="mt-4 text-red-600">
                            {{ errorContent }}
                        </div>
                        <div v-if="activeType === 'image'"
                             class="flex justify-center h-[70vh] items-center rounded-2xl border border-solid border-blue-400 bg-white">
                            <img v-if="form.image_show" class="h-[60vh]" :src="form.image_show" alt="">
                            <div v-else class="justify-items-center">
                                <img
                                    @click="triggerFileInput"
                                    src="/images/sample-match-photo-3.png"
                                    alt="image-select"
                                    class="cursor-pointer w-[416px] h-[250px]"
                                />
                                <img
                                    class="h-[38px] mt-2 cursor-pointer"
                                    src="/images/icon-select.png"
                                    alt=""
                                    @click="triggerFileInput"
                                />
                            </div>
                        </div>
                        <div v-if="errorImage" class="text-red-600">
                            {{ errorImage }}
                        </div>
<!--                        <div class="relative mt-4 flex items-center">-->
<!--                            <div v-if="activeType === 'image'" @click="triggerFileInput" class="flex gap-4 mt-4">-->
<!--                                <span>Tải lên</span>-->
<!--                                <img-->
<!--                                    class="h-[25px] cursor-pointer"-->
<!--                                    src="/images/icon-upload-file.png"-->
<!--                                    alt=""-->
<!--                                />-->
<!--                            </div>-->
                            <input
                                id="file-input"
                                type="file"
                                accept="image/*"
                                class="hidden"
                                ref="fileInput"
                                @change="uploadFile"
                            />
<!--                        </div>-->
                        <div v-if="activeType === 'pdf'"
                             class="flex justify-center h-[70vh] items-center rounded-2xl border border-solid border-blue-400 bg-white">
                            <iframe
                                v-if="form.pdf_show"
                                :src="form.pdf_show"
                                class="w-full h-[600px] mt-4"
                                frameborder="0"
                            />
                            <div v-else class="justify-items-center">
                                <div>Bạn hãy tải file PDF</div>
                                <img
                                    class="h-[38px] mt-2 cursor-pointer"
                                    src="/images/icon-select.png"
                                    alt=""
                                    @click="triggerFilePdf"
                                />
                            </div>
                        </div>
                        <div v-if="errorPdf" class="text-red-600">
                            {{ errorPdf }}
                        </div>
                        <div v-if="activeType !== 'multi-image'" class="relative mt-4 flex items-center mb-4">
                            <input
                                id="file-input"
                                type="file"
                                accept="application/pdf"
                                class="hidden"
                                ref="fileInputPdf"
                                @change="uploadFilePdf"
                            />
                            <div class="absolute right-0 flex gap-4 mt-4">
                                <img
                                    @click="goBack"
                                    class="h-[34px] cursor-pointer"
                                    src="/images/icon-button-back.png"
                                    alt=""
                                />
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
                                    html-type="submit"
                                >
                                    Lưu
                                </a-button>
                            </div>
                        </div>
                    </Form>
                    <div v-if="activeType === 'multi-image'">
                        <ImageUploader title="Tải ảnh sản phẩm" class="mx-auto" @update:images="handleUploadedImages" :old-images="props.practice_images" @remove:old="handleRemoveOld"/>
                    </div>
                </div>
            </div>
        </div>
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
<style scoped>
.tox-statusbar {
    display: none;
}
</style>
