<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { ref } from 'vue';
import { useToast } from 'vue-toastification';
import InputError from '@/Components/InputError.vue';
import * as yup from 'yup';
import { Link } from '@inertiajs/vue3';
import { ErrorMessage, Field, Form } from 'vee-validate';

const isUploading = ref(false);
const props = defineProps({
  video: {
    type: Object,
  },
  isYouTubeVideo: {
    type: Boolean,
  },
});
const page = usePage();
const query = page.props.query;
const app_id = query.app_id;
const book_id = query.book_id;
const week_id = query.week_id;
const practice_id = query.practice_id;
const form = useForm({
  video: props.video,
  video_show: props.video,
  video_db: props.video,
});
const isYouTubeVideo = ref(props.isYouTubeVideo);
const inputUrl = ref('');
const videoUrl = ref(!props.isYouTubeVideo ? props.video : null);
const fileInput = ref(null);
const youtubeEmbedUrl = ref(props.isYouTubeVideo ? props.video : null);
const triggerFileInput = () => {
  fileInput.value.value = '';
  fileInput.value.click();
};
const handleFileChange = async (event) => {
  const file = event.target.files[0];
  const formData = new FormData();
  formData.append('filename', file.name);
  formData.append('type', file.type);
  if (file && file.type.startsWith('video/')) {
    isUploading.value = true;
    try {
        const res = await axios.post('/api/upload-file-presigned', formData);

        const upload = await axios.put(res.data.s3_file_upload_url, file, {
            headers: {
                'Content-type': file.type,
            },
        });

      videoUrl.value = res.data.s3_file_url;
      form.video_show = res.data.s3_file_url;
      form.video_db = res.data.file_url;
      form.video = res.data.file_url;
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
const beforeDestroy = () => {
  // Giải phóng URL khi component bị huỷ
  if (videoUrl.value) {
    URL.revokeObjectURL(videoUrl.value);
  }
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
      form.video = youtubeEmbedUrl.value;
      form.video_show = youtubeEmbedUrl.value;
      form.video_db = youtubeEmbedUrl.value;
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
const toast = useToast();
const rules = {
  // video: yup.string().required("Video không được để trống")
};
const handleSubmit = () => {
  const formData = new FormData();
  formData.append('app_id', app_id);
  formData.append('week_id', week_id);
  formData.append('book_id', book_id);
  formData.append('practice_id', practice_id);
  formData.append('content', form.video_db);
  formData.append('video_show', form.video_show);
  formData.append('video_db', form.video_db);
  axios
    .post(route('lessons.video.json.store'), formData)
    .then((response) => {
      if (response.data.status) {
        toast.success('Chỉnh sửa video thành công');
        setTimeout(function () {
          location.href = route('lessons.index', {
            app_id: app_id,
            book_id: book_id,
            week_id: week_id,
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
  form.video = '';
  form.video_db = '';
  isYouTubeVideo.value = false;
  videoUrl.value = null;
  youtubeEmbedUrl.value = '';
  inputUrl.value = '';
};
const goBack = () => {
  window.location = route('lessons.index', { app_id, book_id, week_id });
};
</script>

<template>
  <Head title="Edit Video" />
  <SchoolLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">App</h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-4/5 sm:px-6 lg:px-8">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">Sửa video</h1>
        <div
          class="mt-4 min-h-[460px] w-full rounded-2xl border border-solid border-blue-400 bg-white p-9 max-md:max-w-full"
          tabindex="0"
        >
          <div>
            <a-input
              v-model:value="inputUrl"
              size="large"
              placeholder="Nhập link:"
              class="custom-input h-[38px] rounded-2xl"
              @input="updateVideoPreview"
            />
          </div>
          <div class="relative mt-2 flex">
            <div class="flex items-center gap-4">
              <img
                class="h-[38px] cursor-pointer"
                src="/images/icon-select.png"
                alt=""
                @click="triggerFileInput"
              />
              <span v-if="!videoUrl && !youtubeEmbedUrl">
                Không có tệp nào được chọn
              </span>
              <input
                type="file"
                accept="video/*"
                class="hidden"
                ref="fileInput"
                @change="handleFileChange"
              />
            </div>
          </div>
          <div class="mt-4">
            <div v-if="isYouTubeVideo">
              <!-- Nhúng video YouTube -->
              <iframe
                :src="youtubeEmbedUrl"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                width="600"
              >
              </iframe>
            </div>
            <div v-if="videoUrl">
              <!-- Nếu không phải YouTube, hiển thị video bình thường -->
              <video :src="videoUrl" controls width="600" />
            </div>
          </div>
        </div>
        <Form @submit="handleSubmit">
          <!--                    <Field-->
          <!--                        name="content"-->
          <!--                        :rules="rules.video"-->
          <!--                        v-model="form.video"-->
          <!--                        type="hidden"-->
          <!--                    >-->
          <!--                    </Field>-->
          <!--                    <ErrorMessage class="text-sm text-red-600" name="content" />-->
          <!--                    <InputError class="mt-2" :message="form.errors.video" />-->
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
  </SchoolLayout>
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
