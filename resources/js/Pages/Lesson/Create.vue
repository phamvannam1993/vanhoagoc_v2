<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import MasterLayout from '@/Layouts/MasterLayout.vue';
import { ErrorMessage, Field, Form } from 'vee-validate';
import * as yup from 'yup';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

const props = defineProps({
  numberPractice: {
    type: Number,
  },
  query: {
    type: Object,
  },
});
const app_id = props.query.app_id;
const book_id = props.query.book_id;
const week_id = props.query.week_id;
const toast = useToast();
const backgroundRaw  =  {
        style: 'default',
        value_show: '',
        value_db: '',
        value_name: ''
    };
const colorNotPracticeRaw  =  {
        style: 'default',
        value_show: '',
        value_db: '',
        value_name: ''
    };
const colorDonePracticeRaw  =  {
        style: 'default',
        value_show: '',
        value_db: '',
        value_name: ''
    };
const form = useForm({
  name: '',
  numberPractice: props.numberPractice,
  taptrung: 'true',
  image: null,
  image_show: '',
  image_db: '',
  background: backgroundRaw,
  color_not_practice: colorNotPracticeRaw,
  color_done_practice: colorDonePracticeRaw,
});
const options = ref([
  {
    value: 'true',
    label: 'Có',
  },
  {
    value: 'false',
    label: 'Không',
  },
]);
const rules = {
  name: yup.string().required('Tên app không được để trống'),
  numberPractice: yup.string().required('ID bài học không được để trống'),
};
const handleSubmit = () => {
    const formData = new FormData();
    formData.append('name', form.name);
    formData.append('numberPractice', form.numberPractice);
    formData.append('taptrung', form.taptrung);
    formData.append('week_id', week_id);
    formData.append('book_id', book_id);
    formData.append('app_id', app_id);
    formData.append('avatar', form.image_db);
    formData.append('background', JSON.stringify(form.background));
    formData.append('color_not_practice', JSON.stringify(form.color_not_practice));
    formData.append('color_done_practice', JSON.stringify(form.color_done_practice));
  axios
    .post(route('lessons.json.store'), formData)
    .then((response) => {
      if (response.data.status) {
        toast.success('Tạo bài học thành công');
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
const filterOption = (input, option) => {
  return option.value.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};
const filterOptionPractice = (input, option) => {
  return option.value.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};
const goBack = () => {
  window.location = route('lessons.index', { app_id, book_id, week_id });
};
const isUploading = ref(false);
const fileInput = ref(null);
const triggerFileInput = () => {
    fileInput.value.value = '';
    fileInput.value.click();
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
        formData.append('filename', file.name);
        formData.append('type', file.type);
        isUploading.value = true;
        const res = await axios.post('/api/upload-file-presigned', formData);

        const upload = await axios.put(res.data.s3_file_upload_url, file, {
            headers: {
                'Content-type': file.type,
            },
        });

        form.image_show = res.data.s3_file_url;
        form.image_db = res.data.file_url;
    } catch (error) {
        console.error('Lỗi upload:', error);
    } finally {
        isUploading.value = false;
    }
};
const removeImage = () => {
    form.image_show = '';
    form.image_db = '';
    form.delete_file = true;
};
const handleBackgroundChange = () => {
    form.background.value_show = '';
    form.background.value_db = '';
    form.background.value_name = '';
}
</script>

<template>
  <Head title="Create Lesson" />

  <MasterLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">App</h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-full md:w-10/12 sm:px-6 lg:px-8">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">Thêm mới bài học</h1>
        <Form @submit="handleSubmit">
            <div class="flex gap-8 w-full">
                <div class="w-1/2">
                    <div class="relative mt-6 w-full">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Tên bài học<span class="text-red-600" aria-hidden="true">*</span>
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
                    <div class="relative mt-6 w-full">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            ID bài học<span class="text-red-600" aria-hidden="true">*</span>
                        </label>
                        <div class="mt-2 w-4/5">
                            <Field name="numberPractice" :rules="rules.numberPractice" v-model="form.numberPractice">
                                <a-input
                                    v-model:value="form.numberPractice"
                                    placeholder="ID bài học"
                                    disabled
                                    readonly
                                ></a-input>
                            </Field>
                            <ErrorMessage class="text-sm text-red-600" name="numberPractice" />
                            <InputError class="mt-2" :message="form.errors.numberPractice" />
                        </div>
                    </div>
                    <div class="relative mt-6 w-full">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Tập trung
                        </label>
                        <div class="mt-2 w-4/5">
                            <a-select
                                class="input-search w-full"
                                v-model:value="form.taptrung"
                                show-search
                                placeholder="Tất cả app"
                                size="large"
                                :options="options"
                                :filter-option="filterOption"
                            ></a-select>
                        </div>
                    </div>
                    <div class="relative mt-6 w-full">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Ảnh đại diện
                        </label>
                        <div
                            v-if="form.image_show"
                            class="image-container mt-2 w-4/5"
                        >
                            <img
                                :src="form.image_show"
                                @click="triggerFileInput"
                                alt="Preview"
                                class="image h-[64px] w-[110px] cursor-pointer object-contain"
                            />
                            <img
                                @click="removeImage"
                                class="delete-icon cursor-pointer"
                                src="/images/icon-game-choose-correct/icon-delete.png"
                                alt=""
                            />
                        </div>
                        <div v-else class="relative mt-2 w-4/5">
                            <img
                                @click="triggerFileInput"
                                src="/images/icon-select-image.png"
                                alt="image-select"
                                class="cursor-pointer h-[64px] w-[110px]"
                            />
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
                </div>
                <div class="w-1/2">
                    <div class="relative mt-6 w-full">
                        <label for="appNameInput" class="block text-base font-semibold text-black">
                            Kiểu nền
                        </label>

                        <div class="mt-2 w-4/5 border border-[#5FB2FF] rounded-xl p-4">
                            <div class="flex justify-between items-start w-full">
                                <div class="flex flex-col gap-2">
                                    <a-radio-group v-model:value="form.background.style"  @change="handleBackgroundChange" class="flex flex-col gap-2">
                                        <a-radio :value="'default'">Mặc định</a-radio>
                                        <a-radio :value="'color'">Chọn màu nền</a-radio>
                                    </a-radio-group>
                                    <div v-if="form.background.style === 'color'" class="mt-4 flex items-center gap-4">
                                        <label class="text-sm font-medium">Chọn màu:</label>
                                        <a-input
                                            v-model:value="form.background.value_db"
                                            type="color"
                                            class="w-[50px] h-[34px] p-0 border-none shadow-none cursor-pointer"
                                        />
                                        <div class="text-sm">Màu đang chọn:
                                            <a-input class="w-1/2"
                                                     :style="{ borderColor: '#5FB2FF !important', borderWidth: '1px', borderStyle: 'solid', borderRadius: '0.75rem' }"
                                                     size="large"
                                                     :allow-clear="true" v-model:value="form.background.value_db"></a-input>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="relative mt-6 w-full">
                        <label for="appNameInput" class="block text-base font-semibold text-black">
                            Màu chữ trạng thái chưa làm bài luyện tập
                        </label>

                        <div class="mt-2 w-4/5 border border-[#5FB2FF] rounded-xl p-4">
                            <div class="flex justify-between items-start w-full">
                                <div class="flex flex-col gap-2">
                                    <a-radio-group v-model:value="form.color_not_practice.style"  @change="handleCorlorNotChange" class="flex flex-col gap-2">
                                        <a-radio :value="'default'">Mặc định</a-radio>
                                        <a-radio :value="'color'">Chọn màu chữ</a-radio>
                                    </a-radio-group>
                                    <div v-if="form.color_not_practice.style === 'color'" class="mt-4 flex items-center gap-4">
                                        <label class="text-sm font-medium">Chọn màu:</label>
                                        <a-input
                                            v-model:value="form.color_not_practice.value_db"
                                            type="color"
                                            class="w-[50px] h-[34px] p-0 border-none shadow-none cursor-pointer"
                                        />
                                        <div class="text-sm">Màu đang chọn:
                                            <a-input class="w-1/2"
                                                     :style="{ borderColor: '#5FB2FF !important', borderWidth: '1px', borderStyle: 'solid', borderRadius: '0.75rem' }"
                                                     size="large"
                                                     :allow-clear="true" v-model:value="form.color_not_practice.value_db"></a-input>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="relative mt-6 w-full">
                        <label for="appNameInput" class="block text-base font-semibold text-black">
                            Màu chữ trạng thái đã nộp bài luyện tập
                        </label>

                        <div class="mt-2 w-4/5 border border-[#5FB2FF] rounded-xl p-4">
                            <div class="flex justify-between items-start w-full">
                                <div class="flex flex-col gap-2">
                                    <a-radio-group v-model:value="form.color_done_practice.style"  @change="handleColorDoneChange" class="flex flex-col gap-2">
                                        <a-radio :value="'default'">Mặc định</a-radio>
                                        <a-radio :value="'color'">Chọn màu chữ</a-radio>
                                    </a-radio-group>
                                    <div v-if="form.color_done_practice.style === 'color'" class="mt-4 flex items-center gap-4">
                                        <label class="text-sm font-medium">Chọn màu:</label>
                                        <a-input
                                            v-model:value="form.color_done_practice.value_db"
                                            type="color"
                                            class="w-[50px] h-[34px] p-0 border-none shadow-none cursor-pointer"
                                        />
                                        <div class="text-sm">Màu đang chọn:
                                            <a-input class="w-1/2"
                                                     :style="{ borderColor: '#5FB2FF !important', borderWidth: '1px', borderStyle: 'solid', borderRadius: '0.75rem' }"
                                                     size="large"
                                                     :allow-clear="true" v-model:value="form.color_done_practice.value_db"></a-input>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
  </MasterLayout>
</template>
