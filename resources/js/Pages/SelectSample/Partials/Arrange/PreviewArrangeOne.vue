<script setup>
import { ref, defineProps, defineEmits, computed } from 'vue';

const props = defineProps({
    openPreview: Boolean, // Nhận v-model:openPreview từ cha
    data: Object, // Nhận v-model:data từ cha
});
const open = computed({
    get: () => props.openPreview, // Lấy giá trị từ cha
    set: (value) => emit('update:openPreview', value) // Gửi sự kiện để cập nhật
});
const emit = defineEmits(['update:openPreview']);
const handleOk = () => {
    open.value = false; // Đóng modal
};
const data = computed({
    get: () => props.data,
    set: (value) => emit('update:data', value),
});
const openVideo = ref(false);
const openVideoTitle = () => {
    openVideo.value = true;
};
const videoUrl = ref(data.value.video_title_show);
const handleClose = () => {
    openVideo.value = false;
    const saveVideo = data.value.video_title_show;
    videoUrl.value = ''; // Xóa src để reset video
    setTimeout(() => {
        videoUrl.value = saveVideo;
    }, 300);
}
</script>

<template>
    <a-modal v-model:open="open" @ok="handleOk" :width="1000">
        <template #title></template>
        <div class="flex gap-2">
            <div class="w-2/4 font-bold text-[18px]">
                {{ data.title || "Bạn điền tên" }}
            </div>
            <div v-if="data.audio_title_show" class="flex items-center">
                <img
                    class="h-[30px] w-[30px] cursor-pointer"
                    src="/images/icon-sound.png"
                    alt=""
                />
                <audio
                    ref="audioPlayer"
                    :src="data.audio_title_show"
                    controls
                ></audio>
            </div>
            <div v-if="data.video_title_name" class="flex gap-2 items-center">
                <img @click="openVideoTitle" class="h-[30px] w-[30px] cursor-pointer" src="/images/icon-game-choose-correct/icon-video-new.png">
                <div>{{ data.video_title_name }}</div>
            </div>
        </div>
        <div class="mt-16 flex justify-center gap-4">
            <div
                v-for="(item, index) in data.listAnswer"
                :key="index"
                class="min-h-[240px] w-1/3 items-center justify-center rounded-md border-2 border-solid border-[#E5E5E5] bg-white p-4 text-[64px] max-md:max-w-full"
            >
                <div class="flex gap-4">
                    <div  v-if="item.type_answer === 1 || item.type_answer === 3" class="w-full">
                        <div class="items-center justify-center rounded-md border-2 border-solid border-[#E5E5E5] bg-white p-4 text-[64px] max-md:max-w-full">
                            {{ item.inputAnswer }}</div>
                        <div class="flex gap-4 mt-2 items-center">
                            <audio
                                class="mt-4"
                                v-if="item.type_answer === 3 && item.audio_answer_show"
                                ref="audioPlayer"
                                :src="item.audio_answer_show"
                                controls
                            ></audio>
                        </div>
                    </div>
                    <div v-else class="mb-2">
                        <img
                            v-if="item.type_answer === 2 && item.image_answer_show"
                            :src="item.image_answer_show"
                            alt="Preview"
                            class="h-[137px] w-[376px] cursor-pointer object-contain"
                        />
                    </div>
                </div>
                <div class="justify-items-center">
                    <div
                        class="h-[40px] w-[40px] text-[18px] rounded-md border-2 border-solid border-[#E5E5E5] flex justify-center items-center"
                    >
                        {{ item.inputNumber }}
                    </div>
                </div>
            </div>
        </div>
        <template #footer>
        </template>
    </a-modal>
    <a-modal v-model:open="openVideo" :width="800" @cancel="handleClose">
        <div class="flex justify-center">
            <div v-if="data.video_title_show">
                <div v-if="data.isYouTubeVideo">
                    <!-- Nhúng video YouTube -->
                    <iframe
                        :src="videoUrl"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        width="600"
                        height="300"
                    >
                    </iframe>
                </div>
                <div v-else>
                    <!-- Nếu không phải YouTube, hiển thị video bình thường -->
                    <video
                        :src="videoUrl"
                        controls
                        width="600"
                        height="300"
                    />
                </div>
            </div>
            <div v-else>Không có video để hiển thị</div>
        </div>
        <template #footer> </template>
    </a-modal>
</template>
<style>
.custom-height-sample {
    min-height: 455px;
    padding: 2.5rem;
}
</style>
