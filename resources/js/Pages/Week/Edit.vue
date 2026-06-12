<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { ErrorMessage, Field, Form } from 'vee-validate';
import * as yup from 'yup';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { BASE_URL } from '@/config.js';
import '@fortawesome/fontawesome-free/css/all.min.css';

const page = usePage();
const query = page.props.query;
const app_id = query.app_id;
const numberOfWeekOptions = page.props.numberOfWeekOptions;
const book_id = query.book_id;
const record = page.props.record;
const toast = useToast();
const filterOption = (input, option) => {
  return option.value.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};
const isUploading = ref(false);
const form = useForm({
  name: record.name,
  image: null,
  image_show: null,
  image_db: record.img,
  numberWeek: parseInt(record.numberWeek),
  delete_file: false,
});
const rules = {
  name: yup.string().required('Tên tuần không được để trống'),
};
const fileInput = ref(null);
const triggerFileInput = () => {
  fileInput.value.value = '';
  fileInput.value.click(); // Kích hoạt sự kiện click trên input
};
const handleFileChange = async (event) => {
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
    form.image_show = res.data.s3_file_url;
    form.image_db = res.data.file_url;
  } catch (error) {
    console.error('Lỗi upload:', error);
  } finally {
    isUploading.value = false;
  }
};
const handleSubmit = () => {
  const formData = new FormData();
  formData.append('id', record.id);
  formData.append('img', form.image_db);
  formData.append('name', form.name);
  formData.append('numberWeek', form.numberWeek);
  formData.append('delete_file', form.delete_file);
  axios
    .post(route('weeks.json.update'), formData)
    .then((response) => {
      if (response.data.status) {
        toast.success('Cập nhật tuần thành công');
        setTimeout(function () {
          location.href = response.data.data.redirectUrl;
        }, 500);
      } else {
        // form error
      }
    })
    .catch((error) => {
      toast.error(error);
    });
};
const removeImage = () => {
  record.img = null;
  form.image_db = '';
  form.delete_file = true;
};
const goBack = () => {
  window.location = route('weeks.index', { app_id, book_id });
};
</script>
<template>
  <Head title="Edit Week" />

  <SchoolLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Week</h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-4/5 sm:px-6 lg:px-8">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">Sửa tuần</h1>
        <Form @submit="handleSubmit">
          <div class="relative mt-6 w-4/5">
            <label
              for="appNameInput"
              class="block text-base font-semibold text-black"
            >
              Tên tuần<span class="text-red-600" aria-hidden="true">*</span>
            </label>
            <div class="mt-2 w-4/5">
              <Field name="name" :rules="rules.name" v-model="form.name">
                <a-input
                  placeholder="Bạn điền tên"
                  :allow-clear="true"
                  v-model:value="form.name"
                >
                </a-input>
              </Field>
              <ErrorMessage class="text-sm text-red-600" name="name" />
              <InputError class="mt-2" :message="form.errors.name" />
            </div>
          </div>
          <div class="relative mt-6 w-4/5">
            <label for="appNameInput" class="block text-base font-semibold text-black">ID tuần</label>
            <div class="mt-2 w-4/5">
              <a-select
                class="input-search w-full"
                :value="form.numberWeek"
                disabled
                placeholder="Tất cả app"
                size="large"
                :options="numberOfWeekOptions"
              ></a-select>
            </div>
          </div>
          <div class="relative mt-6 w-4/5">
            <label
              for="appNameInput"
              class="block text-base font-semibold text-black"
            >
              Ảnh đại diện
            </label>
            <div
              v-if="record.img && !form.image_show"
              class="image-container mt-2 w-4/5"
            >
              <img
                :src="record.img"
                @click="triggerFileInput"
                alt="Preview"
                class="image h-[174px] w-[376px] cursor-pointer object-contain"
              />
              <img
                @click="removeImage"
                class="delete-icon"
                src="/images/icon-game-choose-correct/icon-delete.png"
                alt=""
              />
            </div>
            <div v-else class="relative mt-2 w-4/5">
              <img
                v-if="form.image_show"
                :src="form.image_show"
                @click="triggerFileInput"
                alt="Preview"
                class="h-[174px] w-[376px] cursor-pointer object-contain"
              />
              <img
                v-else
                @click="triggerFileInput"
                src="/images/icon-select-image.png"
                alt="image-select"
                class="cursor-pointer"
              />
              <div
                v-if="!form.image_show"
                class="absolute inset-0 top-3/4 ml-[5rem] cursor-pointer lg:ml-[10rem]"
              >
                Chọn tệp
              </div>
            </div>
            <input
              id="file-input"
              type="file"
              accept="image/*"
              class="hidden"
              ref="fileInput"
              @change="handleFileChange"
            />
          </div>
          <div class="mt-4 flex gap-4">
            <a-button @click="goBack" class="custom-bg text-black" size="large">
              Quay lại
            </a-button>
            <a-button
              class="text-white"
              size="large"
              type="primary"
              html-type="submit"
            >
              Đi tiếp
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
<style scoped>
i {
  font-size: 24px;
  color: red;
}

/* Định dạng container */
.image-container {
  position: relative; /* Để định vị icon trên hình ảnh */
  display: inline-block;
  width: 376px; /* Kích thước của hình ảnh */
  height: 195px; /* Kích thước của hình ảnh */
  overflow: hidden; /* Đảm bảo phần thừa bị cắt */
}

/* Hình ảnh */
.image {
  width: 100%;
  height: 100%;
  object-fit: cover; /* Để hình ảnh không bị méo */
  border-radius: 8px; /* Tùy chọn bo góc */
}

/* Icon delete */
.delete-icon {
  position: absolute;
  top: 10px; /* Khoảng cách từ trên cùng */
  right: 10px; /* Khoảng cách từ bên phải */
  font-size: 24px; /* Kích thước icon */
  color: white; /* Màu icon */
  background-color: rgba(0, 0, 0, 0.5); /* Nền mờ cho icon */
  border-radius: 50%; /* Làm icon thành hình tròn */
  padding: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

/* Hiệu ứng khi hover vào icon */
.delete-icon:hover {
  background-color: rgba(255, 0, 0, 0.8); /* Đổi màu nền khi hover */
}
</style>
