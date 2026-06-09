<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm } from "@inertiajs/vue3";
import { ErrorMessage, Field, Form } from "vee-validate";
import InputError from "@/Components/InputError.vue";
import * as yup from "yup";
import { useToast } from "vue-toastification";
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import dayjs from "dayjs";

const toast = useToast();

const props = defineProps({
    apps: {
        type: Array,
        default: () => [],
    },
    entity: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    img_rule: props.entity?.img_rule ?? '',
    name: props.entity?.name ?? '',
    app_id: props.entity?.app_id ?? null,
    class_id: props.entity?.class_id ?? null,
    number_of_times: props.entity?.number_of_times ?? '',
    duration: props.entity?.duration ?? '',
    start_datetime: props.entity?.start_datetime ? dayjs(props.entity.start_datetime) : null,
    end_datetime: props.entity?.end_datetime ? dayjs(props.entity.end_datetime) : null,
    practice_ids: props.entity?.practice_ids ?? [],
});

const classesList = ref([]);
const loadClasses = async (appId) => {
    if (!appId) { classesList.value = []; return; }
    try {
        const res = await axios.get(route('admins.event.json.getClassesByApp', { app_id: appId }));
        if (res.data.status) {
            classesList.value = res.data.data.map(c => ({ value: c.id, label: c.name }));
        }
    } catch { classesList.value = []; }
};

const rules = {
    name: yup.string().required('Tên sự kiện không được để trống'),
    app_id: yup.string().required('App không được để trống'),
    start_datetime: yup.string().required('Thời gian bắt đầu không được để trống'),
    end_datetime: yup.string().required('Thời gian kết thúc không được để trống'),
    number_of_times: yup.string().required('Lượt chơi không được để trống'),
    duration: yup.string().required('Thời lượng không được để trống'),
    practice_ids: yup
        .array()
        .min(1, 'Bài tập không được để trống')
        .required('Bài tập không được để trống'),
};

const selectedApp = computed(() => {
    return props.apps.find(app => Number(app.id) === Number(form.app_id));
});

const books = ref([]);

const buildBooks = () => {
    if (!selectedApp.value) {
        books.value = [];
        return;
    }

    books.value = (selectedApp.value.books ?? []).map(book => {
        const updatedWeeks = (book.weeks ?? []).map(week => {
            const practices = Array.isArray(week.practices)
                ? week.practices
                : Object.values(week.practices ?? {});

            const isWeekActive = practices.some(practice =>
                (form.practice_ids ?? []).map(Number).includes(Number(practice.id))
            );

            return {
                ...week,
                practices,
                active: isWeekActive,
            };
        });

        const isBookActive = updatedWeeks.some(week => week.active);

        return {
            ...book,
            active: isBookActive,
            weeks: updatedWeeks,
        };
    });
};

watch(
    () => form.app_id,
    (newValue, oldValue) => {
        if (oldValue !== undefined && Number(newValue) !== Number(oldValue)) {
            form.practice_ids = [];
            form.class_id = null;
        }

        buildBooks();
        loadClasses(newValue);
    },
    { immediate: true }
);

const toggleBook = (bookIndex) => {
    books.value[bookIndex].active = !books.value[bookIndex].active;
};

const toggleWeek = (bookIndex, weekIndex) => {
    books.value[bookIndex].weeks[weekIndex].active = !books.value[bookIndex].weeks[weekIndex].active;
};

const handleSubmit = async () => {
    const payload = {
        ...form,
        start_datetime: form.start_datetime
            ? dayjs(form.start_datetime).format('YYYY-MM-DD HH:mm:ss')
            : null,
        end_datetime: form.end_datetime
            ? dayjs(form.end_datetime).format('YYYY-MM-DD HH:mm:ss')
            : null,
    };

    if (overlapEvents.value.length > 0) {
        toast.error('Khoảng thời gian trùng với sự kiện đã có trong lớp này!');
        return;
    }

    try {
        if (props.entity) {
            payload.id = props.entity.id;
            await axios.post(route('admins.event.json.update'), payload);
        } else {
            await axios.post(route('admins.event.store'), payload);
        }

        window.location.href = route('admins.event.index');
    } catch (error) {
        console.error('Error submitting form:', error);
        toast.error('Có lỗi xảy ra khi lưu sự kiện');
    }
};

// Overlap check
const overlapEvents = ref([]);
let overlapTimer = null;

const checkOverlap = async () => {
    if (!form.class_id || !form.start_datetime || !form.end_datetime) {
        overlapEvents.value = [];
        return;
    }
    const params = {
        class_id: form.class_id,
        start_datetime: dayjs(form.start_datetime).format('YYYY-MM-DD HH:mm:ss'),
        end_datetime: dayjs(form.end_datetime).format('YYYY-MM-DD HH:mm:ss'),
    };
    if (props.entity?.id) params.exclude_id = props.entity.id;

    try {
        const res = await axios.get(route('admins.event.json.checkOverlap', params));
        overlapEvents.value = res.data.overlapping ?? [];
    } catch {
        overlapEvents.value = [];
    }
};

const scheduleOverlapCheck = () => {
    clearTimeout(overlapTimer);
    overlapTimer = setTimeout(checkOverlap, 400);
};

watch(() => [form.class_id, form.start_datetime, form.end_datetime], scheduleOverlapCheck);

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
        toast.error('Vui lòng chọn file hình ảnh');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('file', file);

        isUploading.value = true;

        const res = await axios.post('/api/upload-file', formData);

        form.img_rule = res.data.s3_file_url;
    } catch (error) {
        console.error('Lỗi upload:', error);
        toast.error('Upload ảnh thất bại');
    } finally {
        isUploading.value = false;
    }
};

const removeImage = () => {
    form.img_rule = '';
};

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head :title="props.entity ? 'Cập nhật sự kiện' : 'Thêm sự kiện'" />

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ props.entity ? 'Cập nhật sự kiện' : 'Thêm sự kiện' }}
            </h2>
        </template>

        <div class="app-page mx-auto flex w-full md:w-4/5 gap-4 py-4">
            <div class="content-page w-full">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">
                    {{ props.entity ? 'Cập nhật Sự kiện' : 'Thêm Sự kiện' }}
                </h1>

                <Form @submit="handleSubmit" v-slot="{ errors }" class="w-full">
                    <div class="flex w-full gap-8">
                        <div class="w-full">
                            <div class="flex gap-8 mt-6">
                                <div class="relative mt-6 w-fit">
                                    <label class="block text-base font-semibold text-black">
                                        Ảnh đại diện
                                    </label>

                                    <div class="image-container mt-2 w-full md:w-[376px] h-[195px]">
                                        <img
                                            :src="form.img_rule !== '' ? form.img_rule : '/images/icon-select-image.png'"
                                            @click="triggerFileInput"
                                            alt="Preview"
                                            class="image h-[174px] w-full md:w-[376px] cursor-pointer object-contain"
                                        />

                                        <img
                                            @click="removeImage"
                                            v-if="form.img_rule"
                                            class="delete-icon"
                                            src="/images/icon-game-choose-correct/icon-delete.png"
                                            alt=""
                                        />

                                        <div
                                            v-if="!form.img_rule"
                                            class="absolute inset-0 top-3/4 cursor-pointer text-center"
                                            @click="triggerFileInput"
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
                            </div>

                            <div class="flex flex-col md:flex-row gap-8 mt-6">
                                <div class="w-full md:w-1/2 relative">
                                    <label class="block text-base font-semibold text-black">
                                        Chọn app (*)
                                    </label>

                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <Field name="app_id" :rules="rules.app_id" v-model="form.app_id">
                                                    <a-select
                                                        v-model:value="form.app_id"
                                                        placeholder="Chọn app"
                                                        size="large"
                                                        style="width: 100%"
                                                        :allow-clear="true"
                                                    >
                                                        <a-select-option
                                                            v-for="app in props.apps"
                                                            :key="app.id"
                                                            :value="app.id"
                                                        >
                                                            {{ app.title ?? app.name }}
                                                        </a-select-option>
                                                    </a-select>
                                                </Field>

                                                <ErrorMessage class="text-sm text-red-600" name="app_id" />
                                                <InputError class="mt-2" :message="form.errors.app_id" />
                                            </a-col>
                                        </a-row>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row gap-8 mt-6">
                                <div class="w-full md:w-1/2 relative">
                                    <label class="block text-base font-semibold text-black">
                                        Tên sự kiện (*)
                                    </label>

                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <Field name="name" :rules="rules.name" v-model="form.name">
                                                    <a-input
                                                        placeholder="Bạn điền tên sự kiện"
                                                        :allow-clear="true"
                                                        v-model:value="form.name"
                                                        size="large"
                                                    />
                                                </Field>

                                                <ErrorMessage class="text-sm text-red-600" name="name" />
                                                <InputError class="mt-2" :message="form.errors.name" />
                                            </a-col>
                                        </a-row>
                                    </div>
                                </div>

                                <div class="w-full md:w-1/2 relative">
                                    <label class="block text-base font-semibold text-black">
                                        Lớp
                                    </label>

                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <a-select
                                                    v-model:value="form.class_id"
                                                    placeholder="Chọn lớp"
                                                    size="large"
                                                    style="width: 100%"
                                                    :allow-clear="true"
                                                    :options="classesList"
                                                />
                                                <InputError class="mt-2" :message="form.errors.class_id" />
                                            </a-col>
                                        </a-row>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row gap-8 mt-6">
                                <div class="w-full md:w-1/2 relative">
                                    <label class="block text-base font-semibold text-black">
                                        Thời gian bắt đầu (*)
                                    </label>

                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <Field name="start_datetime" :rules="rules.start_datetime" v-model="form.start_datetime">
                                                    <a-date-picker
                                                        size="large"
                                                        style="width: 100%"
                                                        v-model:value="form.start_datetime"
                                                        placeholder="Chọn ngày"
                                                        format="HH:mm:ss DD/MM/YYYY"
                                                        :show-time="true"
                                                    />
                                                </Field>

                                                <ErrorMessage class="text-sm text-red-600" name="start_datetime" />
                                                <InputError class="mt-2" :message="form.errors.start_datetime" />
                                            </a-col>
                                        </a-row>
                                    </div>
                                </div>

                                <div class="w-full md:w-1/2 relative">
                                    <label class="block text-base font-semibold text-black">
                                        Thời gian kết thúc (*)
                                    </label>

                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <Field name="end_datetime" :rules="rules.end_datetime" v-model="form.end_datetime">
                                                    <a-date-picker
                                                        size="large"
                                                        style="width: 100%"
                                                        v-model:value="form.end_datetime"
                                                        placeholder="Chọn ngày"
                                                        format="HH:mm:ss DD/MM/YYYY"
                                                        :show-time="true"
                                                    />
                                                </Field>

                                                <ErrorMessage class="text-sm text-red-600" name="end_datetime" />
                                                <InputError class="mt-2" :message="form.errors.end_datetime" />
                                            </a-col>
                                        </a-row>
                                    </div>
                                </div>
                            </div>

                            <div v-if="overlapEvents.length > 0" class="mt-4 rounded-lg border border-red-300 bg-red-50 p-4">
                                <div class="flex items-center gap-2 font-semibold text-red-600 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                    </svg>
                                    Khoảng thời gian trùng với sự kiện đã có trong lớp này:
                                </div>
                                <ul class="space-y-1">
                                    <li v-for="ev in overlapEvents" :key="ev.id" class="text-sm text-red-700">
                                        • <strong>{{ ev.name }}</strong>: {{ ev.start }} → {{ ev.end }}
                                    </li>
                                </ul>
                            </div>

                            <div class="flex flex-col md:flex-row gap-8 mt-6">
                                <div class="w-full md:w-1/2 relative">
                                    <label class="block text-base font-semibold text-black">
                                        Lượt chơi (*)
                                    </label>

                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <Field name="number_of_times" :rules="rules.number_of_times" v-model="form.number_of_times">
                                                    <a-input-number
                                                        style="width: 100%"
                                                        placeholder="Lượt chơi"
                                                        :allow-clear="true"
                                                        v-model:value="form.number_of_times"
                                                        size="large"
                                                        :min="1"
                                                    />
                                                </Field>

                                                <ErrorMessage class="text-sm text-red-600" name="number_of_times" />
                                                <InputError class="mt-2" :message="form.errors.number_of_times" />
                                            </a-col>
                                        </a-row>
                                    </div>
                                </div>

                                <div class="w-full md:w-1/2 relative">
                                    <label class="block text-base font-semibold text-black">
                                        Thời lượng (*)
                                    </label>

                                    <div class="mt-2">
                                        <a-row>
                                            <a-col :span="24">
                                                <Field name="duration" :rules="rules.duration" v-model="form.duration">
                                                    <a-input-number
                                                        style="width: 100%"
                                                        placeholder="Thời lượng (đơn vị giây)"
                                                        :allow-clear="true"
                                                        v-model:value="form.duration"
                                                        size="large"
                                                        :min="1"
                                                    />
                                                </Field>

                                                <ErrorMessage class="text-sm text-red-600" name="duration" />
                                                <InputError class="mt-2" :message="form.errors.duration" />
                                            </a-col>
                                        </a-row>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-8 mt-6">
                                <div class="w-full relative">
                                    <label class="block text-base font-semibold text-black">
                                        Bài tập
                                    </label>

                                    <div class="mt-2">
                                        <div v-if="!form.app_id" class="text-sm text-gray-500">
                                            Vui lòng chọn app trước khi chọn bài tập.
                                        </div>

                                        <div
                                            v-else-if="books.length === 0"
                                            class="text-sm text-gray-500"
                                        >
                                            App này chưa có bài tập.
                                        </div>

                                        <div
                                            v-else
                                            v-for="(book, bookIndex) in books"
                                            :key="book.id"
                                            class="mb-6 border border-gray-200 p-4 rounded-lg hover:bg-gray-50 transition-all duration-200"
                                        >
                                            <div
                                                class="flex items-center text-lg font-semibold text-gray-800 cursor-pointer"
                                                @click="toggleBook(bookIndex)"
                                            >
                                                <span class="text-blue-500 text-xl mr-2">
                                                    {{ book.active ? '-' : '+' }}
                                                </span>

                                                <div>{{ book.title }}</div>
                                            </div>

                                            <div
                                                v-if="book.active"
                                                v-for="(week, weekIndex) in book.weeks"
                                                :key="week.id"
                                                class="ml-5 mt-4 pl-4 border-l-4 border-blue-500"
                                            >
                                                <div
                                                    class="flex items-center text-md font-medium text-gray-700 cursor-pointer"
                                                    @click="toggleWeek(bookIndex, weekIndex)"
                                                >
                                                    <span class="text-blue-500 text-xl mr-2">
                                                        {{ week.active ? '-' : '+' }}
                                                    </span>

                                                    <div>{{ week.name }}</div>
                                                </div>

                                                <div v-if="week.active" class="ml-5 mt-2">
                                                    <div
                                                        v-for="practice in week.practices"
                                                        :key="practice.id"
                                                        class="flex items-center mb-4"
                                                    >
                                                        <Field
                                                            :id="`practice_ids_${practice.id}`"
                                                            name="practice_ids"
                                                            type="checkbox"
                                                            :value="practice.id"
                                                            v-model="form.practice_ids"
                                                            class="mr-2 h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-0"
                                                        />

                                                        <label
                                                            class="text-gray-600 text-sm mt-1 cursor-pointer"
                                                            :for="`practice_ids_${practice.id}`"
                                                        >
                                                            {{ practice.name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <ErrorMessage class="text-sm text-red-600" name="practice_ids" />
                                        <InputError class="mt-2" :message="form.errors.practice_ids" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-center gap-4">
                        <img
                            @click="goBack"
                            class="h-[34px] cursor-pointer"
                            src="/images/icon-button-back.png"
                            alt=""
                        />

                        <div class="text-center">
                            <a-row>
                                <a-col :span="24">
                                    <a-button
                                        class="w-[100px] text-white"
                                        size="middle"
                                        type="primary"
                                        html-type="submit"
                                        :disabled="overlapEvents.length > 0"
                                    >
                                        Lưu
                                    </a-button>
                                </a-col>
                            </a-row>
                        </div>
                    </div>
                </Form>
            </div>
        </div>
    </SchoolLayout>
</template>

<style>
.image-container {
    position: relative;
    display: inline-block;
    overflow: hidden;
}

.image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}

.delete-icon {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 24px;
    color: white;
    background-color: rgba(0, 0, 0, 0.5);
    border-radius: 50%;
    padding: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.delete-icon:hover {
    background-color: rgba(255, 0, 0, 0.8);
}
</style>
