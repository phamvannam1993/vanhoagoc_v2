<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { ErrorMessage, Field, Form } from 'vee-validate';
import * as yup from 'yup';
import InputError from '@/Components/InputError.vue';
import { useToast } from 'vue-toastification';
import { onMounted, ref } from 'vue';
const props = defineProps({
  app: {
    type: Object,
  },
});

const toast = useToast();
const page = usePage();
const query = page.props.query;
const app_id = query.appId;
const backgroundRaw  = props.app?.background
    ? JSON.parse(props.app.background)
    : {
        style: 'default',
        value_show: '',
        value_db: '',
        value_name: ''
    };
const backgroundSoundRaw  = props.app?.background_sound
    ? JSON.parse(props.app.background_sound)
    : {
        style: 'default',
        value_show: '',
        value_db: ''
    };
const soundClickRaw  = props.app?.sound_click
    ? JSON.parse(props.app.sound_click)
    : {
        style: 'default',
        value_show: '',
        value_db: ''
    };
const soundChooseCorrectRaw  = props.app?.sound_choose_correct
    ? JSON.parse(props.app.sound_choose_correct)
    : {
        style: 'default',
        value_show: '',
        value_db: ''
    };
const soundChooseWrong  = props.app?.sound_choose_wrong
    ? JSON.parse(props.app.sound_choose_wrong)
    : {
        style: 'default',
        value_show: '',
        value_db: ''
    };
const soundOneCorrectRaw  = props.app?.sound_one_correct
    ? JSON.parse(props.app.sound_one_correct)
    : {
        style: 'default',
        value_show: '',
        value_db: ''
    };
const soundOneWrongRaw  = props.app?.sound_one_wrong
    ? JSON.parse(props.app.sound_one_wrong)
    : {
        style: 'default',
        value_show: '',
        value_db: ''
    };
const soundMultiCorrectRaw  = props.app?.sound_multi_correct
    ? JSON.parse(props.app.sound_multi_correct)
    : {
        style: 'default',
        value_show: '',
        value_db: '',
        number: 5,
        content: ''
    };
const soundMultiWrongRaw  = props.app?.sound_multi_wrong
    ? JSON.parse(props.app.sound_multi_wrong)
    : {
        style: 'default',
        value_show: '',
        value_db: '',
        number: 5,
        content: ''
    };
const soundResultExcellentRaw  = props.app?.sound_result_excellent
    ? JSON.parse(props.app.sound_result_excellent)
    : {
        style: 'default',
        value_show: '',
        value_db: ''
    };
const soundResultGoodRaw  = props.app?.sound_result_good
    ? JSON.parse(props.app.sound_result_good)
    : {
        style: 'default',
        value_show: '',
        value_db: ''
    };
const soundResultAverageRaw  = props.app?.sound_result_average
    ? JSON.parse(props.app.sound_result_average)
    : {
        style: 'default',
        value_show: '',
        value_db: ''
    };

const form = useForm({
  name: props.app.name,
  image: '',
  image_show: '',
  image_db: props.app?.img ?? '',
  delete_file: false,
  background: backgroundRaw,
  background_sound: backgroundSoundRaw,
  sound_click: soundClickRaw,
  sound_choose_correct: soundChooseCorrectRaw,
  sound_choose_wrong: soundChooseWrong,
  sound_one_correct: soundOneCorrectRaw,
  sound_one_wrong: soundOneWrongRaw,
  sound_multi_correct: soundMultiCorrectRaw,
  sound_multi_wrong: soundMultiWrongRaw,
  sound_result_excellent: soundResultExcellentRaw,
  sound_result_good: soundResultGoodRaw,
  sound_result_average: soundResultAverageRaw
});
const rules = {
  name: yup.string().required('Tên app không được để trống'),
};
const isUploading = ref(false);
const handleSubmit = () => {
  const formData = new FormData();
  formData.append('img', form.image_db);
  formData.append('name', form.name);
  formData.append('app_id', app_id);
  formData.append('id', props.app.id);
  formData.append('delete_file', form.delete_file);
  formData.append('background', JSON.stringify(form.background));
  formData.append('background_sound', JSON.stringify(form.background_sound));
  formData.append('sound_click', JSON.stringify(form.sound_click));
  formData.append('sound_choose_correct', JSON.stringify(form.sound_choose_correct));
  formData.append('sound_choose_wrong', JSON.stringify(form.sound_choose_wrong));
  formData.append('sound_one_correct', JSON.stringify(form.sound_one_correct));
  formData.append('sound_one_wrong', JSON.stringify(form.sound_one_wrong));
  formData.append('sound_multi_correct', JSON.stringify(form.sound_multi_correct));
  formData.append('sound_multi_wrong', JSON.stringify(form.sound_multi_wrong));
  formData.append('sound_result_excellent', JSON.stringify(form.sound_result_excellent));
  formData.append('sound_result_good', JSON.stringify(form.sound_result_good));
  formData.append('sound_result_average', JSON.stringify(form.sound_result_average));

  axios
    .post(route('apps.json.update'), formData)
    .then((response) => {
      if (response.status === 200) {
        toast.success('Cập nhật app thành công');
        setTimeout(function () {
          location.href = '/apps'; // URL cần chuyển hướng
        }, 500);
      }
    })
    .catch((error) => {
      if (error.response.status === 404) {
        form.errors.name = error.response.data.message;
      } else {
        toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
      }
    });
};
onMounted(() => {
  form.name = props.app.name;
});
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
  props.app.img_show = '';
  form.image_show = '';
  form.image_db = '';
  form.delete_file = true;
};
const goBack = () => {
  window.location = route('apps.dashboard');
};
// Options setting
const fileInputBackground = ref(null);
const triggerFileInputBackground = () => {
    fileInputBackground.value.value = '';
    fileInputBackground.value.click();
};
const removeImageBackground = () => {

}

const handleUploadBackground = async (event) => {
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

        form.background.value_show = res.data.s3_file_url;
        form.background.value_db = res.data.file_url;
    } catch (error) {
        console.error('Lỗi upload:', error);
    } finally {
        isUploading.value = false;
    }
};
const handleBackgroundChange = () => {
    form.background.value_show = '';
    form.background.value_db = '';
    form.background.value_name = '';
}

// Background sound
const fileBackgroundSound = ref(null);
const triggerBackgroundSound = () => {
    fileBackgroundSound.value.value = '';
    fileBackgroundSound.value.click();
};
const handleBackgroundSoundChange = () => {
    form.background_sound.value_show = '';
    form.background_sound.value_db = '';
}
const handleUploadBackgroundSound = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    try {
        const formData = new FormData();
        formData.append('file', file);
        isUploading.value = true;
        const res = await axios.post('/api/upload-file', formData);
        form.background_sound.value_show = file.name;
        form.background_sound.value_db = res.data.file_url;
    } catch (error) {
        console.error('Lỗi upload:', error);
    } finally {
        isUploading.value = false;
    }
}

// Sound click
const fileSoundClick = ref(null);
const triggerSoundClick = () => {
    fileSoundClick.value.value = '';
    fileSoundClick.value.click();
};
const handleSoundClickChange = () => {
    form.sound_click.value_show = '';
    form.sound_click.value_db = '';
}
const handleUploadSoundClick = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    try {
        const formData = new FormData();
        formData.append('file', file);
        isUploading.value = true;
        const res = await axios.post('/api/upload-file', formData);
        form.sound_click.value_show = file.name;
        form.sound_click.value_db = res.data.file_url;
    } catch (error) {
        console.error('Lỗi upload:', error);
    } finally {
        isUploading.value = false;
    }
}

// Sound choose correct
const fileSoundChooseCorrect = ref(null);
const triggerSoundChooseCorrect = () => {
    fileSoundChooseCorrect.value.value = '';
    fileSoundChooseCorrect.value.click();
};
const handleSoundChooseCorrectChange = () => {
    form.sound_choose_correct.value_show = '';
    form.sound_choose_correct.value_db = '';
}
const handleUploadSoundChooseCorrect = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    try {
        const formData = new FormData();
        formData.append('file', file);
        isUploading.value = true;
        const res = await axios.post('/api/upload-file', formData);
        form.sound_choose_correct.value_show = file.name;
        form.sound_choose_correct.value_db = res.data.file_url;
    } catch (error) {
        console.error('Lỗi upload:', error);
    } finally {
        isUploading.value = false;
    }
}

// Sound choose wrong
const fileSoundChooseWrong = ref(null);
const triggerSoundChooseWrong = () => {
    fileSoundChooseWrong.value.value = '';
    fileSoundChooseWrong.value.click();
};
const handleSoundChooseWrongChange = () => {
    form.sound_choose_wrong.value_show = '';
    form.sound_choose_wrong.value_db = '';
}
const handleUploadSoundChooseWrong = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    try {
        const formData = new FormData();
        formData.append('file', file);
        isUploading.value = true;
        const res = await axios.post('/api/upload-file', formData);
        form.sound_choose_wrong.value_show = file.name;
        form.sound_choose_wrong.value_db = res.data.file_url;
    } catch (error) {
        console.error('Lỗi upload:', error);
    } finally {
        isUploading.value = false;
    }
}

// Sound one correct
const fileSoundOneCorrect = ref(null);
const triggerSoundOneCorrect = () => {
    fileSoundOneCorrect.value.value = '';
    fileSoundOneCorrect.value.click();
};
const handleSoundOneCorrectChange = () => {
    form.sound_one_correct.value_show = '';
    form.sound_one_correct.value_db = '';
}
const handleUploadSoundOneCorrect = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    try {
        const formData = new FormData();
        formData.append('file', file);
        isUploading.value = true;
        const res = await axios.post('/api/upload-file', formData);
        form.sound_one_correct.value_show = file.name;
        form.sound_one_correct.value_db = res.data.file_url;
    } catch (error) {
        console.error('Lỗi upload:', error);
    } finally {
        isUploading.value = false;
    }
}

// Sound one wrong
const fileSoundOneWrong = ref(null);
const triggerSoundOneWrong = () => {
    fileSoundOneWrong.value.value = '';
    fileSoundOneWrong.value.click();
};
const handleSoundOneWrongChange = () => {
    form.sound_one_wrong.value_show = '';
    form.sound_one_wrong.value_db = '';
}
const handleUploadSoundOneWrong = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    try {
        const formData = new FormData();
        formData.append('file', file);
        isUploading.value = true;
        const res = await axios.post('/api/upload-file', formData);
        form.sound_one_wrong.value_show = file.name;
        form.sound_one_wrong.value_db = res.data.file_url;
    } catch (error) {
        console.error('Lỗi upload:', error);
    } finally {
        isUploading.value = false;
    }
}

// Sound multi correct
const fileSoundMultiCorrect = ref(null);
const triggerSoundMultiCorrect = () => {
    fileSoundMultiCorrect.value.value = '';
    fileSoundMultiCorrect.value.click();
};
const handleSoundMultiCorrectChange = () => {
    form.sound_multi_correct.value_show = '';
    form.sound_multi_correct.value_db = '';
}
const handleUploadSoundMultiCorrect = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    try {
        const formData = new FormData();
        formData.append('file', file);
        isUploading.value = true;
        const res = await axios.post('/api/upload-file', formData);
        form.sound_multi_correct.value_show = file.name;
        form.sound_multi_correct.value_db = res.data.file_url;
    } catch (error) {
        console.error('Lỗi upload:', error);
    } finally {
        isUploading.value = false;
    }
}

// Sound multi wrong
const fileSoundMultiWrong = ref(null);
const triggerSoundMultiWrong = () => {
    fileSoundMultiWrong.value.value = '';
    fileSoundMultiWrong.value.click();
};
const handleSoundMultiWrongChange = () => {
    form.sound_multi_wrong.value_show = '';
    form.sound_multi_wrong.value_db = '';
}
const handleUploadSoundMultiWrong = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    try {
        const formData = new FormData();
        formData.append('file', file);
        isUploading.value = true;
        const res = await axios.post('/api/upload-file', formData);
        form.sound_multi_wrong.value_show = file.name;
        form.sound_multi_wrong.value_db = res.data.file_url;
    } catch (error) {
        console.error('Lỗi upload:', error);
    } finally {
        isUploading.value = false;
    }
}

// Excellent Result
const fileSoundResultExcellent = ref(null);
const triggerSoundResultExcellent = () => {
    fileSoundResultExcellent.value.value = '';
    fileSoundResultExcellent.value.click();
};
const handleSoundResultExcellent = () => {
    form.sound_result_excellent.value_show = '';
    form.sound_result_excellent.value_db = '';
}
const handleUploadSoundResultExcellent = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    try {
        const formData = new FormData();
        formData.append('file', file);
        isUploading.value = true;
        const res = await axios.post('/api/upload-file', formData);
        form.sound_result_excellent.value_show = file.name;
        form.sound_result_excellent.value_db = res.data.file_url;
    } catch (error) {
        console.error('Lỗi upload:', error);
    } finally {
        isUploading.value = false;
    }
}

// Good Result
const fileSoundResultGood = ref(null);
const triggerSoundResultGood = () => {
    fileSoundResultGood.value.value = '';
    fileSoundResultGood.value.click();
};
const handleSoundResultGood = () => {
    form.sound_result_good.value_show = '';
    form.sound_result_good.value_db = '';
}
const handleUploadSoundResultGood = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    try {
        const formData = new FormData();
        formData.append('file', file);
        isUploading.value = true;
        const res = await axios.post('/api/upload-file', formData);
        form.sound_result_good.value_show = file.name;
        form.sound_result_good.value_db = res.data.file_url;
    } catch (error) {
        console.error('Lỗi upload:', error);
    } finally {
        isUploading.value = false;
    }
}

// Average Result
const fileSoundResultAverage = ref(null);
const triggerSoundResultAverage = () => {
    fileSoundResultAverage.value.value = '';
    fileSoundResultAverage.value.click();
};
const handleSoundResultAverage = () => {
    form.sound_result_average.value_show = '';
    form.sound_result_average.value_db = '';
}
const handleUploadSoundResultAverage = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    try {
        const formData = new FormData();
        formData.append('file', file);
        isUploading.value = true;
        const res = await axios.post('/api/upload-file', formData);
        form.sound_result_average.value_show = file.name;
        form.sound_result_average.value_db = res.data.file_url;
    } catch (error) {
        console.error('Lỗi upload:', error);
    } finally {
        isUploading.value = false;
    }
}
</script>

<template>
  <Head title="Chỉnh sửa App" />

  <SchoolLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">App</h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-full md:w-10/12 sm:px-6 lg:px-8">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">Cài đặt App</h1>
        <Form @submit="handleSubmit">
            <div class="flex gap-8">
                <div class="w-1/2">
                    <div class="relative mt-6 w-full">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Tên App<span class="text-red-600" aria-hidden="true">*</span>
                        </label>
                        <div class="mt-2 w-4/5">
                            <Field name="name" :rules="rules.name" v-model="form.name">
                                <a-input
                                    :style="{ borderColor: '#5FB2FF !important', borderWidth: '1px', borderStyle: 'solid', borderRadius: '0.75rem' }"
                                    size="large"
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
                            Ảnh đại diện
                        </label>
                        <div
                            v-if="props.app.img_show && !form.image_show"
                            class="image-container mt-2 w-4/5"
                        >
                            <img
                                :src="props.app.img_show"
                                @click="triggerFileInput"
                                alt="Preview"
                                class="image h-[64px] w-[110px] cursor-pointer object-contain"
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
                                class="h-[64px] w-[110px] cursor-pointer object-contain"
                            />
                            <img
                                v-else
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

                    <div class="relative mt-6 w-full">
                        <label for="appNameInput" class="block text-base font-semibold text-black">
                            Kiểu nền
                        </label>

                        <div class="mt-2 w-full border border-[#5FB2FF] rounded-xl p-4">
                            <div class="flex justify-between items-start w-full">
                                <div class="flex flex-col gap-2 w-3/4">
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
                                            <a-input class="w-1/3"
                                                     :style="{ borderColor: '#5FB2FF !important', borderWidth: '1px', borderStyle: 'solid', borderRadius: '0.75rem' }"
                                                     size="large"
                                                     :allow-clear="true" v-model:value="form.background.value_db"></a-input>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col items-end gap-2">
                                    <a-radio-group v-model:value="form.background.style">
                                        <a-radio :value="'image'">
                                            <span class="border rounded-xl p-2 bg-[#D9D9D9]">Tải ảnh lên</span>
                                        </a-radio>
                                    </a-radio-group>

                                    <div v-if="form.background.style === 'image'" class="flex items-center gap-2 mt-2">
                                        <div
                                            v-if="backgroundRaw.value_show && !form.background.value_show"
                                            class="image-container"
                                        >
                                            <!--                                            <img-->
                                            <!--                                                :src="props.app.background.value_show"-->
                                            <!--                                                @click="triggerFileInput"-->
                                            <!--                                                alt="Preview"-->
                                            <!--                                                class="w-[110px] h-[58px] cursor-pointer object-contain"-->
                                            <!--                                            />-->
                                            <!--                                            <img-->
                                            <!--                                                @click="removeImageBackground"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <div v-else>
                                            <img
                                                v-if="form.background.value_show"
                                                :src="form.background.value_show"
                                                @click="triggerFileInputBackground"
                                                alt="Preview"
                                                class="w-[110px] h-[58px] cursor-pointer object-contain"
                                            />
                                            <img
                                                v-else
                                                @click="triggerFileInputBackground"
                                                src="/images/app/upload-image-app.png"
                                                alt="image-select"
                                                class="cursor-pointer"
                                            />
                                        </div>

                                        <input
                                            ref="fileInputBackground"
                                            type="file"
                                            accept="image/*"
                                            class="hidden"
                                            @change="handleUploadBackground"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative mt-6 w-full">
                        <label for="appNameInput" class="block text-base font-semibold text-black">
                            Hiệu ứng âm thanh nút click
                        </label>

                        <div class="mt-2 w-full border border-[#5FB2FF] rounded-xl p-4">
                            <div class="flex justify-between items-start w-full">
                                <div class="flex flex-col gap-2">
                                    <a-radio-group v-model:value="form.sound_click.style" @change="handleSoundClickChange" class="flex flex-col gap-2">
                                        <a-radio :value="'default'">Mặc định</a-radio>
                                    </a-radio-group>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <a-radio-group v-model:value="form.sound_click.style">
                                        <a-radio :value="'sound'">
                                            <span @click="triggerSoundClick" class="border rounded-xl p-2 bg-[#D9D9D9]">Tải nhạc lên</span>
                                        </a-radio>
                                    </a-radio-group>
                                    <div v-if="form.sound_click.style === 'sound'" class="flex items-center gap-2 mt-2">
                                        <div
                                            v-if="props.app.background_sound_value_show && !form.sound_click.value_show"
                                            class="image-container"
                                        >
                                            <!--                                            <img-->
                                            <!--                                                :src="props.app.background.value_show"-->
                                            <!--                                                @click="triggerFileInput"-->
                                            <!--                                                alt="Preview"-->
                                            <!--                                                class="w-[110px] h-[58px] cursor-pointer object-contain"-->
                                            <!--                                            />-->
                                            <!--                                            <img-->
                                            <!--                                                @click="removeImageBackground"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <div v-else class="flex">
                                            <div>{{ form.sound_click.value_show }}</div>
                                            <!--                                            <img-->
                                            <!--                                                @click="removeBackgroundSound"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <input
                                            type="file"
                                            accept="audio/*"
                                            class="hidden"
                                            ref="fileSoundClick"
                                            @change="handleUploadSoundClick"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative mt-6 w-full">
                        <label for="appNameInput" class="block text-base font-semibold text-black">
                            Hiệu ứng âm thanh khi chọn đúng từ khóa/trả lời đúng khi xem video
                        </label>

                        <div class="mt-2 w-full border border-[#5FB2FF] rounded-xl p-4">
                            <div class="flex justify-between items-start w-full">
                                <div class="flex flex-col gap-2">
                                    <a-radio-group v-model:value="form.sound_choose_correct.style" @change="handleSoundChooseCorrectChange" class="flex flex-col gap-2">
                                        <a-radio :value="'default'">Mặc định</a-radio>
                                    </a-radio-group>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <a-radio-group v-model:value="form.sound_choose_correct.style">
                                        <a-radio :value="'sound'">
                                            <span @click="triggerSoundChooseCorrect" class="border rounded-xl p-2 bg-[#D9D9D9]">Tải nhạc lên</span>
                                        </a-radio>
                                    </a-radio-group>
                                    <div v-if="form.sound_choose_correct.style === 'sound'" class="flex items-center gap-2 mt-2">
                                        <div
                                            v-if="props.app.background_sound_value_show && !form.sound_choose_correct.value_show"
                                            class="image-container"
                                        >
                                            <!--                                            <img-->
                                            <!--                                                :src="props.app.background.value_show"-->
                                            <!--                                                @click="triggerFileInput"-->
                                            <!--                                                alt="Preview"-->
                                            <!--                                                class="w-[110px] h-[58px] cursor-pointer object-contain"-->
                                            <!--                                            />-->
                                            <!--                                            <img-->
                                            <!--                                                @click="removeImageBackground"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <div v-else class="flex">
                                            <div>{{ form.sound_choose_correct.value_show }}</div>
                                            <!--                                            <img-->
                                            <!--                                                @click="removeBackgroundSound"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <input
                                            type="file"
                                            accept="audio/*"
                                            class="hidden"
                                            ref="fileSoundChooseCorrect"
                                            @change="handleUploadSoundChooseCorrect"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative mt-6 w-full">
                        <label for="appNameInput" class="block text-base font-semibold text-black">
                            Hiệu ứng âm thanh khi chọn sai từ khóa/trả lời sai khi xem video
                        </label>

                        <div class="mt-2 w-full border border-[#5FB2FF] rounded-xl p-4">
                            <div class="flex justify-between items-start w-full">
                                <div class="flex flex-col gap-2">
                                    <a-radio-group v-model:value="form.sound_choose_wrong.style" @change="handleSoundChooseCorrectChange" class="flex flex-col gap-2">
                                        <a-radio :value="'default'">Mặc định</a-radio>
                                    </a-radio-group>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <a-radio-group v-model:value="form.sound_choose_wrong.style">
                                        <a-radio :value="'sound'">
                                            <span @click="triggerSoundChooseWrong" class="border rounded-xl p-2 bg-[#D9D9D9]">Tải nhạc lên</span>
                                        </a-radio>
                                    </a-radio-group>
                                    <div v-if="form.sound_choose_wrong.style === 'sound'" class="flex items-center gap-2 mt-2">
                                        <div
                                            v-if="props.app.background_sound_value_show && !form.sound_choose_wrong.value_show"
                                            class="image-container"
                                        >
                                            <!--                                            <img-->
                                            <!--                                                :src="props.app.background.value_show"-->
                                            <!--                                                @click="triggerFileInput"-->
                                            <!--                                                alt="Preview"-->
                                            <!--                                                class="w-[110px] h-[58px] cursor-pointer object-contain"-->
                                            <!--                                            />-->
                                            <!--                                            <img-->
                                            <!--                                                @click="removeImageBackground"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <div v-else class="flex">
                                            <div>{{ form.sound_choose_wrong.value_show }}</div>
                                            <!--                                            <img-->
                                            <!--                                                @click="removeBackgroundSound"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <input
                                            type="file"
                                            accept="audio/*"
                                            class="hidden"
                                            ref="fileSoundChooseWrong"
                                            @change="handleUploadSoundChooseWrong"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative mt-6 w-full">
                        <label for="appNameInput" class="block text-base font-semibold text-black">
                            Hiệu ứng âm thanh khi làm đúng 1 câu
                        </label>

                        <div class="mt-2 w-full border border-[#5FB2FF] rounded-xl p-4">
                            <div class="flex justify-between items-start w-full">
                                <div class="flex flex-col gap-2">
                                    <a-radio-group v-model:value="form.sound_one_correct.style" @change="handleSoundOneCorrectChange" class="flex flex-col gap-2">
                                        <a-radio :value="'default'">Mặc định</a-radio>
                                    </a-radio-group>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <a-radio-group v-model:value="form.sound_one_correct.style">
                                        <a-radio :value="'sound'">
                                            <span @click="triggerSoundOneCorrect" class="border rounded-xl p-2 bg-[#D9D9D9]">Tải nhạc lên</span>
                                        </a-radio>
                                    </a-radio-group>
                                    <div v-if="form.sound_one_correct.style === 'sound'" class="flex items-center gap-2 mt-2">
                                        <div
                                            v-if="props.app.background_sound_value_show && !form.sound_one_correct.value_show"
                                            class="image-container"
                                        >
                                            <!--                                            <img-->
                                            <!--                                                :src="props.app.background.value_show"-->
                                            <!--                                                @click="triggerFileInput"-->
                                            <!--                                                alt="Preview"-->
                                            <!--                                                class="w-[110px] h-[58px] cursor-pointer object-contain"-->
                                            <!--                                            />-->
                                            <!--                                            <img-->
                                            <!--                                                @click="removeImageBackground"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <div v-else class="flex">
                                            <div>{{ form.sound_one_correct.value_show }}</div>
                                            <!--                                            <img-->
                                            <!--                                                @click="removeBackgroundSound"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <input
                                            type="file"
                                            accept="audio/*"
                                            class="hidden"
                                            ref="fileSoundOneCorrect"
                                            @change="handleUploadSoundOneCorrect"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative mt-6 w-full">
                        <label for="appNameInput" class="block text-base font-semibold text-black">
                            Hiệu ứng âm thanh khi làm sai 1 câu
                        </label>

                        <div class="mt-2 w-full border border-[#5FB2FF] rounded-xl p-4">
                            <div class="flex justify-between items-start w-full">
                                <div class="flex flex-col gap-2">
                                    <a-radio-group v-model:value="form.sound_one_wrong.style" @change="handleSoundOneWrongChange" class="flex flex-col gap-2">
                                        <a-radio :value="'default'">Mặc định</a-radio>
                                    </a-radio-group>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <a-radio-group v-model:value="form.sound_one_wrong.style">
                                        <a-radio :value="'sound'">
                                            <span @click="triggerSoundOneWrong" class="border rounded-xl p-2 bg-[#D9D9D9]">Tải nhạc lên</span>
                                        </a-radio>
                                    </a-radio-group>
                                    <div v-if="form.sound_one_wrong.style === 'sound'" class="flex items-center gap-2 mt-2">
                                        <div
                                            v-if="props.app.background_sound_value_show && !form.sound_one_wrong.value_show"
                                            class="image-container"
                                        >
                                            <!--                                            <img-->
                                            <!--                                                :src="props.app.background.value_show"-->
                                            <!--                                                @click="triggerFileInput"-->
                                            <!--                                                alt="Preview"-->
                                            <!--                                                class="w-[110px] h-[58px] cursor-pointer object-contain"-->
                                            <!--                                            />-->
                                            <!--                                            <img-->
                                            <!--                                                @click="removeImageBackground"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <div v-else class="flex">
                                            <div>{{ form.sound_one_wrong.value_show }}</div>
                                            <!--                                            <img-->
                                            <!--                                                @click="removeBackgroundSound"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <input
                                            type="file"
                                            accept="audio/*"
                                            class="hidden"
                                            ref="fileSoundOneWrong"
                                            @change="handleUploadSoundOneWrong"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="w-1/2">
                    <div class="relative mt-6 w-full">
                        <label for="appNameInput" class="block text-base font-semibold text-black">
                            Khi làm đúng nhiều câu liên tiếp
                        </label>

                        <div class="mt-2 w-full border border-[#5FB2FF] rounded-xl p-4">
                            <div class="flex justify-between items-start w-full">
                                <div class="flex flex-col gap-2">
                                    <a-radio-group v-model:value="form.sound_multi_correct.style" @change="handleSoundMultiCorrectChange" class="flex flex-col gap-2">
                                        <a-radio :value="'default'">Âm thanh mặc định</a-radio>
                                    </a-radio-group>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <a-radio-group v-model:value="form.sound_multi_correct.style">
                                        <a-radio :value="'sound'">
                                            <span @click="triggerSoundMultiCorrect" class="border rounded-xl p-2 bg-[#D9D9D9]">Tải nhạc lên</span>
                                        </a-radio>
                                    </a-radio-group>
                                    <div v-if="form.sound_multi_correct.style === 'sound'" class="flex items-center gap-2 mt-2">
                                        <div
                                            v-if="props.app.background_sound_value_show && !form.sound_multi_correct.value_show"
                                            class="image-container"
                                        >
                                            <!--                                            <img-->
                                            <!--                                                :src="props.app.background.value_show"-->
                                            <!--                                                @click="triggerFileInput"-->
                                            <!--                                                alt="Preview"-->
                                            <!--                                                class="w-[110px] h-[58px] cursor-pointer object-contain"-->
                                            <!--                                            />-->
                                            <!--                                            <img-->
                                            <!--                                                @click="removeImageBackground"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <div v-else class="flex">
                                            <div>{{ form.sound_multi_correct.value_show }}</div>
                                            <!--                                            <img-->
                                            <!--                                                @click="removeBackgroundSound"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <input
                                            type="file"
                                            accept="audio/*"
                                            class="hidden"
                                            ref="fileSoundMultiCorrect"
                                            @change="handleUploadSoundMultiCorrect"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="flex mt-4 gap-4 items-center">
                                <div>Số câu đúng liên tiếp:</div>
                                    <a-input
                                        class="w-1/2"
                                        :style="{ borderColor: '#5FB2FF !important', borderWidth: '1px', borderStyle: 'solid', borderRadius: '0.75rem' }"
                                        size="large"
                                        :allow-clear="true"
                                        v-model:value="form.sound_multi_correct.number"
                                    >
                                    </a-input>
                            </div>
                            <div class="mt-4">
                                <div>Nhập nội dung (Động viên, khích lệ):</div>
                                <div>
                                    <a-textarea
                                        placeholder="Bạn nhập nội dung: vd: Thật ngầu! Giữ vững phong độ nhé!"
                                        :allow-clear="true"
                                        v-model:value="form.sound_multi_correct.content"
                                        :rows="3"
                                        class="w-full custom-textarea"
                                    >
                                    </a-textarea>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="relative mt-6 w-full">
                        <label for="appNameInput" class="block text-base font-semibold text-black">
                            Khi làm sai nhiều câu liên tiếp
                        </label>

                        <div class="mt-2 w-full border border-[#5FB2FF] rounded-xl p-4">
                            <div class="flex justify-between items-start w-full">
                                <div class="flex flex-col gap-2">
                                    <a-radio-group v-model:value="form.sound_multi_wrong.style" @change="handleSoundMultiWrongChange" class="flex flex-col gap-2">
                                        <a-radio :value="'default'">Âm thanh mặc định</a-radio>
                                    </a-radio-group>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <a-radio-group v-model:value="form.sound_multi_wrong.style">
                                        <a-radio :value="'sound'">
                                            <span @click="triggerSoundMultiWrong" class="border rounded-xl p-2 bg-[#D9D9D9]">Tải nhạc lên</span>
                                        </a-radio>
                                    </a-radio-group>
                                    <div v-if="form.sound_multi_wrong.style === 'sound'" class="flex items-center gap-2 mt-2">
                                        <div
                                            v-if="props.app.background_sound_value_show && !form.sound_multi_wrong.value_show"
                                            class="image-container"
                                        >
                                            <!--                                            <img-->
                                            <!--                                                :src="props.app.background.value_show"-->
                                            <!--                                                @click="triggerFileInput"-->
                                            <!--                                                alt="Preview"-->
                                            <!--                                                class="w-[110px] h-[58px] cursor-pointer object-contain"-->
                                            <!--                                            />-->
                                            <!--                                            <img-->
                                            <!--                                                @click="removeImageBackground"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <div v-else class="flex">
                                            <div>{{ form.sound_multi_wrong.value_show }}</div>
                                            <!--                                            <img-->
                                            <!--                                                @click="removeBackgroundSound"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <input
                                            type="file"
                                            accept="audio/*"
                                            class="hidden"
                                            ref="fileSoundMultiWrong"
                                            @change="handleUploadSoundMultiWrong"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="flex mt-4 gap-4 items-center">
                                <div>Số câu sai liên tiếp:</div>
                                    <a-input
                                        class="w-1/2"
                                        :style="{ borderColor: '#5FB2FF !important', borderWidth: '1px', borderStyle: 'solid', borderRadius: '0.75rem' }"
                                        size="large"
                                        :allow-clear="true"
                                        v-model:value="form.sound_multi_wrong.number"
                                    >
                                    </a-input>
                            </div>
                            <div class="mt-4">
                                <div>Nhập nội dung (Động viên, khích lệ):</div>
                                <div>
                                    <a-textarea
                                        placeholder="Bạn nhập nội dung: vd: Không sao! Cố gắng câu sau bạn nhé!"
                                        :allow-clear="true"
                                        v-model:value="form.sound_multi_wrong.content"
                                        :rows="3"
                                        class="w-full custom-textarea"
                                    >
                                    </a-textarea>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="relative mt-6 w-full">
                        <label for="appNameInput" class="block text-base font-semibold text-black">
                            Âm thanh khi nộp bài: Đạt kết quả tốt
                        </label>

                        <div class="mt-2 w-full border border-[#5FB2FF] rounded-xl p-4">
                            <div class="flex justify-between items-start w-full">
                                <div class="flex flex-col gap-2">
                                    <a-radio-group v-model:value="form.sound_result_excellent.style" @change="handleSoundResultExcellent" class="flex flex-col gap-2">
                                        <a-radio :value="'default'">Mặc định</a-radio>
                                    </a-radio-group>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <a-radio-group v-model:value="form.sound_result_excellent.style">
                                        <a-radio :value="'sound'">
                                            <span @click="triggerSoundResultExcellent" class="border rounded-xl p-2 bg-[#D9D9D9]">Tải nhạc lên</span>
                                        </a-radio>
                                    </a-radio-group>
                                    <div v-if="form.sound_result_excellent.style === 'sound'" class="flex items-center gap-2 mt-2">
                                        <div
                                            v-if="props.app.background_sound_value_show && !form.sound_result_excellent.value_show"
                                            class="image-container"
                                        >
                                            <!--                                            <img-->
                                            <!--                                                :src="props.app.background.value_show"-->
                                            <!--                                                @click="triggerFileInput"-->
                                            <!--                                                alt="Preview"-->
                                            <!--                                                class="w-[110px] h-[58px] cursor-pointer object-contain"-->
                                            <!--                                            />-->
                                            <!--                                            <img-->
                                            <!--                                                @click="removeImageBackground"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <div v-else class="flex">
                                            <div>{{ form.sound_result_excellent.value_show }}</div>
                                            <!--                                            <img-->
                                            <!--                                                @click="removeBackgroundSound"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <input
                                            type="file"
                                            accept="audio/*"
                                            class="hidden"
                                            ref="fileSoundResultExcellent"
                                            @change="handleUploadSoundResultExcellent"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative mt-6 w-full">
                        <label for="appNameInput" class="block text-base font-semibold text-black">
                            Âm thanh khi nộp bài: Đạt kết quả khá
                        </label>

                        <div class="mt-2 w-full border border-[#5FB2FF] rounded-xl p-4">
                            <div class="flex justify-between items-start w-full">
                                <div class="flex flex-col gap-2">
                                    <a-radio-group v-model:value="form.sound_result_good.style" @change="handleSoundResultGood" class="flex flex-col gap-2">
                                        <a-radio :value="'default'">Mặc định</a-radio>
                                    </a-radio-group>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <a-radio-group v-model:value="form.sound_result_good.style">
                                        <a-radio :value="'sound'">
                                            <span @click="triggerSoundResultGood" class="border rounded-xl p-2 bg-[#D9D9D9]">Tải nhạc lên</span>
                                        </a-radio>
                                    </a-radio-group>
                                    <div v-if="form.sound_result_good.style === 'sound'" class="flex items-center gap-2 mt-2">
                                        <div
                                            v-if="props.app.background_sound_value_show && !form.sound_result_good.value_show"
                                            class="image-container"
                                        >
                                            <!--                                            <img-->
                                            <!--                                                :src="props.app.background.value_show"-->
                                            <!--                                                @click="triggerFileInput"-->
                                            <!--                                                alt="Preview"-->
                                            <!--                                                class="w-[110px] h-[58px] cursor-pointer object-contain"-->
                                            <!--                                            />-->
                                            <!--                                            <img-->
                                            <!--                                                @click="removeImageBackground"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <div v-else class="flex">
                                            <div>{{ form.sound_result_good.value_show }}</div>
                                            <!--                                            <img-->
                                            <!--                                                @click="removeBackgroundSound"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <input
                                            type="file"
                                            accept="audio/*"
                                            class="hidden"
                                            ref="fileSoundResultGood"
                                            @change="handleUploadSoundResultGood"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative mt-6 w-full">
                        <label for="appNameInput" class="block text-base font-semibold text-black">
                            Âm thanh khi nộp bài: Đạt kết quả trung bình
                        </label>

                        <div class="mt-2 w-full border border-[#5FB2FF] rounded-xl p-4">
                            <div class="flex justify-between items-start w-full">
                                <div class="flex flex-col gap-2">
                                    <a-radio-group v-model:value="form.sound_result_average.style" @change="handleSoundResultAverage" class="flex flex-col gap-2">
                                        <a-radio :value="'default'">Mặc định</a-radio>
                                    </a-radio-group>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <a-radio-group v-model:value="form.sound_result_average.style">
                                        <a-radio :value="'sound'">
                                            <span @click="triggerSoundResultAverage" class="border rounded-xl p-2 bg-[#D9D9D9]">Tải nhạc lên</span>
                                        </a-radio>
                                    </a-radio-group>
                                    <div v-if="form.sound_result_average.style === 'sound'" class="flex items-center gap-2 mt-2">
                                        <div
                                            v-if="props.app.background_sound_value_show && !form.sound_result_average.value_show"
                                            class="image-container"
                                        >
                                            <!--                                            <img-->
                                            <!--                                                :src="props.app.background.value_show"-->
                                            <!--                                                @click="triggerFileInput"-->
                                            <!--                                                alt="Preview"-->
                                            <!--                                                class="w-[110px] h-[58px] cursor-pointer object-contain"-->
                                            <!--                                            />-->
                                            <!--                                            <img-->
                                            <!--                                                @click="removeImageBackground"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <div v-else class="flex">
                                            <div>{{ form.sound_result_average.value_show }}</div>
                                            <!--                                            <img-->
                                            <!--                                                @click="removeBackgroundSound"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <input
                                            type="file"
                                            accept="audio/*"
                                            class="hidden"
                                            ref="fileSoundResultAverage"
                                            @change="handleUploadSoundResultAverage"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative mt-6 w-full">
                        <label for="appNameInput" class="block text-base font-semibold text-black">
                            Nhạc nền
                        </label>

                        <div class="mt-2 w-full border border-[#5FB2FF] rounded-xl p-4">
                            <div class="flex justify-between items-start w-full">
                                <div class="flex flex-col gap-2">
                                    <a-radio-group v-model:value="form.background_sound.style"  @change="handleBackgroundSoundChange" class="flex flex-col gap-2">
                                        <a-radio :value="'default'">Mặc định</a-radio>
                                    </a-radio-group>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <a-radio-group v-model:value="form.background_sound.style">
                                        <a-radio :value="'sound'">
                                            <span @click="triggerBackgroundSound" class="border rounded-xl p-2 bg-[#D9D9D9]">Tải nhạc lên</span>
                                        </a-radio>
                                    </a-radio-group>

                                    <div v-if="form.background_sound.style === 'sound'" class="flex items-center gap-2 mt-2">
                                        <div
                                            v-if="backgroundSoundRaw.value_show && !form.background_sound.value_show"
                                            class="image-container"
                                        >
                                            <!--                                            <img-->
                                            <!--                                                :src="props.app.background.value_show"-->
                                            <!--                                                @click="triggerFileInput"-->
                                            <!--                                                alt="Preview"-->
                                            <!--                                                class="w-[110px] h-[58px] cursor-pointer object-contain"-->
                                            <!--                                            />-->
                                            <!--                                            <img-->
                                            <!--                                                @click="removeImageBackground"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <div v-else class="flex">
                                            <div>{{ form.background_sound.value_show }}</div>
                                            <!--                                            <img-->
                                            <!--                                                @click="removeBackgroundSound"-->
                                            <!--                                                class="delete-icon"-->
                                            <!--                                                src="/images/app/upload-image-app.png"-->
                                            <!--                                                alt=""-->
                                            <!--                                            />-->
                                        </div>

                                        <input
                                            type="file"
                                            accept="audio/*"
                                            class="hidden"
                                            ref="fileBackgroundSound"
                                            @change="handleUploadBackgroundSound"
                                        />
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

 ::v-deep(.custom-textarea .ant-input) {
     border: 1px solid #5FB2FF !important;
     border-radius: 12px !important; /* rounded-xl */
     padding: 8px 12px; /* Optional: dễ nhìn hơn */
 }

/* Focus style */
::v-deep(.custom-textarea .ant-input:focus) {
    border-color: #5FB2FF !important;
    box-shadow: 0 0 0 2px rgba(95, 178, 255, 0.2); /* giống như focus ring */
}
</style>
