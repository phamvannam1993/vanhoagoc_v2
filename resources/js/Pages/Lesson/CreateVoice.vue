<script setup>
import { Head, useForm, usePage } from "@inertiajs/vue3";
import MasterLayout from "@/Layouts/MasterLayout.vue";
import { ref } from "vue";
import { useToast } from "vue-toastification";
import InputError from "@/Components/InputError.vue";
import * as yup from "yup";
import { ErrorMessage, Field, Form } from "vee-validate";

const isUploading = ref(false);
const page = usePage();
const query = page.props.query;
const app_id = query.app_id;
const book_id = query.book_id;
const week_id = query.week_id;
const practice_id = query.practice_id;
const form = useForm({
    video: "",
    video_show: null,
    video_db: null
});
const isVideo = ref(false);
const fileInput = ref(null);
const triggerFileInput = () => {
    fileInput.value.value = "";
    fileInput.value.click();
};
const handleFileChange = async (event) => {
    const file = event.target.files[0];
    if (!file) return;
    const validTypes = ['audio/mpeg', 'audio/wav', 'audio/mp3', 'audio/x-wav', 'audio/ogg', 'video/mp4'];
    if (!validTypes.includes(file.type)) {
        return;
    }

    const formData = new FormData();
    formData.append("file", file);
    if (file && (file.type.startsWith("audio/") || file.type.startsWith("video/"))) {
        isUploading.value = true;
        try {
            if (file.type.startsWith("video/")) {
                isVideo.value = true;
            }
            const res = await axios.post("/api/upload-file", formData);
            form.video_show = res.data.s3_file_url;
            form.video_db = res.data.file_url;
            form.video = res.data.file_url;
        } catch (error) {
            console.error("Lỗi upload:", error);
        } finally {
            isUploading.value = false;
        }
    } else {
        console.log("Error file");
    }
};
const toast = useToast();
const rules = {
    video: yup.string().required("Video không được để trống")
};
const handleSubmit = () => {
    const formData = new FormData();
    formData.append("id", query.id);
    formData.append("field", "lesson_video");
    formData.append("app_id", app_id);
    formData.append("week_id", week_id);
    formData.append("book_id", book_id);
    formData.append("practice_id", practice_id);
    formData.append("content", form.video_db);
    formData.append("video_show", form.video_show);
    formData.append("video_db", form.video_db);

    axios
        .post(route("lessons.voice.json.store"), formData)
        .then((response) => {
            if (response.data.status) {
                toast.success("Tạo sách nói thành công");
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
    form.video = "";
    form.video_show = "";
    form.video_db = "";
    isVideo.value =false;
};
const goBack = () => {
    window.location = route("lessons.index", { app_id, book_id, week_id });
};
</script>

<template>
    <Head title="Create Voice Book" />
    <MasterLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Create Voice Book</h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-4/5 sm:px-6 lg:px-8">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Thêm sách nói</h1>
                <div
                    class="mt-4 min-h-[460px] w-full rounded-2xl border border-solid border-blue-400 bg-white p-9 max-md:max-w-full"
                    tabindex="0"
                >
                    <div class="relative mt-2 flex">
                        <div class="flex items-center gap-4">
                            <img
                                class="h-[38px] cursor-pointer"
                                src="/images/icon-select.png"
                                alt=""
                                @click="triggerFileInput"
                            />
                            <span v-if="!form.video_show">
                        Không có tệp nào được chọn
                      </span>
                            <input
                                type="file"
                                accept="audio/*,video/mp4"
                                class="hidden"
                                ref="fileInput"
                                @change="handleFileChange"
                            />
                        </div>
                    </div>
                    <div class="mt-4">
                        <div v-if="form.video_show">
                            <video v-if="isVideo" :src="form.video_show" controls width="600" height="300" />
                            <audio
                                v-else
                                class="mt-4"
                                ref="audioPlayer"
                                :src="form.video_show"
                                controls
                            ></audio>
                        </div>
                    </div>
                </div>
                <Form @submit="handleSubmit">
                    <Field
                        name="content"
                        :rules="rules.video"
                        v-model="form.video"
                        type="hidden"
                    >
                    </Field>
                    <ErrorMessage class="text-sm text-red-600" name="content" />
                    <InputError class="mt-2" :message="form.errors.video" />
                    <div class="float-right mt-4 flex gap-4">
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
                </Form>
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
    </MasterLayout>
</template>
<style scoped lang="scss">
.custom-input {
    border-color: #5fb2ff;
}

video {
    max-width: 100%;
    height: 325px;
}

iframe {
    max-width: 100%;
    height: 325px;
}
</style>
