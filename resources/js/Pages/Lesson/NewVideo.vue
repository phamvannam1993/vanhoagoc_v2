<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import MasterLayout from '@/Layouts/MasterLayout.vue';
import { Form } from 'vee-validate';
import { AudioOutlined } from '@ant-design/icons-vue';
import { ref } from 'vue';

const page = usePage();
const query = page.props.query;
const app_id = query.app_id;
const book_id = query.book_id;
const week_id = query.week_id;
const form = useForm({
    descContent:
        'Một con khỉ đang leo cây, xung quanh là bầy khỉ con đang vui chơi. Dưới mặt đất là...',
    presentContent: 'Tiếng chim hót trong không gian yên tĩnh',
    model: null,
    duration: null,
    dimension: null,
    language: null,
    music: null,
});
const recognition = ref(null);
// Khởi tạo SpeechRecognition API
const startSpeechRecognitionDesc = () => {
    if ('webkitSpeechRecognition' in window) {
        recognition.value = new webkitSpeechRecognition();
        recognition.value.continuous = true; // Cho phép nhận dạng liên tục
        recognition.value.interimResults = true; // Hiển thị kết quả trong khi nhận dạng

        recognition.value.onresult = (event) => {
            let transcript = event.results[event.resultIndex][0].transcript;
            form.descContent = transcript; // Cập nhật nội dung nhập liệu
        };

        recognition.value.onstart = () => {
            console.log('Bắt đầu nhận diện giọng nói...');
        };
        recognition.value.onend = () => {
            console.log('Kết thúc nhận diện giọng nói.');
        };

        recognition.value.start(); // Bắt đầu nhận dạng
    } else {
        alert('Trình duyệt của bạn không hỗ trợ Speech Recognition API.');
    }
};
const startSpeechRecognitionPresent = () => {
    if ('webkitSpeechRecognition' in window) {
        recognition.value = new webkitSpeechRecognition();
        recognition.value.continuous = true; // Cho phép nhận dạng liên tục
        recognition.value.interimResults = true; // Hiển thị kết quả trong khi nhận dạng

        recognition.value.onresult = (event) => {
            let transcript = event.results[event.resultIndex][0].transcript;
            form.presentContent = transcript; // Cập nhật nội dung nhập liệu
        };

        recognition.value.onstart = () => {
            console.log('Bắt đầu nhận diện giọng nói...');
        };
        recognition.value.onend = () => {
            console.log('Kết thúc nhận diện giọng nói.');
        };

        recognition.value.start(); // Bắt đầu nhận dạng
    } else {
        alert('Trình duyệt của bạn không hỗ trợ Speech Recognition API.');
    }
};
const handleSubmit = () => {
    const params = {
        descContent: form.descContent,
        presentContent: form.presentContent,
        model: form.model,
        duration: form.duration,
        dimension: form.dimension,
        language: form.language,
        music: form.music,
    };
    // Todo Tạo Video
    axios
        .post(route('questions.json.newVideo'), params)
        .then((response) => {
            if (response.data.status) {
                toast.success('Tạo app thành công');
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
const options = ref([
    {
        value: 1,
        label: 'Kling',
    },
]);
const optionsTime = ref([
    {
        value: 1,
        label: '5',
    },
    {
        value: 2,
        label: '10',
    },
]);
const optionsSize = ref([
    {
        value: 1,
        label: '16:9',
    },
    {
        value: 2,
        label: '3:4',
    },
]);
const optionsLanguage = ref([
    {
        value: 1,
        label: 'Tiếng Việt',
    },
    {
        value: 2,
        label: 'Tiếng Anh',
    },
]);
const optionsMusic = ref([
    {
        value: 1,
        label: 'Nhạc Việt',
    },
    {
        value: 2,
        label: 'Nhạc Anh',
    },
]);
const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head title="New Video" />
    <MasterLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                App
            </h2>
        </template>

        <div class="app-page mx-auto flex w-4/5 gap-4 py-4">
            <div class="content-page w-1/2 sm:px-6 lg:px-8">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Tạo Video</h1>
                <Form @submit="handleSubmit" v-slot="{ errors }">
                    <div class="relative mt-6">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Mô tả nội dung video
                        </label>
                        <div class="mt-2">
                            <a-row>
                                <a-col :span="24">
                                    <!-- a-textarea với icon micro -->
                                    <a-textarea
                                        v-model:value="form.descContent"
                                        placeholder="Nói gì đó..."
                                        :rows="4"
                                        class="textarea-with-icon"
                                    />
                                    <AudioOutlined
                                        class="microphone-icon"
                                        :onClick="startSpeechRecognitionDesc"
                                    />
                                </a-col>
                            </a-row>
                        </div>
                    </div>
                    <div class="relative mt-6">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Nội dung thuyết trình
                        </label>
                        <div class="mt-2">
                            <a-row>
                                <a-col :span="24">
                                    <!-- a-textarea với icon micro -->
                                    <a-textarea
                                        v-model:value="form.presentContent"
                                        placeholder="Nói gì đó..."
                                        :rows="4"
                                        class="textarea-with-icon"
                                    />
                                    <AudioOutlined
                                        class="microphone-icon"
                                        :onClick="startSpeechRecognitionPresent"
                                    />
                                </a-col>
                            </a-row>
                        </div>
                    </div>
                    <div class="relative mt-6">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Mô hình
                        </label>
                        <div class="mt-2">
                            <a-row>
                                <a-col :span="24">
                                    <a-select
                                        class="input-search w-full"
                                        v-model:value="form.model"
                                        show-search
                                        placeholder="Chọn mô hình"
                                        size="large"
                                        :options="options"
                                        :filter-option="filterOption"
                                    ></a-select>
                                </a-col>
                            </a-row>
                        </div>
                    </div>
                    <div class="relative mt-6">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Thời lượng
                        </label>
                        <div class="mt-2">
                            <a-row>
                                <a-col :span="24">
                                    <a-select
                                        class="input-search w-full"
                                        v-model:value="form.duration"
                                        show-search
                                        placeholder="Chọn thời lượng"
                                        size="large"
                                        :options="optionsTime"
                                        :filter-option="filterOption"
                                    ></a-select>
                                </a-col>
                            </a-row>
                        </div>
                    </div>
                    <div class="relative mt-6">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Kích thước
                        </label>
                        <div class="mt-2">
                            <a-row>
                                <a-col :span="24">
                                    <a-select
                                        class="input-search w-full"
                                        v-model:value="form.dimension"
                                        show-search
                                        placeholder="Chọn kích thước"
                                        size="large"
                                        :options="optionsSize"
                                        :filter-option="filterOption"
                                    ></a-select>
                                </a-col>
                            </a-row>
                        </div>
                    </div>
                    <div class="relative mt-6">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Ngôn ngữ
                        </label>
                        <div class="mt-2">
                            <a-row>
                                <a-col :span="24">
                                    <a-select
                                        class="input-search w-full"
                                        v-model:value="form.language"
                                        show-search
                                        placeholder="Tất cả app"
                                        size="large"
                                        :options="optionsLanguage"
                                        :filter-option="filterOption"
                                    ></a-select>
                                </a-col>
                            </a-row>
                        </div>
                    </div>
                    <div class="relative mt-6">
                        <label
                            for="appNameInput"
                            class="block text-base font-semibold text-black"
                        >
                            Nhạc nền
                        </label>
                        <div class="mt-2">
                            <a-row>
                                <a-col :span="24">
                                    <a-select
                                        class="input-search w-full"
                                        v-model:value="form.music"
                                        show-search
                                        placeholder="Chọn tệp"
                                        size="large"
                                        :options="optionsMusic"
                                        :filter-option="filterOption"
                                    ></a-select>
                                </a-col>
                            </a-row>
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
                                    >
                                        Tạo
                                    </a-button>
                                </a-col>
                            </a-row>
                        </div>
                    </div>
                </Form>
            </div>
            <div class="relative w-1/2">
                <div class="absolute top-1/4 mt-6 w-full pl-20">
                    <label
                        for="appNameInput"
                        class="block font-bold text-[#2C75E3]"
                    >
                        Kết quả
                    </label>
                    <div class="mt-2">
                        <a-row>
                            <a-col :span="24">
                                <iframe
                                    src="https://www.youtube.com/embed/o5Y3GYu4ldk"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                    width="600"
                                    height="325px"
                                >
                                </iframe>
                            </a-col>
                        </a-row>
                        <div class="mt-4 w-[600px] text-end">
                            <a-row>
                                <a-col :span="24">
                                    <Link
                                        :href="
                                            route('lessons.video.history', {
                                                app_id: app_id,
                                                book_id: book_id,
                                                week_id: week_id,
                                            })
                                        "
                                    >
                                        <a-button
                                            class="w-[100px] text-white"
                                            size="large"
                                            type="primary"
                                        >
                                            Lịch sử
                                        </a-button>
                                    </Link>
                                </a-col>
                            </a-row>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MasterLayout>
</template>
<style scoped lang="scss">
.custom-input {
    border-color: #5fb2ff;
}

.textarea-with-icon {
    position: relative;
    width: 100%;
}

.microphone-icon {
    position: absolute;
    right: 10px;
    bottom: 10px;
    cursor: pointer;
}
</style>
